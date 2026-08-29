<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Models\Iot\MonitoringStation;
use App\Models\Iot\Device;
use App\Models\Iot\SensorReading;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class MqttListenerCommand extends Command
{
    /**
     * Tên và chữ ký của artisan command.
     *
     * @var string
     */
    protected $signature = 'mqtt:listen {--connection=default : Tên connection trong config/mqtt-client.php}';

    /**
     * Mô tả command.
     *
     * @var string
     */
    protected $description = 'Chạy tiến trình lắng nghe (daemon) nhận dữ liệu cảm biến và trạng thái trạm IoT qua MQTT Broker';

    /**
     * Thực thi command.
     */
    public function handle()
    {
        $connectionName = $this->option('connection');
        $this->info("=================================================================");
        $this->info("   HỆ THỐNG QUAN TRẮC IoT BẮC NINH - MQTT LISTENER WORKER        ");
        $this->info("=================================================================");
        $this->info("Kết nối MQTT Broker qua config connection: [{$connectionName}]");

        try {
            $mqtt = MQTT::connection($connectionName);

            // 1. Subscribe Topic Dữ liệu cảm biến Telemetry
            $telemetryTopic = 'khcn/stations/+/telemetry';
            $this->info("-> Subscribed topic Telemetry: {$telemetryTopic}");
            $mqtt->subscribe($telemetryTopic, function (string $topic, string $message) {
                $this->handleTelemetryMessage($topic, $message);
            }, 1);

            // 2. Subscribe Topic Trạng thái Trạm (Status & LWT)
            $statusTopic = 'khcn/stations/+/status';
            $this->info("-> Subscribed topic Station Status: {$statusTopic}");
            $mqtt->subscribe($statusTopic, function (string $topic, string $message) {
                $this->handleStatusMessage($topic, $message);
            }, 1);

            // 3. Subscribe Topic Xác nhận thực thi lệnh (Command ACK)
            $ackTopic = 'khcn/stations/+/ack';
            $this->info("-> Subscribed topic Command ACK: {$ackTopic}");
            $mqtt->subscribe($ackTopic, function (string $topic, string $message) {
                $this->handleAckMessage($topic, $message);
            }, 1);

            $this->info("Đang lắng nghe dữ liệu từ các trạm hiện trường... (Bấm Ctrl+C để dừng)");

            // Bắt đầu vòng lặp nhận message
            $mqtt->loop(true);

        } catch (\Throwable $e) {
            $this->error("Lỗi khi kết nối hoặc chạy MQTT Listener: " . $e->getMessage());
            Log::error("MQTT Listener Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Xử lý gói tin dữ liệu cảm biến (Telemetry).
     */
    protected function handleTelemetryMessage(string $topic, string $message): void
    {
        try {
            $payload = json_decode($message, true);
            if (!$payload) {
                $this->warn("[{$topic}] Gói tin JSON không hợp lệ: {$message}");
                return;
            }

            $stationCode = $payload['station_code'] ?? $this->extractStationCodeFromTopic($topic);
            if (!$stationCode) {
                $this->warn("[{$topic}] Không tìm thấy station_code trong gói tin.");
                return;
            }

            $station = MonitoringStation::where('code', $stationCode)->first();
            if (!$station) {
                $this->warn("[{$topic}] Trạm quan trắc có mã [{$stationCode}] chưa được đăng ký trong hệ thống.");
                return;
            }

            $recordedAt = isset($payload['timestamp'])
                ? Carbon::parse($payload['timestamp'])->setTimezone(config('app.timezone', 'Asia/Ho_Chi_Minh'))
                : now();


            $readings = $payload['readings'] ?? [];
            $savedCount = 0;

            foreach ($readings as $item) {
                $deviceCode = $item['device_code'] ?? null;
                $value = $item['value'] ?? null;

                if (!$deviceCode || $value === null || !is_numeric($value)) {
                    continue;
                }

                // Tự động tìm hoặc tạo thiết bị thuộc trạm
                $device = Device::firstOrCreate([
                    'monitoring_station_id' => $station->id,
                    'code' => $deviceCode,
                ], [
                    'name' => $item['name'] ?? 'Cảm biến ' . $deviceCode,
                    'type' => 'sensor',
                    'sensor_type' => $this->detectSensorType($deviceCode),
                    'status' => 'active',
                ]);

                // Lưu dữ liệu vào bảng sensor_readings
                SensorReading::create([
                    'device_id' => $device->id,
                    'value' => (double) $value,
                    'recorded_at' => $recordedAt,
                    'created_at' => now(),
                ]);

                $savedCount++;
            }

            // Cập nhật trạng thái trạm về active
            $station->update(['status' => 'active']);

            $this->info("[TELEMETRY] " . now()->format('H:i:s') . " | Trạm {$stationCode} | Đã lưu {$savedCount} chỉ số quan trắc.");
            Log::info("MQTT Telemetry Ingested: Station {$stationCode}, {$savedCount} readings.");

        } catch (\Throwable $e) {
            $this->error("[TELEMETRY ERROR] Lỗi xử lý message: " . $e->getMessage());
            Log::error("MQTT Telemetry Handler Error: " . $e->getMessage());
        }
    }

    /**
     * Xử lý thông điệp trạng thái Online / Offline / LWT của trạm.
     */
    protected function handleStatusMessage(string $topic, string $message): void
    {
        try {
            $payload = json_decode($message, true);
            if (!$payload) return;

            $stationCode = $payload['station_code'] ?? $this->extractStationCodeFromTopic($topic);
            $status = strtolower($payload['status'] ?? 'unknown');

            $station = MonitoringStation::where('code', $stationCode)->first();
            if ($station) {
                $dbStatus = ($status === 'online') ? 'active' : (($status === 'offline') ? 'danger' : 'maintenance');
                $station->update(['status' => $dbStatus]);

                $this->warn("[STATUS] " . now()->format('H:i:s') . " | Trạm {$stationCode} chuyển trạng thái: {$status} -> DB status: {$dbStatus}");
                Log::info("MQTT Station Status Update: Station {$stationCode} is now {$status}");
            }
        } catch (\Throwable $e) {
            Log::error("MQTT Status Handler Error: " . $e->getMessage());
        }
    }

    /**
     * Xử lý xác nhận thực thi lệnh (ACK) từ máy trạm gửi lên.
     */
    protected function handleAckMessage(string $topic, string $message): void
    {
        try {
            $payload = json_decode($message, true);
            if (!$payload) return;

            $commandId = $payload['command_id'] ?? 'N/A';
            $stationCode = $payload['station_code'] ?? $this->extractStationCodeFromTopic($topic);
            $action = $payload['action'] ?? 'UNKNOWN';
            $success = $payload['success'] ?? false;
            $msg = $payload['message'] ?? '';

            $statusText = $success ? 'THÀNH CÔNG' : 'THẤT BẠI';
            $this->info("[COMMAND ACK] " . now()->format('H:i:s') . " | Lệnh [{$commandId}] ({$action}) tại trạm [{$stationCode}] -> {$statusText}: {$msg}");
            Log::info("MQTT Command ACK: {$commandId} | {$stationCode} | {$action} | Success: " . ($success ? '1' : '0') . " | Msg: {$msg}");
        } catch (\Throwable $e) {
            Log::error("MQTT ACK Handler Error: " . $e->getMessage());
        }
    }

    /**
     * Trích xuất mã trạm từ topic dạng khcn/stations/{station_code}/...
     */
    protected function extractStationCodeFromTopic(string $topic): ?string
    {
        $parts = explode('/', $topic);
        return $parts[2] ?? null;
    }

    /**
     * Suy đoán sensor_type dựa trên mã device_code.
     */
    protected function detectSensorType(string $code): string
    {
        $codeUpper = strtoupper($code);
        if (str_contains($codeUpper, 'TEMP') || str_contains($codeUpper, 'HUM') || str_contains($codeUpper, 'CLIMATE')) {
            return 'climate';
        }
        if (str_contains($codeUpper, 'SOIL') || str_contains($codeUpper, 'MOIST') || str_contains($codeUpper, 'PH') || str_contains($codeUpper, 'EC')) {
            return 'soil';
        }
        if (str_contains($codeUpper, 'RAIN') || str_contains($codeUpper, 'WIND') || str_contains($codeUpper, 'LIGHT') || str_contains($codeUpper, 'LUX')) {
            return 'weather';
        }
        return 'general';
    }
}
