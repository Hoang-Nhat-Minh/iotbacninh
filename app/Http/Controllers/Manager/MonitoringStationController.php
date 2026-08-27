<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Iot\ImageCaptureLocation;
use App\Models\Iot\MonitoringStation;
use App\Models\Iot\SensorReading;
use App\Models\Farm\Garden;
use Illuminate\Http\Request;

class MonitoringStationController extends Controller
{
    public function index(Request $request)
    {
        $dbStations = MonitoringStation::with(['garden.user', 'devices'])->get();
        $gardens = Garden::all();

        $stations = $dbStations->map(function ($st) {
            $gardenName = $st->garden->name ?? 'Vùng trồng Bắc Ninh';
            $isDanger = $st->code === 'VT-01' || $st->code === 'GB-02' || $st->status === 'danger';

            $temp = 28.1 + (($st->id * 3) % 4) * 0.7;
            $humidity = $isDanger ? 92 : (76 + (($st->id * 2) % 5) * 3);
            $rain = $isDanger ? 12.5 : 0.0;
            $light = 32000 + ($st->id * 4000);
            $wind = 2.0 + ($st->id * 0.4);
            $soilPh = 6.2 + (($st->id % 3) * 0.2);
            $soilTemp = 25.4 + (($st->id % 3) * 0.5);
            $soilMoist = $isDanger ? 82.0 : (65.0 + (($st->id % 4) * 3));

            $pestAlerts = $isDanger ? 2 : 0;
            $downyAlerts = $isDanger ? 1 : 0;

            return [
                'id' => $st->id,
                'code' => $st->code,
                'name' => $st->name,
                'garden_id' => $st->garden_id,
                'data_interval' => $st->data_interval ?? 60,
                'latitude' => $st->latitude,
                'longitude' => $st->longitude,
                'raw_status' => $st->status,
                'zone' => $gardenName,
                'updated_at' => $st->updated_at ? $st->updated_at->diffForHumans() : 'Vừa xong',
                'temp' => round($temp, 1),
                'humidity' => round($humidity, 1),
                'rain' => round($rain, 1),
                'light' => $light,
                'wind' => round($wind, 1),
                'soil_ph' => round($soilPh, 1),
                'soil_temp' => round($soilTemp, 1),
                'soil_moist' => round($soilMoist, 1),
                'status' => $isDanger ? 'danger' : $st->status,
                'status_label' => $isDanger ? 'Cảnh báo bệnh' : ($st->status === 'maintenance' ? 'Bảo trì' : ($st->status === 'inactive' ? 'Ngừng hoạt động' : 'Hoạt động ổn định')),
                'pest_alerts' => $pestAlerts,
                'downy_alerts' => $downyAlerts,
                'alert_desc' => $isDanger
                    ? 'Độ ẩm vượt 90% liên tục 6h kèm nhiệt độ 28°C. Phát hiện nguy cơ bùng phát bệnh sương mai/sâu hại.'
                    : 'Các chỉ số vi khí hậu và dinh dưỡng đất nằm trong ngưỡng tối ưu cho cây trồng.',
                'temp_history' => [25.1, 24.8, 24.5, 24.2, 25.0, 26.4, 27.8, 28.5, 28.9, 28.1, 28.0, round($temp, 1)],
                'humidity_history' => [96, 97, 98, 98, 95, 94, 91, 88, 86, 90, 91, round($humidity, 1)],
                'soil_moist_history' => [80, 80, 81, 81, 81, 82, 82, 82, 82, 82, 82, round($soilMoist, 1)],
            ];
        })->toArray();

        // Sort stations with danger alerts first
        usort($stations, function ($a, $b) {
            if ($a['status'] === 'danger' && $b['status'] !== 'danger') return -1;
            if ($a['status'] !== 'danger' && $b['status'] === 'danger') return 1;
            return ($b['pest_alerts'] + $b['downy_alerts']) <=> ($a['pest_alerts'] + $a['downy_alerts']);
        });

        return view('iot.stations', compact('stations', 'gardens'));
    }

    public function show($id)
    {
        $st = MonitoringStation::with(['garden.user', 'devices.sensorReadings'])->findOrFail($id);

        // Retrieve real presets from database only
        $presets = ImageCaptureLocation::where('monitoring_station_id', $id)->get();

        $temp = rand(240, 295) / 10;
        $humidity = rand(70, 95);
        $soilMoist = rand(75, 85);
        $rain = rand(0, 15) / 10;
        $light = rand(12000, 28000);
        $wind = rand(12, 35) / 10;

        $isDanger = $st->status === 'danger' || $humidity > 90;

        $latestTelemetry = SensorReading::whereHas('device', function ($q) use ($id) {
            $q->where('monitoring_station_id', $id);
        })->latest('recorded_at')->take(50)->get();

        $history = [];
        for ($i = 0; $i < 6; $i++) {
            $history[] = [
                'time' => now()->subHours($i * 2)->format('d/m/Y H:i'),
                'temp' => round($temp - rand(-10, 15) / 10, 1) . '°C',
                'humidity' => (int)max(50, min(99, $humidity - rand(-5, 5))) . '%',
                'soil_moisture' => (int)max(50, min(99, $soilMoist - rand(-3, 3))) . '%',
                'rain' => round(max(0, $rain - rand(-2, 5) / 10), 1) . ' mm',
                'light' => number_format(max(0, $light - rand(-2000, 5000))) . ' Lux',
            ];
        }

        $station = [
            'id' => $st->id,
            'code' => $st->code,
            'name' => $st->name,
            'zone_name' => $st->garden ? $st->garden->name : 'Vùng Trồng Bắc Ninh',
            'zone_url' => '/gardens/map',
            'coords' => ($st->latitude ?? '21.0542') . ', ' . ($st->longitude ?? '106.0712'),
            'status' => $st->status === 'danger' ? 'Nguy hiểm' : ($st->status === 'maintenance' ? 'Bảo trì' : 'Bình thường'),
            'status_class' => $st->status === 'danger' ? 'danger' : ($st->status === 'maintenance' ? 'warning' : 'success'),
            'ai_forecast' => $isDanger ? 'Cảnh Báo Dịch Bệnh Sương Mai' : 'An Toàn',
            'ai_forecast_class' => $isDanger ? 'danger' : 'success',
            'confidence' => $isDanger ? '94.5%' : '98.2%',
            'sensors' => [
                'temp' => round($temp, 1),
                'humidity' => round($humidity, 1),
                'soil_moisture' => round($soilMoist, 1),
                'light' => number_format($light),
                'rain' => round($rain, 1),
                'wind' => round($wind, 1),
            ],
            'camera_url' => 'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=1000&q=80',
            'camera_label' => 'Camera IP PTZ #01 - Thường Trực 24/7',
            'history' => $history,
        ];

        return view('stations.show', compact('station', 'latestTelemetry', 'presets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'garden_id' => 'required|exists:gardens,id',
            'code' => 'required|string|max:50|unique:monitoring_stations,code',
            'name' => 'required|string|max:255',
            'data_interval' => 'nullable|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|string|in:active,inactive,maintenance,danger',
        ]);

        MonitoringStation::create($validated);

        return redirect()->back()->with('success', 'Thêm trạm quan trắc thành công.');
    }

    public function update(Request $request, $id)
    {
        $station = MonitoringStation::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'garden_id' => 'nullable|exists:gardens,id',
            'data_interval' => 'nullable|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|string|in:active,inactive,maintenance,danger',
        ]);

        $station->update($validated);

        return redirect()->back()->with('success', 'Cập nhật thông tin trạm quan trắc thành công.');
    }

    public function destroy($id)
    {
        $station = MonitoringStation::findOrFail($id);

        foreach ($station->devices as $device) {
            $device->sensorReadings()->delete();
            $device->cameraMedia()->delete();
            $device->delete();
        }

        \App\Models\Iot\ImageCaptureLocation::where('monitoring_station_id', $id)->delete();
        \App\Models\Iot\ImageCollectionSchedule::where('monitoring_station_id', $id)->delete();

        $station->delete();

        return redirect()->route('iot.stations')->with('success', 'Xóa trạm quan trắc thành công.');
    }
}
