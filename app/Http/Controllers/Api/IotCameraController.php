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
        $urls = $this->buildStreamUrls($request, $streamKey);

        $streamInfo = [
            'station_code' => $station->code,
            'camera_id' => $camId,
            'stream_key' => $streamKey,
            'hls_url' => $urls['hls_url'],
            'webrtc_url' => $urls['webrtc_url'],
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
            $streamKey = $streamData['stream_key'] ?? "{$station->code}_{$camId}";
            $urls = $this->buildStreamUrls($request, $streamKey);
            $streamData['hls_url'] = $urls['hls_url'];
            $streamData['webrtc_url'] = $urls['webrtc_url'];

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
     * Proxy HLS stream từ MediaMTX nội bộ (127.0.0.1:9072) ra ngoài HTTPS.
     * Hoạt động an toàn dự phòng ngay lập tức nếu Nginx chưa cấu hình block `location /live/`.
     */
    public function proxyHls(string $path)
    {
        $internalHost = env('MEDIA_SERVER_INTERNAL_HOST', '127.0.0.1');
        $internalPort = env('MEDIA_SERVER_INTERNAL_HLS_PORT', 9072);
        $targetUrl = "http://{$internalHost}:{$internalPort}/live/{$path}";

        try {
            $ch = curl_init($targetUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                if (str_ends_with($path, '.m3u8')) {
                    $contentType = 'application/vnd.apple.mpegurl';
                } elseif (str_ends_with($path, '.ts')) {
                    $contentType = 'video/MP2T';
                }

                return response($content, $httpCode)
                    ->header('Content-Type', $contentType ?: 'application/octet-stream')
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            }

            return response()->json([
                'error' => 'Luồng camera hiện chưa sẵn sàng hoặc đã tắt.',
                'upstream_code' => $httpCode,
            ], $httpCode ?: 502);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Không thể kết nối MediaMTX server nội bộ: ' . $e->getMessage()
            ], 502);
        }
    }

    /**
     * Tạo đường dẫn URL phát video HLS và WebRTC phù hợp với môi trường HTTP/HTTPS
     * và hỗ trợ chạy trực tiếp qua Nginx Reverse Proxy để tránh lỗi Mixed Content.
     */
    private function buildStreamUrls(Request $request, string $streamKey): array
    {
        $isHttps = $request->isSecure() || $request->header('X-Forwarded-Proto') === 'https';
        $scheme = env('MEDIA_SERVER_SCHEME', $isHttps ? 'https' : 'http');
        $mediaHost = env('MEDIA_SERVER_HOST', $request->getHost());

        // 1. URL HLS: Nếu có biến MEDIA_SERVER_HLS_BASE_URL thì ưu tiên
        if ($customHlsBase = env('MEDIA_SERVER_HLS_BASE_URL')) {
            $hlsUrl = rtrim($customHlsBase, '/') . "/{$streamKey}/index.m3u8";
        } else {
            $configuredHlsPort = env('MEDIA_SERVER_HLS_PORT');
            // Nếu có cấu hình port cụ thể (khác 80, 443)
            if ($configuredHlsPort && !in_array((string) $configuredHlsPort, ['80', '443', 'none', 'false'], true)) {
                $hlsHost = "{$mediaHost}:{$configuredHlsPort}";
            } else {
                // Mặc định: HTTPS chạy qua Nginx Reverse Proxy (không cần port)
                // HTTP Local chạy qua port 9072 của MediaMTX
                $hlsHost = $isHttps ? $mediaHost : "{$mediaHost}:9072";
            }
            $hlsUrl = "{$scheme}://{$hlsHost}/live/{$streamKey}/index.m3u8";
        }

        // 2. URL WebRTC
        if ($customWebrtcBase = env('MEDIA_SERVER_WEBRTC_BASE_URL')) {
            $webrtcUrl = rtrim($customWebrtcBase, '/') . "/{$streamKey}";
        } else {
            $configuredWebrtcPort = env('MEDIA_SERVER_WEBRTC_PORT');
            if ($configuredWebrtcPort && !in_array((string) $configuredWebrtcPort, ['80', '443', 'none', 'false'], true)) {
                $webrtcHost = "{$mediaHost}:{$configuredWebrtcPort}";
            } else {
                $webrtcHost = $isHttps ? $mediaHost : "{$mediaHost}:9073";
            }
            $webrtcUrl = "{$scheme}://{$webrtcHost}/live/{$streamKey}";
        }

        return [
            'hls_url' => $hlsUrl,
            'webrtc_url' => $webrtcUrl,
        ];
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

