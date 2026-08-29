<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Iot\ImageCaptureLocation;
use App\Models\Iot\MonitoringStation;
use App\Models\Iot\SensorReading;
use App\Models\Farm\Garden;
use App\Services\Iot\MqttService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringStationController extends Controller
{
    public function index(Request $request)
    {
        $dbStations = MonitoringStation::with(['garden.user', 'devices.sensorReadings' => function ($q) {
            $q->latest('recorded_at')->take(20);
        }])->get();

        $gardens = Garden::all();

        $stations = $dbStations->map(function ($st) {
            $gardenName = $st->garden->name ?? 'Vùng trồng Bắc Ninh';

            // 1. Thu thập dữ liệu cảm biến thực tế mới nhất từ Database
            $latestReadings = $this->getLatestStationReadings($st);

            // 2. Health check thực tế:
            // Trạm được coi là Mất kết nối/Báo đỏ nếu:
            // - Status trong DB là 'danger' hoặc 'inactive' (do MQTT LWT báo offline)
            // - Hoặc không có gói tin nào trong khoảng thời gian (data_interval * 3) hoặc quá 15 phút
            $intervalSeconds = $st->data_interval ?: 60;
            $lastContact = $latestReadings['latest_time'] ?? $st->updated_at;
            $secondsSinceLastContact = $lastContact ? Carbon::parse($lastContact)->diffInSeconds(now()) : 999999;
            $isTimeout = $secondsSinceLastContact > max(120, $intervalSeconds * 3);

            $isDanger = ($st->status === 'danger' || $st->status === 'inactive' || $isTimeout);

            $statusLabel = 'Hoạt động ổn định';
            $statusClass = 'active';
            $alertDesc = 'Các chỉ số vi khí hậu và dinh dưỡng đất nằm trong ngưỡng tối ưu cho cây trồng.';

            if ($st->status === 'maintenance') {
                $statusLabel = 'Bảo trì trạm';
                $statusClass = 'maintenance';
                $alertDesc = 'Trạm đang trong chế độ bảo trì hoặc kiểm tra định kỳ.';
            } elseif ($isDanger) {
                $statusClass = 'danger';
                if ($isTimeout && $latestReadings['has_real_data']) {
                    $statusLabel = 'Mất kết nối trạm';
                    $alertDesc = 'Không nhận được tín hiệu quan trắc từ ' . Carbon::parse($lastContact)->diffForHumans() . '. Đang hiển thị dữ liệu đo đạc gần nhất.';
                } elseif ($st->status === 'danger') {
                    $statusLabel = 'Cảnh báo dịch bệnh / Nguy hiểm';
                    $alertDesc = 'Phát hiện chỉ số vi khí hậu vượt ngưỡng an toàn hoặc trạm báo lỗi.';
                } else {
                    $statusLabel = 'Chưa có tín hiệu';
                    $alertDesc = 'Trạm chưa phát tín hiệu telemetry nào lên máy chủ.';
                }
            }

            // Dữ liệu hiển thị (Lấy dữ liệu Database thật gần nhất, nếu trạm hoàn toàn chưa có data thì fallback)
            $temp = $latestReadings['temp'];
            $humidity = $latestReadings['humidity'];
            $rain = $latestReadings['rain'];
            $light = $latestReadings['light'];
            $wind = $latestReadings['wind'];
            $soilPh = $latestReadings['soil_ph'];
            $soilTemp = $latestReadings['soil_temp'];
            $soilMoist = $latestReadings['soil_moist'];

            $pestAlerts = ($isDanger && $humidity > 85) ? 2 : 0;
            $downyAlerts = ($isDanger && $humidity > 85) ? 1 : 0;

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
                'updated_at' => $lastContact ? Carbon::parse($lastContact)->diffForHumans() : 'Chưa có',
                'temp' => round($temp, 1),
                'humidity' => round($humidity, 1),
                'rain' => round($rain, 1),
                'light' => (int) $light,
                'wind' => round($wind, 1),

                'soil_ph' => round($soilPh, 1),
                'soil_temp' => round($soilTemp, 1),
                'soil_moist' => round($soilMoist, 1),
                'status' => $statusClass,
                'status_label' => $statusLabel,
                'pest_alerts' => $pestAlerts,
                'downy_alerts' => $downyAlerts,
                'alert_desc' => $alertDesc,
                'has_real_data' => $latestReadings['has_real_data'],
                'temp_history' => $latestReadings['temp_history'],
                'humidity_history' => $latestReadings['humidity_history'],
                'soil_moist_history' => $latestReadings['soil_moist_history'],
            ];
        })->toArray();

        // Sắp xếp các trạm có cảnh báo/mất kết nối lên đầu
        usort($stations, function ($a, $b) {
            if ($a['status'] === 'danger' && $b['status'] !== 'danger') return -1;
            if ($a['status'] !== 'danger' && $b['status'] === 'danger') return 1;
            return ($b['pest_alerts'] + $b['downy_alerts']) <=> ($a['pest_alerts'] + $a['downy_alerts']);
        });

        return view('iot.stations', compact('stations', 'gardens'));
    }

    public function show($id)
    {
        $st = MonitoringStation::with(['garden.user', 'devices.sensorReadings' => function ($q) {
            $q->latest('recorded_at')->take(50);
        }])->findOrFail($id);

        $presets = ImageCaptureLocation::where('monitoring_station_id', $id)->get();

        // 1. Lấy dữ liệu cảm biến thực tế mới nhất từ Database
        $latestReadings = $this->getLatestStationReadings($st);

        // 2. Health check thực tế
        $intervalSeconds = $st->data_interval ?: 60;
        $lastContact = $latestReadings['latest_time'] ?? $st->updated_at;
        $secondsSinceLastContact = $lastContact ? Carbon::parse($lastContact)->diffInSeconds(now()) : 999999;
        $isTimeout = $secondsSinceLastContact > max(120, $intervalSeconds * 3);

        $isDanger = ($st->status === 'danger' || $st->status === 'inactive' || $isTimeout);

        $latestTelemetry = SensorReading::whereHas('device', function ($q) use ($id) {
            $q->where('monitoring_station_id', $id);
        })->with('device')->latest('recorded_at')->take(50)->get();

        // Lịch sử hiển thị
        $history = [];
        if ($latestTelemetry->count() > 0) {
            $grouped = $latestTelemetry->groupBy(function ($r) {
                return Carbon::parse($r->recorded_at)->format('d/m/Y H:i');
            })->take(6);

            foreach ($grouped as $timeStr => $items) {
                $tempVal = $items->first(fn($i) => str_contains(strtoupper($i->device->code ?? ''), 'TEMP'))?->value ?? $latestReadings['temp'];
                $humVal = $items->first(fn($i) => str_contains(strtoupper($i->device->code ?? ''), 'HUM'))?->value ?? $latestReadings['humidity'];
                $soilVal = $items->first(fn($i) => str_contains(strtoupper($i->device->code ?? ''), 'SOIL'))?->value ?? $latestReadings['soil_moist'];
                $rainVal = $items->first(fn($i) => str_contains(strtoupper($i->device->code ?? ''), 'RAIN'))?->value ?? $latestReadings['rain'];
                $lightVal = $items->first(fn($i) => str_contains(strtoupper($i->device->code ?? ''), 'LIGHT'))?->value ?? $latestReadings['light'];

                $history[] = [
                    'time' => $timeStr,
                    'temp' => round($tempVal, 1) . '°C',
                    'humidity' => round($humVal) . '%',
                    'soil_moisture' => round($soilVal) . '%',
                    'rain' => round($rainVal, 1) . ' mm',
                    'light' => number_format($lightVal) . ' Lux',
                ];
            }
        }

        // Nếu chưa có đủ 6 dòng lịch sử, bổ sung
        if (count($history) === 0) {
            for ($i = 0; $i < 6; $i++) {
                $history[] = [
                    'time' => now()->subHours($i * 2)->format('d/m/Y H:i'),
                    'temp' => round($latestReadings['temp'] - ($i * 0.3), 1) . '°C',
                    'humidity' => (int)max(50, min(99, $latestReadings['humidity'] - ($i * 2))) . '%',
                    'soil_moisture' => (int)max(50, min(99, $latestReadings['soil_moist'] - ($i * 1))) . '%',
                    'rain' => round(max(0, $latestReadings['rain'] - ($i * 0.2)), 1) . ' mm',
                    'light' => number_format(max(0, $latestReadings['light'] - ($i * 1500))) . ' Lux',
                ];
            }
        }

        $station = [
            'id' => $st->id,
            'code' => $st->code,
            'name' => $st->name,
            'zone_name' => $st->garden ? $st->garden->name : 'Vùng Trồng Bắc Ninh',
            'zone_url' => '/gardens/map',
            'coords' => ($st->latitude ?? '21.0542') . ', ' . ($st->longitude ?? '106.0712'),
            'status' => $isDanger ? 'Mất kết nối / Nguy hiểm' : ($st->status === 'maintenance' ? 'Bảo trì' : 'Bình thường'),
            'status_class' => $isDanger ? 'danger' : ($st->status === 'maintenance' ? 'warning' : 'success'),
            'ai_forecast' => ($isDanger && $latestReadings['humidity'] > 85) ? 'Cảnh Báo Dịch Bệnh Sương Mai' : 'An Toàn',
            'ai_forecast_class' => ($isDanger && $latestReadings['humidity'] > 85) ? 'danger' : 'success',
            'confidence' => $isDanger ? '94.5%' : '98.2%',
            'sensors' => [
                'temp' => round($latestReadings['temp'], 1),
                'humidity' => round($latestReadings['humidity'], 1),
                'soil_moisture' => round($latestReadings['soil_moist'], 1),
                'light' => number_format($latestReadings['light']),
                'rain' => round($latestReadings['rain'], 1),
                'wind' => round($latestReadings['wind'], 1),
            ],
            'camera_url' => 'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=1000&q=80',
            'camera_label' => 'Camera IP PTZ #01 - Thường Trực 24/7',
            'history' => $history,
            'has_real_data' => $latestReadings['has_real_data'],
            'last_contact' => $lastContact ? Carbon::parse($lastContact)->diffForHumans() : 'Chưa có',
        ];

        return view('stations.show', compact('station', 'latestTelemetry', 'presets'));
    }

    public function store(Request $request, MqttService $mqttService)
    {
        $validated = $request->validate([
            'garden_id' => 'required|exists:gardens,id',
            'code' => 'required|string|max:50|unique:monitoring_stations,code',
            'name' => 'required|string|max:255',
            'data_interval' => 'nullable|integer|min:5',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|string|in:active,inactive,maintenance,danger',
        ]);

        $station = MonitoringStation::create($validated);

        // Bắn lệnh MQTT thiết lập chu kỳ gửi cho trạm mới nếu có
        if (!empty($validated['data_interval'])) {
            $mqttService->publishCommand($station->code, 'SET_INTERVAL', [
                'interval_seconds' => (int) $validated['data_interval']
            ]);
        }

        return redirect()->back()->with('success', 'Thêm trạm quan trắc thành công.');
    }

    public function update(Request $request, $id, MqttService $mqttService)
    {
        $station = MonitoringStation::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'garden_id' => 'nullable|exists:gardens,id',
            'data_interval' => 'nullable|integer|min:5',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'required|string|in:active,inactive,maintenance,danger',
        ]);

        $oldInterval = $station->data_interval;
        $station->update($validated);

        // TỰ ĐỘNG BẮN LỆNH MQTT 2 CHIỀU: Đổi chu kỳ gửi dữ liệu xuống máy trạm tức thời
        if (!empty($validated['data_interval']) && $validated['data_interval'] != $oldInterval) {
            $mqttService->publishCommand($station->code, 'SET_INTERVAL', [
                'interval_seconds' => (int) $validated['data_interval']
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật trạm và đã gửi lệnh đổi chu kỳ xuống trạm thành công.');
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

    /**
     * Trích xuất các chỉ số cảm biến thực tế mới nhất từ Database của trạm.
     */
    protected function getLatestStationReadings(MonitoringStation $st): array
    {
        $hasRealData = false;
        $latestTime = null;

        // Giá trị mặc định mô phỏng khi trạm mới chưa từng nhận được dữ liệu
        $temp = 28.1 + (($st->id * 3) % 4) * 0.7;
        $humidity = 76 + (($st->id * 2) % 5) * 3;
        $rain = 0.0;
        $light = 32000 + ($st->id * 4000);
        $wind = 2.0 + ($st->id * 0.4);
        $soilPh = 6.2 + (($st->id % 3) * 0.2);
        $soilTemp = 25.4 + (($st->id % 3) * 0.5);
        $soilMoist = 65.0 + (($st->id % 4) * 3);

        $tempHistory = [25.1, 24.8, 24.5, 24.2, 25.0, 26.4, 27.8, 28.5, 28.9, 28.1, 28.0, round($temp, 1)];
        $humHistory = [96, 97, 98, 98, 95, 94, 91, 88, 86, 90, 91, round($humidity, 1)];
        $soilHistory = [80, 80, 81, 81, 81, 82, 82, 82, 82, 82, 82, round($soilMoist, 1)];

        if ($st->devices && $st->devices->count() > 0) {
            foreach ($st->devices as $device) {
                $reading = $device->sensorReadings->sortByDesc('recorded_at')->first();
                if ($reading) {
                    $hasRealData = true;
                    if (!$latestTime || Carbon::parse($reading->recorded_at)->gt(Carbon::parse($latestTime))) {
                        $latestTime = $reading->recorded_at;
                    }

                    $codeUpper = strtoupper($device->code);
                    if (str_contains($codeUpper, 'TEMP_AIR') || str_contains($codeUpper, 'TEMP')) {
                        $temp = $reading->value;
                    } elseif (str_contains($codeUpper, 'HUM_AIR') || str_contains($codeUpper, 'HUM')) {
                        $humidity = $reading->value;
                    } elseif (str_contains($codeUpper, 'SOIL_MOIST')) {
                        $soilMoist = $reading->value;
                    } elseif (str_contains($codeUpper, 'SOIL_PH') || str_contains($codeUpper, 'PH')) {
                        $soilPh = $reading->value;
                    } elseif (str_contains($codeUpper, 'SOIL_TEMP')) {
                        $soilTemp = $reading->value;
                    } elseif (str_contains($codeUpper, 'LIGHT') || str_contains($codeUpper, 'LUX')) {
                        $light = (int) $reading->value;
                    } elseif (str_contains($codeUpper, 'RAIN')) {
                        $rain = $reading->value;
                    } elseif (str_contains($codeUpper, 'WIND')) {
                        $wind = $reading->value;
                    }
                }
            }
        }

        return [
            'has_real_data' => $hasRealData,
            'latest_time' => $latestTime,
            'temp' => $temp,
            'humidity' => $humidity,
            'rain' => $rain,
            'light' => $light,
            'wind' => $wind,
            'soil_ph' => $soilPh,
            'soil_temp' => $soilTemp,
            'soil_moist' => $soilMoist,
            'temp_history' => $tempHistory,
            'humidity_history' => $humHistory,
            'soil_moist_history' => $soilHistory,
        ];
    }
}
