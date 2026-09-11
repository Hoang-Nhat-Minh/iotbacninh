<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Iot\MonitoringStation;
use App\Models\Iot\Device;
use App\Models\Iot\SensorReading;
use App\Models\Iot\CameraMedia;
use Illuminate\Http\Request;

class IotIngestionController extends Controller
{
    public function ingestSensorData(Request $request)
    {
        $validated = $request->validate([
            'station_code' => 'required|exists:monitoring_stations,code',
            'readings' => 'required|array',
            'readings.*.device_code' => 'required|string',
            'readings.*.value' => 'required|numeric',
            'recorded_at' => 'nullable|date',
        ]);

        $station = MonitoringStation::where('code', $validated['station_code'])->firstOrFail();
        $recordedAt = $validated['recorded_at'] ?? now();

        $saved = [];
        foreach ($validated['readings'] as $item) {
            $device = Device::firstOrCreate([
                'monitoring_station_id' => $station->id,
                'code' => $item['device_code'],
            ], [
                'name' => 'Sensor ' . $item['device_code'],
                'type' => 'sensor',
                'sensor_type' => 'climate',
                'status' => 'active',
            ]);

            $saved[] = SensorReading::create([
                'device_id' => $device->id,
                'value' => $item['value'],
                'recorded_at' => $recordedAt,
            ]);
        }

        return response()->json([
            'success' => true,
            'count' => count($saved),
            'message' => 'Lưu dữ liệu cảm biến quan trắc thành công.',
        ]);
    }

    public function ingestCameraImage(Request $request)
    {
        $request->validate([
            'station_code' => 'required|exists:monitoring_stations,code',
            'image' => 'required|image|max:10240',
            'captured_at' => 'nullable|date',
            'camera_id' => 'nullable|string',
        ]);

        $station = MonitoringStation::where('code', $request->station_code)->firstOrFail();
        $camId = $request->input('camera_id', 'cam_1');

        $cameraNames = [
            'cam_1' => 'Camera 01 (Toàn cảnh)',
            'cam_2' => 'Camera 02 (Cận cảnh)',
            'cam_3' => 'Camera 03 (Khu vực đất)',
            'cam_4' => 'Camera 04 (Lối vào vườn)',
        ];
        $camLabel = $cameraNames[$camId] ?? ('Camera ' . strtoupper($camId));

        $path = $request->file('image')->store('uploads/camera_images/' . $station->code . '/' . $camId, 'public');

        $cameraDevice = Device::firstOrCreate([
            'monitoring_station_id' => $station->id,
            'code' => 'CAM-' . $station->code . '-' . $camId,
        ], [
            'name' => $camLabel . ' - ' . $station->name,
            'type' => 'camera',
            'sensor_type' => 'camera',
            'status' => 'active',
        ]);

        $media = CameraMedia::create([
            'device_id' => $cameraDevice->id,
            'type' => 'image',
            'name' => $camLabel . ' - ' . now()->format('d/m/Y H:i:s'),
            'file_path' => $path,
            'created_at' => $request->captured_at ? \Carbon\Carbon::parse($request->captured_at) : now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => array_merge($media->toArray(), [
                'camera_id' => $camId,
                'camera_label' => $camLabel,
            ]),
            'message' => 'Lưu ảnh camera trạm quan trắc thành công.',
        ]);
    }

    public function ingestCameraVideo(Request $request)
    {
        $request->validate([
            'station_code' => 'required|exists:monitoring_stations,code',
            'video' => 'required|file|max:51200',
            'captured_at' => 'nullable',
            'camera_id' => 'nullable|string',
        ]);

        $station = MonitoringStation::where('code', $request->station_code)->firstOrFail();
        $camId = $request->input('camera_id', 'cam_1');

        $cameraNames = [
            'cam_1' => 'Camera 01 (Toàn cảnh)',
            'cam_2' => 'Camera 02 (Cận cảnh)',
            'cam_3' => 'Camera 03 (Khu vực đất)',
            'cam_4' => 'Camera 04 (Lối vào vườn)',
        ];
        $camLabel = $cameraNames[$camId] ?? ('Camera ' . strtoupper($camId));

        $path = $request->file('video')->store('uploads/camera_videos/' . $station->code . '/' . $camId, 'public');

        $cameraDevice = Device::firstOrCreate([
            'monitoring_station_id' => $station->id,
            'code' => 'CAM-' . $station->code . '-' . $camId,
        ], [
            'name' => $camLabel . ' - ' . $station->name,
            'type' => 'camera',
            'sensor_type' => 'camera',
            'status' => 'active',
        ]);

        $media = CameraMedia::create([
            'device_id' => $cameraDevice->id,
            'type' => 'video',
            'name' => $camLabel . ' (Clip 10s) - ' . now()->format('d/m/Y H:i:s'),
            'file_path' => $path,
            'created_at' => $request->captured_at ? \Carbon\Carbon::parse($request->captured_at) : now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => array_merge($media->toArray(), [
                'camera_id' => $camId,
                'camera_label' => $camLabel,
                'video_url' => asset('storage/' . $path),
            ]),
            'message' => 'Lưu video ghi hình thành công.',
        ]);
    }


    public function sendCommand(string $stationCode, Request $request, \App\Services\Iot\MqttService $mqttService)
    {
        $validated = $request->validate([
            'action' => 'required|string',
            'params' => 'nullable|array',
        ]);

        $station = MonitoringStation::where('code', $stationCode)->firstOrFail();
        $result = $mqttService->publishCommand($station->code, $validated['action'], $validated['params'] ?? []);

        return response()->json([
            'success' => $result['success'],
            'station_code' => $stationCode,
            'command' => $result,
            'message' => $result['success'] ? 'Đã gửi lệnh điều khiển xuống trạm thành công.' : 'Gửi lệnh thất bại.',
        ], $result['success'] ? 200 : 500);
    }
}

