<?php

namespace App\Services\Iot;

use App\Models\Iot\ImageCollectionSchedule;
use App\Models\Iot\MonitoringStation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class CameraScheduleService
{
    public function __construct(
        protected MqttService $mqttService
    ) {}

    /**
     * Quét và thực thi các lịch chụp ảnh tự động đang active theo khung giờ và chu kỳ.
     *
     * @param bool $force Bỏ qua kiểm tra khung giờ và chu kỳ để chạy ngay lập tức (dùng khi test)
     * @return array Danh sách các lệnh đã được kích hoạt
     */
    public function checkAndTriggerSchedules(bool $force = false): array
    {
        $activeSchedules = ImageCollectionSchedule::where('status', 'active')->get();
        $triggered = [];
        $now = now();
        $currentTimeStr = $now->format('H:i:s');

        foreach ($activeSchedules as $schedule) {
            $startTime = Carbon::parse($schedule->start_time)->format('H:i:s');
            $endTime = Carbon::parse($schedule->end_time)->format('H:i:s');

            if (!$force) {
                // 1. Kiểm tra hiện tại có nằm trong khung giờ [start_time, end_time] không
                $inWindow = false;
                if ($startTime <= $endTime) {
                    $inWindow = ($currentTimeStr >= $startTime && $currentTimeStr <= $endTime);
                } else {
                    // Khung giờ qua đêm (ví dụ: 22:00 -> 05:00)
                    $inWindow = ($currentTimeStr >= $startTime || $currentTimeStr <= $endTime);
                }

                if (!$inWindow) {
                    continue;
                }

                // 2. Kiểm tra chu kỳ interval (phút) kể từ lần chụp gần nhất
                $intervalMinutes = max(1, (int) ($schedule->interval ?: 60));
                $cacheKey = "camera_schedule_last_run_{$schedule->id}";
                $lastRunTimestamp = Cache::get($cacheKey);

                if ($lastRunTimestamp && ($now->timestamp - $lastRunTimestamp) < ($intervalMinutes * 60 - 30)) {
                    continue;
                }
            }

            // 3. Xác định danh sách trạm mục tiêu
            if ($schedule->monitoring_station_id) {
                $stations = MonitoringStation::where('id', $schedule->monitoring_station_id)
                    ->where('status', '!=', 'inactive')
                    ->get();
            } else {
                $stations = MonitoringStation::where('status', '!=', 'inactive')->get();
            }

            if ($stations->isEmpty()) {
                continue;
            }

            // 4. Gửi lệnh MQTT CAPTURE_SNAPSHOT xuống từng trạm (như chụp manual)
            foreach ($stations as $station) {
                $camId = 'cam_1'; // Mặc định chụp Camera 01 (Toàn cảnh)
                $cmdResult = $this->mqttService->publishCameraCommand($station->code, 'CAPTURE_SNAPSHOT', [
                    'camera_id' => $camId,
                    'quality' => 'main',
                    'trigger_source' => 'auto_schedule',
                    'schedule_id' => $schedule->id,
                    'schedule_name' => $schedule->name,
                ]);

                $logMsg = "[AUTO_SCHEDULE_CAPTURE] Kích hoạt chụp ảnh tự động theo lịch '{$schedule->name}' tới trạm {$station->code}";
                Log::info($logMsg, [
                    'schedule_id' => $schedule->id,
                    'station' => $station->code,
                    'camera_id' => $camId,
                    'command_result' => $cmdResult,
                ]);

                $triggered[] = [
                    'schedule_id' => $schedule->id,
                    'schedule_name' => $schedule->name,
                    'station_code' => $station->code,
                    'camera_id' => $camId,
                    'success' => $cmdResult['success'] ?? false,
                    'command_id' => $cmdResult['command_id'] ?? null,
                ];
            }

            // 5. Cập nhật thời điểm vừa kích hoạt vào Cache để tính chu kỳ tiếp theo
            Cache::put("camera_schedule_last_run_{$schedule->id}", $now->timestamp, now()->addDays(2));
        }

        return $triggered;
    }

    /**
     * Kích hoạt chụp ảnh ngay lập tức cho một lịch trình cụ thể.
     */
    public function triggerScheduleNow(int $scheduleId): array
    {
        $schedule = ImageCollectionSchedule::findOrFail($scheduleId);
        $triggered = [];
        $now = now();

        if ($schedule->monitoring_station_id) {
            $stations = MonitoringStation::where('id', $schedule->monitoring_station_id)->get();
        } else {
            $stations = MonitoringStation::where('status', '!=', 'inactive')->get();
        }

        foreach ($stations as $station) {
            $camId = 'cam_1';
            $cmdResult = $this->mqttService->publishCameraCommand($station->code, 'CAPTURE_SNAPSHOT', [
                'camera_id' => $camId,
                'quality' => 'main',
                'trigger_source' => 'manual_schedule_trigger',
                'schedule_id' => $schedule->id,
                'schedule_name' => $schedule->name,
            ]);

            Log::info("[MANUAL_SCHEDULE_TRIGGER] Kích hoạt chụp thủ công theo lịch '{$schedule->name}' tới trạm {$station->code}", [
                'schedule_id' => $schedule->id,
                'station' => $station->code,
                'command_result' => $cmdResult,
            ]);

            $triggered[] = [
                'schedule_id' => $schedule->id,
                'schedule_name' => $schedule->name,
                'station_code' => $station->code,
                'camera_id' => $camId,
                'success' => $cmdResult['success'] ?? false,
                'command_id' => $cmdResult['command_id'] ?? null,
            ];
        }

        Cache::put("camera_schedule_last_run_{$schedule->id}", $now->timestamp, now()->addDays(2));

        return $triggered;
    }
}
