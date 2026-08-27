<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Iot\CameraMedia;
use App\Models\Iot\MonitoringStation;
use Illuminate\Http\Request;

class MediaMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $stationId = $request->input('station_id');

        $imagesQuery = CameraMedia::where('type', 'image')->with('device.monitoringStation');
        $videosQuery = CameraMedia::where('type', 'video')->with('device.monitoringStation');

        if ($stationId) {
            $imagesQuery->whereHas('device', function ($q) use ($stationId) {
                $q->where('monitoring_station_id', $stationId);
            });
            $videosQuery->whereHas('device', function ($q) use ($stationId) {
                $q->where('monitoring_station_id', $stationId);
            });
        }

        $images = $imagesQuery->latest()->paginate(12, ['*'], 'images_page');
        $videos = $videosQuery->latest()->paginate(8, ['*'], 'videos_page');
        $stations = MonitoringStation::all();

        return view('iot.media', compact('images', 'videos', 'stations'));
    }

    public function rename(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
        ]);

        $media = CameraMedia::findOrFail($validated['id']);
        $media->name = $validated['name'];
        $media->save();

        return redirect()->route('iot.media')->with('success', 'Đổi tên file media thành công.');
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        $media = CameraMedia::findOrFail($validated['id']);
        $media->delete();

        return redirect()->route('iot.media')->with('success', 'Xóa file media thành công.');
    }
}
