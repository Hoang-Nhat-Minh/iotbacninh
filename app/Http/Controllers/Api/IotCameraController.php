<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Iot\MonitoringStation;
use App\Services\Iot\MqttService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IotCameraController extends Controller
{
    /**
     * Bật luồng phát trực tiếp On-Demand cho Camera của trạm qua MQTT.
     */
    public function startStream(string $stationCode, Request $request, MqttService $mqttService)
    {
        $station = MonitoringStation::where('code', $stationCode)->firstOrFail();

        $validated = $request->validate([
            'camera_id' => 'nullable|string|in:cam_1,cam_2',
            'duration_seconds' => 'nullable|integer|min:30|max:600',
            'quality' => 'nullable|string|in:sub,main',
        ]);

        $camId = $validated['camera_id'] ?? 'cam_1';
        $duration = (int) ($validated['duration_seconds'] ?? 180);
        $quality = $validated['quality'] ?? 'sub';

        $result = $mqttService->publishCameraCommand($station->code, 'START_STREAM', [
            'camera_id' => $camId,
            'duration_seconds' => $duration,
            'quality' => $quality,
        ]);

        // Ghi log chi tiết lệnh gửi tới MQTT
        Log::info("[MQTT_CAMERA_SENT] Gửi lệnh START_STREAM tới MQTT Broker", [
            'station' => $station->code,
            'camera_id' => $camId,
            'action' => 'START_STREAM',
            'topic' => $result['topic'] ?? "khcn/stations/{$station->code}/camera/command",
            'command_id' => $result['command_id'] ?? null,
            'payload' => $result['payload'] ?? null,
            'mqtt_status' => $result['success'] ? 'PUBLISHED' : 'FAILED',
        ]);

        // Đợi gói ACK phản hồi từ trạm qua MQTT (nếu trạm đang online)
        $ack = $this->waitForMqttAck($station->code, $result['command_id'] ?? null);
        if ($ack) {
            Log::info("[MQTT_CAMERA_ACK] Trạm phản hồi kết quả lệnh START_STREAM qua MQTT", [
                'station' => $station->code,
                'command_id' => $result['command_id'] ?? null,
                'ack' => $ack,
            ]);
        } else {
            Log::info("[MQTT_CAMERA_ACK_PENDING] Broker đã nhận lệnh START_STREAM (chờ worker hoặc trạm phản hồi ngầm)", [
                'station' => $station->code,
                'command_id' => $result['command_id'] ?? null,
            ]);
        }

        $streamKey = "{$station->code}_{$camId}";
        $mediaHost = env('MEDIA_SERVER_HOST', $request->getHost());
        $hlsPort = env('MEDIA_SERVER_HLS_PORT', 8888);
        $webrtcPort = env('MEDIA_SERVER_WEBRTC_PORT', 8889);

        $streamInfo = [
            'station_code' => $station->code,
            'camera_id' => $camId,
            'stream_key' => $streamKey,
            'hls_url' => "http://{$mediaHost}:{$hlsPort}/live/{$streamKey}/index.m3u8",
            'webrtc_url' => "http://{$mediaHost}:{$webrtcPort}/live/{$streamKey}",
            'duration_seconds' => $duration,
            'expire_at' => time() + $duration,
        ];

        // Đặt trước Cache trạng thái stream để UI có thể phản hồi tức thì
        Cache::put("camera_stream_{$station->code}_{$camId}", array_merge(['active' => true], $streamInfo), $duration + 60);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? "Đã gửi lệnh kích hoạt luồng camera {$camId} ({$duration}s) xuống trạm."
                : "Gửi lệnh kích hoạt luồng camera thất bại.",
            'command' => $result,
            'ack' => $ack,
            'stream' => $streamInfo,
        ], $result['success'] ? 200 : 500);
    }

    /**
     * Tắt luồng phát trực tiếp.
     */
    public function stopStream(string $stationCode, Request $request, MqttService $mqttService)
    {
        $station = MonitoringStation::where('code', $stationCode)->firstOrFail();

        $validated = $request->validate([
            'camera_id' => 'nullable|string|in:cam_1,cam_2',
        ]);

        $camId = $validated['camera_id'] ?? 'cam_1';

        $result = $mqttService->publishCameraCommand($station->code, 'STOP_STREAM', [
            'camera_id' => $camId,
        ]);

        // Ghi log chi tiết lệnh dừng stream gửi tới MQTT
        Log::info("[MQTT_CAMERA_SENT] Gửi lệnh STOP_STREAM tới MQTT Broker", [
            'station' => $station->code,
            'camera_id' => $camId,
            'action' => 'STOP_STREAM',
            'topic' => $result['topic'] ?? "khcn/stations/{$station->code}/camera/command",
            'command_id' => $result['command_id'] ?? null,
            'payload' => $result['payload'] ?? null,
            'mqtt_status' => $result['success'] ? 'PUBLISHED' : 'FAILED',
        ]);

        $ack = $this->waitForMqttAck($station->code, $result['command_id'] ?? null);
        if ($ack) {
            Log::info("[MQTT_CAMERA_ACK] Trạm phản hồi kết quả lệnh STOP_STREAM qua MQTT", [
                'station' => $station->code,
                'command_id' => $result['command_id'] ?? null,
                'ack' => $ack,
            ]);
        }

        Cache::forget("camera_stream_{$station->code}_{$camId}");

        return response()->json([
            'success' => $result['success'],
            'message' => "Đã gửi lệnh dừng luồng camera {$camId}.",
            'command' => $result,
            'ack' => $ack,
        ]);
    }

    /**
     * Điều khiển quay quét góc camera PTZ.
     */
    public function ptzControl(string $stationCode, Request $request, MqttService $mqttService)
    {
        $station = MonitoringStation::where('code', $stationCode)->firstOrFail();

        $validated = $request->validate([
            'camera_id' => 'nullable|string|in:cam_1,cam_2',
            'direction' => 'required|string',
            'speed' => 'nullable|integer|min:1|max:10',
        ]);

        $camId = $validated['camera_id'] ?? 'cam_1';
        $direction = strtoupper($validated['direction']);
        $speed = (int) ($validated['speed'] ?? 5);

        $result = $mqttService->publishCameraCommand($station->code, 'PTZ_CONTROL', [
            'camera_id' => $camId,
            'direction' => $direction,
            'speed' => $speed,
        ]);

        // Ghi log chi tiết lệnh điều khiển PTZ gửi tới MQTT
        Log::info("[MQTT_CAMERA_SENT] Gửi lệnh PTZ_CONTROL [{$direction}] tới MQTT Broker", [
            'station' => $station->code,
            'camera_id' => $camId,
            'action' => 'PTZ_CONTROL',
            'direction' => $direction,
            'speed' => $speed,
            'topic' => $result['topic'] ?? "khcn/stations/{$station->code}/camera/command",
            'command_id' => $result['command_id'] ?? null,
            'payload' => $result['payload'] ?? null,
            'mqtt_status' => $result['success'] ? 'PUBLISHED' : 'FAILED',
        ]);

        $ack = $this->waitForMqttAck($station->code, $result['command_id'] ?? null);
        if ($ack) {
            Log::info("[MQTT_CAMERA_ACK] Trạm phản hồi kết quả lệnh PTZ_CONTROL qua MQTT", [
                'station' => $station->code,
                'command_id' => $result['command_id'] ?? null,
                'ack' => $ack,
            ]);
        }

        return response()->json([
            'success' => $result['success'],
            'message' => "Đã gửi lệnh PTZ [{$direction}] tới camera {$camId}.",
            'command' => $result,
            'ack' => $ack,
        ]);
    }

    /**
     * Yêu cầu chụp ảnh snapshot tức thời từ camera.
     */
    public function captureSnapshot(string $stationCode, Request $request, MqttService $mqttService)
    {
        $station = MonitoringStation::where('code', $stationCode)->firstOrFail();

        $validated = $request->validate([
            'camera_id' => 'nullable|string|in:cam_1,cam_2',
        ]);

        $camId = $validated['camera_id'] ?? 'cam_1';

        $result = $mqttService->publishCameraCommand($station->code, 'CAPTURE_SNAPSHOT', [
            'camera_id' => $camId,
        ]);

        // Ghi log chi tiết lệnh chụp ảnh snapshot gửi tới MQTT
        Log::info("[MQTT_CAMERA_SENT] Gửi lệnh CAPTURE_SNAPSHOT tới MQTT Broker", [
            'station' => $station->code,
            'camera_id' => $camId,
            'action' => 'CAPTURE_SNAPSHOT',
            'topic' => $result['topic'] ?? "khcn/stations/{$station->code}/camera/command",
            'command_id' => $result['command_id'] ?? null,
            'payload' => $result['payload'] ?? null,
            'mqtt_status' => $result['success'] ? 'PUBLISHED' : 'FAILED',
        ]);

        $ack = $this->waitForMqttAck($station->code, $result['command_id'] ?? null);
        if ($ack) {
            Log::info("[MQTT_CAMERA_ACK] Trạm phản hồi kết quả lệnh CAPTURE_SNAPSHOT qua MQTT", [
                'station' => $station->code,
                'command_id' => $result['command_id'] ?? null,
                'ack' => $ack,
            ]);
        }

        return response()->json([
            'success' => $result['success'],
            'message' => "Đã gửi yêu cầu chụp ảnh snapshot tới camera {$camId}.",
            'command' => $result,
            'ack' => $ack,
        ]);
    }

    /**
     * Kiểm tra trạng thái luồng phát hiện tại của Camera.
     */
    public function getStreamStatus(string $stationCode, Request $request)
    {
        $station = MonitoringStation::where('code', $stationCode)->firstOrFail();
        $camId = $request->query('camera_id', 'cam_1');

        $streamData = Cache::get("camera_stream_{$station->code}_{$camId}");

        if ($streamData && !empty($streamData['active'])) {
            $remaining = max(0, ($streamData['expire_at'] ?? time()) - time());
            return response()->json([
                'active' => true,
                'camera_id' => $camId,
                'remaining_seconds' => $remaining,
                'stream' => $streamData,
            ]);
        }

        return response()->json([
            'active' => false,
            'camera_id' => $camId,
            'message' => 'Camera hiện đang ở chế độ chờ.',
        ]);
    }

    /**
     * Chờ gói ACK phản hồi từ trạm qua MQTT Cache trong khoảng thời gian ngắn (tối đa 1.2 giây).
     */
    private function waitForMqttAck(string $stationCode, ?string $commandId, float $timeoutSeconds = 1.2): ?array
    {
        if (!$commandId) {
            return null;
        }

        $ackKey = "camera_ack_{$stationCode}_{$commandId}";
        $startTime = microtime(true);

        while ((microtime(true) - $startTime) < $timeoutSeconds) {
            $cached = Cache::get($ackKey);
            if ($cached) {
                return $cached;
            }
            usleep(80000); // 80ms
        }

        return null;
    }
}

