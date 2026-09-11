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
        $deviceId = $request->input('device_id');

        $imagesQuery = CameraMedia::where('type', 'image')
            ->where('file_path', 'not like', 'http%')
            ->where('file_path', 'not like', '%sample%')
            ->with(['device.monitoringStation']);
        $videosQuery = CameraMedia::where('type', 'video')
            ->where('file_path', 'not like', 'http%')
            ->where('file_path', 'not like', '%sample%')
            ->with(['device.monitoringStation']);

        if ($stationId) {
            $imagesQuery->whereHas('device', function ($q) use ($stationId) {
                $q->where('monitoring_station_id', $stationId);
            });
            $videosQuery->whereHas('device', function ($q) use ($stationId) {
                $q->where('monitoring_station_id', $stationId);
            });
        }

        if ($deviceId) {
            $imagesQuery->where('device_id', $deviceId);
            $videosQuery->where('device_id', $deviceId);
        }

        $images = $imagesQuery->latest()->paginate(12, ['*'], 'images_page')->withQueryString();
        $videos = $videosQuery->latest()->paginate(8, ['*'], 'videos_page')->withQueryString();
        $stations = MonitoringStation::with(['devices' => function ($q) {
            $q->where('type', 'camera');
        }])->get();

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
        $request->validate([
            'id' => 'required_without:ids|nullable|integer',
            'ids' => 'required_without:id|nullable|array',
            'ids.*' => 'integer',
        ]);

        $ids = [];
        if ($request->filled('ids')) {
            $ids = (array) $request->input('ids');
        } elseif ($request->filled('id')) {
            $ids = [(int) $request->input('id')];
        }

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Không tìm thấy file media cần xóa.');
        }

        $medias = CameraMedia::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($medias as $media) {
            // Xóa file vật lý khỏi ổ đĩa lưu trữ nếu có
            if ($media->file_path && !str_starts_with($media->file_path, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($media->file_path);
            }
            $media->delete();
            $count++;
        }

        return redirect()->back()->with('success', "Đã xóa thành công {$count} file media.");
    }
}
