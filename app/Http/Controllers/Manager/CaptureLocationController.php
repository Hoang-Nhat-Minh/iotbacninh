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
        $locations = ImageCaptureLocation::with('monitoringStation')->latest()->get();
        $stations = MonitoringStation::all();

        return view('iot.locations', compact('locations', 'stations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'monitoring_station_id' => 'required|exists:monitoring_stations,id',
            'name' => 'required|string|max:255',
            'pan_angle' => 'nullable|numeric',
            'tilt_angle' => 'nullable|numeric',
            'zoom_level' => 'nullable|numeric',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $validated['pan_angle'] = $request->input('pan_angle', $request->input('pan', 0.00));
        $validated['tilt_angle'] = $request->input('tilt_angle', $request->input('tilt', 0.00));
        $validated['zoom_level'] = $request->input('zoom_level', $request->input('zoom', 1.0));
        $validated['status'] = $validated['status'] ?? 'active';

        ImageCaptureLocation::create($validated);

        return redirect()->back()->with('success', 'Đã lưu tọa độ góc chụp camera thành công.');
    }

    public function update(Request $request, $id)
    {
        $location = ImageCaptureLocation::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pan_angle' => 'nullable|numeric',
            'tilt_angle' => 'nullable|numeric',
            'zoom_level' => 'nullable|numeric',
            'status' => 'required|string|in:active,inactive',
        ]);

        $validated['pan_angle'] = $request->input('pan_angle', $request->input('pan', $location->pan_angle));
        $validated['tilt_angle'] = $request->input('tilt_angle', $request->input('tilt', $location->tilt_angle));
        $validated['zoom_level'] = $request->input('zoom_level', $request->input('zoom', $location->zoom_level));

        $location->update($validated);

        return redirect()->back()->with('success', 'Cập nhật tọa độ góc chụp thành công.');
    }

    public function destroy($id)
    {
        $location = ImageCaptureLocation::findOrFail($id);
        $location->delete();

        return redirect()->back()->with('success', 'Xóa tọa độ góc chụp thành công.');
    }
}
