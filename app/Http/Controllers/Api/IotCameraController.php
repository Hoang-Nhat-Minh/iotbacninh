<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Iot\MonitoringStation;
use App\Services\Iot\MqttService;
use Illuminate\Support\Facades\Cache;

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
            'stream' => $streamInfo,
        ], $result['success'] ? 200 : 500);
    }

    /**
     * Tắt luồng phát trực tiếp để tiết kiệm dung lượng 4G.
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

        Cache::forget("camera_stream_{$station->code}_{$camId}");

        return response()->json([
            'success' => $result['success'],
            'message' => "Đã gửi lệnh dừng luồng camera {$camId}.",
            'command' => $result,
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

        return response()->json([
            'success' => $result['success'],
            'message' => "Đã gửi lệnh PTZ [{$direction}] tới camera {$camId}.",
            'command' => $result,
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

        return response()->json([
            'success' => $result['success'],
            'message' => "Đã gửi yêu cầu chụp ảnh snapshot tới camera {$camId}.",
            'command' => $result,
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
            'message' => 'Camera hiện đang ở chế độ chờ (tắt stream để bảo vệ 4G).',
        ]);
    }
}
