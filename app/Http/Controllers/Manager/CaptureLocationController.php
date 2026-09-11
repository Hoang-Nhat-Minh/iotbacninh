<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Iot\ImageCaptureLocation;
use App\Models\Iot\MonitoringStation;
use Illuminate\Http\Request;

class CaptureLocationController extends Controller
{
    public function index()
    {
        $locations = ImageCaptureLocation::with(['monitoringStation', 'schedule'])->latest()->get();
        $stations = MonitoringStation::all();
        $schedules = \App\Models\Iot\ImageCollectionSchedule::where('status', 'active')->get();

        return view('iot.locations', compact('locations', 'stations', 'schedules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'monitoring_station_id' => 'required|exists:monitoring_stations,id',
            'name' => 'required|string|max:255',
            'camera_id' => 'nullable|string|in:cam_1,cam_2,cam_3,cam_4',
            'schedule_id' => 'nullable|exists:image_collection_schedules,id',
            'pan_angle' => 'nullable|numeric',
            'tilt_angle' => 'nullable|numeric',
            'zoom_level' => 'nullable|numeric',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $validated['camera_id'] = $request->input('camera_id', 'cam_1');
        $validated['schedule_id'] = $request->input('schedule_id');
        $validated['pan_angle'] = $request->input('pan_angle', $request->input('pan', 0.00));
        $validated['tilt_angle'] = $request->input('tilt_angle', $request->input('tilt', 0.00));
        $validated['zoom_level'] = $request->input('zoom_level', $request->input('zoom', 1.0));
        $validated['status'] = $validated['status'] ?? 'active';

        ImageCaptureLocation::create($validated);

        return redirect()->back()->with('success', 'Đã lưu tọa độ góc chụp camera thành công.');
    }

    public function update(Request $request, $id = null)
    {
        $id = $id ?: $request->input('id');
        $location = ImageCaptureLocation::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'camera_id' => 'nullable|string|in:cam_1,cam_2,cam_3,cam_4',
            'schedule_id' => 'nullable|exists:image_collection_schedules,id',
            'pan_angle' => 'nullable|numeric',
            'tilt_angle' => 'nullable|numeric',
            'zoom_level' => 'nullable|numeric',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $validated['camera_id'] = $request->input('camera_id', $location->camera_id ?: 'cam_1');
        $validated['schedule_id'] = $request->input('schedule_id', $location->schedule_id);
        $validated['pan_angle'] = $request->input('pan_angle', $request->input('pan', $location->pan_angle));
        $validated['tilt_angle'] = $request->input('tilt_angle', $request->input('tilt', $location->tilt_angle));
        $validated['zoom_level'] = $request->input('zoom_level', $request->input('zoom', $location->zoom_level));
        if ($request->filled('status')) {
            $validated['status'] = $request->input('status');
        } else {
            unset($validated['status']);
        }

        $location->update($validated);

        return redirect()->back()->with('success', 'Cập nhật tọa độ góc chụp thành công.');
    }

    public function destroy(Request $request, $id = null)
    {
        $id = $id ?: $request->input('id');
        $location = ImageCaptureLocation::findOrFail($id);
        $location->delete();

        return redirect()->back()->with('success', 'Xóa tọa độ góc chụp thành công.');
    }

    /**
     * Kích hoạt xoay PTZ camera tới góc cấu hình và chụp ảnh thử nghiệm.
     */
    public function testCapture(Request $request, \App\Services\Iot\MqttService $mqttService, $id = null)
    {
        $id = $id ?: $request->input('id');
        $location = ImageCaptureLocation::with('monitoringStation')->findOrFail($id);
        $station = $location->monitoringStation;

        if (!$station) {
            return response()->json(['success' => false, 'message' => 'Trạm quan trắc không tồn tại.'], 404);
        }

        $camId = $location->camera_id ?: 'cam_1';
        $result = $mqttService->publishCameraCommand($station->code, 'CAPTURE_SNAPSHOT', [
            'camera_id' => $camId,
            'quality' => 'main',
            'trigger_source' => 'manual_test_location',
            'location_id' => $location->id,
            'location_name' => $location->name,
            'pan' => (float) $location->pan_angle,
            'tilt' => (float) $location->tilt_angle,
            'zoom' => (float) $location->zoom_level,
        ]);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? "Đã gửi lệnh xoay PTZ (Pan: {$location->pan_angle}°, Tilt: {$location->tilt_angle}°, Zoom: {$location->zoom_level}x) và chụp ảnh tới trạm {$station->name}."
                : "Gửi lệnh tới trạm thất bại.",
            'command' => $result,
        ]);
    }
}

