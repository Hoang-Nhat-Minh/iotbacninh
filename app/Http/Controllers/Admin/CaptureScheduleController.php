<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Iot\ImageCollectionSchedule;
use App\Models\Iot\MonitoringStation;
use Illuminate\Http\Request;

class CaptureScheduleController extends Controller
{
    public function index()
    {
        $schedules = ImageCollectionSchedule::with('monitoringStation')->latest()->get();
        $stations = MonitoringStation::all();

        return view('iot.schedules', compact('schedules', 'stations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'monitoring_station_id' => 'nullable|exists:monitoring_stations,id',
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'interval_minutes' => 'nullable|integer|min:1|max:1440',
            'interval' => 'nullable|integer|min:1|max:1440',
            'status' => 'required|string|in:active,inactive',
        ]);

        $validated['interval'] = $request->input('interval', $request->input('interval_minutes', 60));
        unset($validated['interval_minutes']);

        ImageCollectionSchedule::create($validated);

        return redirect()->back()->with('success', 'Thêm khung giờ gửi ảnh thành công.');
    }

    public function update(Request $request, $id)
    {
        $schedule = ImageCollectionSchedule::findOrFail($id);

        $validated = $request->validate([
            'monitoring_station_id' => 'nullable|exists:monitoring_stations,id',
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'interval_minutes' => 'nullable|integer|min:1|max:1440',
            'interval' => 'nullable|integer|min:1|max:1440',
            'status' => 'required|string|in:active,inactive',
        ]);

        $validated['interval'] = $request->input('interval', $request->input('interval_minutes', $schedule->interval));
        unset($validated['interval_minutes']);

        $schedule->update($validated);

        return redirect()->back()->with('success', 'Cập nhật khung giờ gửi ảnh thành công.');
    }

    public function destroy($id)
    {
        $schedule = ImageCollectionSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->back()->with('success', 'Xóa khung giờ thành công.');
    }
}
