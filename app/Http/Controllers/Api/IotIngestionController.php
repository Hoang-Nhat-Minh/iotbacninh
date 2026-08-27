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
        ]);

        $station = MonitoringStation::where('code', $request->station_code)->firstOrFail();
        $path = $request->file('image')->store('uploads/camera_images/' . $station->code, 'public');

        $cameraDevice = Device::firstOrCreate([
            'monitoring_station_id' => $station->id,
            'type' => 'camera',
        ], [
            'name' => 'Camera ' . $station->code,
            'code' => 'CAM-' . $station->code,
            'status' => 'active',
        ]);

        $media = CameraMedia::create([
            'device_id' => $cameraDevice->id,
            'type' => 'image',
            'name' => $request->file('image')->getClientOriginalName(),
            'file_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'data' => $media,
            'message' => 'Lưu ảnh camera trạm quan trắc thành công.',
        ]);
    }
}
