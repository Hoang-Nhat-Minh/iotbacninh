<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farm\Garden;
use App\Models\Iot\MonitoringStation;
use App\Models\User;
use Illuminate\Http\Request;

class GardenController extends Controller
{
    public function index(Request $request)
    {
        $query = Garden::with(['user', 'stations']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $gardens = $query->get();
        $users = User::where('role_id', 3)->get();
        $stations = MonitoringStation::with('garden')->get();

        return view('gardens.map', compact('gardens', 'users', 'stations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|max:50|unique:gardens,code',
            'name' => 'required|string|max:255',
            'crop_type' => 'nullable|string|max:100',
            'area_m2' => 'nullable|numeric',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'boundary_geojson' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        Garden::create($validated);

        return redirect()->route('gardens.map')->with('success', 'Thêm vùng trồng thành công.');
    }

    public function update(Request $request, $id)
    {
        $garden = Garden::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'crop_type' => 'nullable|string|max:100',
            'area_m2' => 'nullable|numeric',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'boundary_geojson' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $garden->update($validated);

        return redirect()->route('gardens.map')->with('success', 'Cập nhật thông tin vùng trồng thành công.');
    }

    public function destroy($id)
    {
        $garden = Garden::withCount(['careHistories', 'stations'])->findOrFail($id);

        if ($garden->care_histories_count > 0 || $garden->stations_count > 0) {
            return redirect()->route('gardens.map')->withErrors([
                'msg' => 'Không thể xóa vùng trồng đang có liên kết nhật ký canh tác hoặc trạm quan trắc.',
            ]);
        }

        $garden->delete();

        return redirect()->route('gardens.map')->with('success', 'Xóa vùng trồng thành công.');
    }
}
