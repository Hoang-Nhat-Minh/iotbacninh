<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Farm\Garden;
use App\Models\Iot\MonitoringStation;
use App\Models\Iot\SensorReading;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WeatherHistoryController extends Controller
{
    public function index(Request $request)
    {
        $gardens = Garden::with('stations')->get();
        $stations = MonitoringStation::with('garden')->get();

        $selectedGardenId = $request->input('garden_id');
        $selectedStationId = $request->input('station_id');

        // Xác định trạm hoặc vùng trồng được chọn
        $selectedGarden = null;
        $selectedStation = null;

        if ($selectedStationId) {
            $selectedStation = $stations->firstWhere('id', $selectedStationId);
            $selectedGarden = $selectedStation ? $selectedStation->garden : null;
        } elseif ($selectedGardenId) {
            $selectedGarden = $gardens->firstWhere('id', $selectedGardenId);
            $selectedStation = $selectedGarden ? $selectedGarden->stations->first() : $stations->first();
        } else {
            $selectedStation = $stations->first();
            $selectedGarden = $selectedStation ? $selectedStation->garden : $gardens->first();
        }

        // Lấy danh sách ID trạm liên quan (nếu chọn theo vùng trồng thì lấy tất cả trạm trong vùng)
        $targetStationIds = [];
        if ($selectedStation) {
            $targetStationIds = [$selectedStation->id];
        } elseif ($selectedGarden && $selectedGarden->stations->count() > 0) {
            $targetStationIds = $selectedGarden->stations->pluck('id')->toArray();
        } else {
            $targetStationIds = $stations->pluck('id')->toArray();
        }

        $startDate = Carbon::now()->subDays(29)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // 1. Thu thập toàn bộ bản ghi SensorReading trong 30 ngày qua của các trạm mục tiêu
        $readings = SensorReading::whereIn('monitoring_station_id', $targetStationIds)
            ->where('recorded_at', '>=', $startDate)
            ->where('recorded_at', '<=', $endDate)
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Nhóm dữ liệu theo ngày Y-m-d
        $readingsByDate = $readings->groupBy(function ($r) {
            return Carbon::parse($r->recorded_at)->format('Y-m-d');
        });

        $dailyWeather = [];
        $totalTemp = 0;
        $totalHumidity = 0;
        $totalRain = 0;
        $totalSoilMoist = 0;
        $rainyDays = 0;

        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dateDisplay = $date->format('d/m/Y');
            $dayOfWeek = $date->locale('vi')->dayName;

            $dayReadings = $readingsByDate->get($dateStr);

            if ($dayReadings && $dayReadings->count() > 0) {
                // TÍNH TOÁN DỮ LIỆU THỰC TẾ TỪ CẢM BIẾN TRẠM
                $tempList = [];
                $humList = [];
                $rainList = [];
                $soilMoistList = [];
                $soilPhList = [];
                $soilTempList = [];
                $windList = [];
                $lightList = [];

                foreach ($dayReadings as $r) {
                    $vals = $this->extractReadingValues($r->data ?? []);
                    if (isset($vals['temp'])) $tempList[] = $vals['temp'];
                    if (isset($vals['humidity'])) $humList[] = $vals['humidity'];
                    if (isset($vals['rain'])) $rainList[] = $vals['rain'];
                    if (isset($vals['soil_moist'])) $soilMoistList[] = $vals['soil_moist'];
                    if (isset($vals['soil_ph'])) $soilPhList[] = $vals['soil_ph'];
                    if (isset($vals['soil_temp'])) $soilTempList[] = $vals['soil_temp'];
                    if (isset($vals['wind'])) $windList[] = $vals['wind'];
                    if (isset($vals['light'])) $lightList[] = $vals['light'];
                }

                $tempAvg = count($tempList) > 0 ? round(collect($tempList)->avg(), 1) : 27.5;
                $tempMin = count($tempList) > 0 ? round(collect($tempList)->min(), 1) : round($tempAvg - 2.5, 1);
                $tempMax = count($tempList) > 0 ? round(collect($tempList)->max(), 1) : round($tempAvg + 3.0, 1);
                $humidityAvg = count($humList) > 0 ? (int)round(collect($humList)->avg()) : 80;
                $rain = count($rainList) > 0 ? round(collect($rainList)->max(), 1) : 0.0;
                $soilMoist = count($soilMoistList) > 0 ? (int)round(collect($soilMoistList)->avg()) : 70;
                $soilPh = count($soilPhList) > 0 ? round(collect($soilPhList)->avg(), 1) : 6.5;
                $wind = count($windList) > 0 ? round(collect($windList)->avg(), 1) : 1.8;
                $light = count($lightList) > 0 ? (int)round(collect($lightList)->avg()) : 25000;
                $isRealSensorData = true;
                $recordsCount = $dayReadings->count();
            } else {
                // TẠO DỮ LIỆU CƠ SỞ MẪU KHÍ HẬU BẮC NINH (ĐỐI VỚI CÁC NGÀY TRƯỚC KHI LẮP ĐẶT TRẠM)
                $stSeed = $selectedStation ? $selectedStation->id : 1;
                $seed = ($stSeed * 100) + ($i * 7);
                $baseTemp = 27.5 + sin($seed) * 3.2;
                $tempMin = round($baseTemp - 2.8, 1);
                $tempMax = round($baseTemp + 3.6, 1);
                $tempAvg = round($baseTemp, 1);

                $humidityAvg = (int)max(60, min(95, 78 + cos($seed) * 12));
                $rain = max(0, round(sin($seed * 2) * 15 - 6, 1));
                $soilMoist = (int)max(55, min(92, 68 + ($rain > 0 ? 12 : 0) + cos($seed) * 4));
                $soilPh = round(6.3 + abs(cos($seed)) * 0.5, 1);
                $wind = round(1.6 + abs(sin($seed)) * 2.0, 1);
                $light = (int)max(6000, min(42000, 26000 + cos($seed * 3) * 10000));
                $isRealSensorData = false;
                $recordsCount = 0;
            }

            // Đánh giá trạng thái thời tiết vi khí hậu nông nghiệp
            if ($rain > 15) {
                $condition = 'Mưa to rào nặng hạt';
                $icon = 'bi-cloud-lightning-rain-fill text-primary';
                $rainyDays++;
            } elseif ($rain > 0) {
                $condition = 'Mưa rào rải rác';
                $icon = 'bi-cloud-rain-fill text-info';
                $rainyDays++;
            } elseif ($humidityAvg > 88) {
                $condition = 'Ẩm độ cao, âm u';
                $icon = 'bi-cloud-fill text-secondary';
            } elseif ($tempMax > 34) {
                $condition = 'Nắng nóng kéo dài';
                $icon = 'bi-sun-fill text-warning';
            } elseif ($tempAvg < 20) {
                $condition = 'Se lạnh, hanh khô';
                $icon = 'bi-cloud-snow text-info';
            } else {
                $condition = 'Nắng nhẹ, thời tiết mát';
                $icon = 'bi-cloud-sun-fill text-warning';
            }

            $totalTemp += $tempAvg;
            $totalHumidity += $humidityAvg;
            $totalRain += $rain;
            $totalSoilMoist += $soilMoist;

            $dailyWeather[] = [
                'date_str' => $dateStr,
                'date_display' => $dateDisplay,
                'day_of_week' => ucfirst($dayOfWeek),
                'is_today' => $i === 0,
                'temp_avg' => $tempAvg,
                'temp_min' => $tempMin,
                'temp_max' => $tempMax,
                'humidity_avg' => $humidityAvg,
                'rain' => $rain,
                'soil_moist' => $soilMoist,
                'soil_ph' => $soilPh,
                'wind' => $wind,
                'light' => number_format($light),
                'condition' => $condition,
                'icon' => $icon,
                'is_real_data' => $isRealSensorData,
                'records_count' => $recordsCount,
            ];
        }

        $summaryStats = [
            'avg_temp' => round($totalTemp / 30, 1),
            'avg_humidity' => round($totalHumidity / 30, 1),
            'avg_soil_moist' => round($totalSoilMoist / 30, 1),
            'total_rain' => round($totalRain, 1),
            'rainy_days' => $rainyDays,
            'garden_name' => $selectedGarden ? $selectedGarden->name : 'Vùng trồng Bắc Ninh',
            'station_name' => $selectedStation ? $selectedStation->name : 'Tất cả trạm',
            'station_code' => $selectedStation ? $selectedStation->code : 'ALL',
        ];

        return view('iot.weather_history', compact('gardens', 'stations', 'selectedGarden', 'selectedStation', 'dailyWeather', 'summaryStats'));
    }

    /**
     * Lấy chi tiết lịch sử đo đạc cảm biến theo từng giờ trong ngày được chọn.
     */
    public function detail($stationId, $date)
    {
        $st = MonitoringStation::with('garden')->findOrFail($stationId);
        $carbonDate = Carbon::parse($date);
        $startOfDay = $carbonDate->copy()->startOfDay();
        $endOfDay = $carbonDate->copy()->endOfDay();

        // 1. Lấy dữ liệu cảm biến thực tế trong ngày từ cơ sở dữ liệu
        $dayReadings = SensorReading::where('monitoring_station_id', $stationId)
            ->where('recorded_at', '>=', $startOfDay)
            ->where('recorded_at', '<=', $endOfDay)
            ->orderBy('recorded_at', 'asc')
            ->get();

        $hourly = [];

        if ($dayReadings->count() > 0) {
            // Hiển thị các bản ghi đo đạc thực tế của cảm biến
            $tempList = [];
            $humList = [];
            $rainList = [];

            foreach ($dayReadings as $r) {
                $vals = $this->extractReadingValues($r->data ?? []);
                $t = $vals['temp'] ?? 27.5;
                $h = $vals['humidity'] ?? 80;
                $rn = $vals['rain'] ?? 0;
                $sm = $vals['soil_moist'] ?? 70;
                $w = $vals['wind'] ?? 1.8;

                $tempList[] = $t;
                $humList[] = $h;
                $rainList[] = $rn;

                $hourly[] = [
                    'time' => Carbon::parse($r->recorded_at)->format('H:i:s'),
                    'temp' => round($t, 1),
                    'humidity' => (int) round($h),
                    'rain' => round($rn, 1),
                    'soil_moist' => (int) round($sm),
                    'wind' => round($w, 1),
                    'is_real' => true,
                ];
            }

            $tempAvg = round(collect($tempList)->avg(), 1);
            $humAvg = (int) round(collect($humList)->avg());
            $totalRain = round(collect($rainList)->max(), 1);
        } else {
            // Fallback: Tạo phân bố 24h giả lập theo chu kỳ ngày đêm
            $seed = ($stationId * 100) + (Carbon::now()->diffInDays($carbonDate) * 7);
            $baseTemp = 27.5 + sin($seed) * 3.2;
            $humidityAvg = (int)max(60, min(95, 78 + cos($seed) * 12));
            $rain = max(0, round(sin($seed * 2) * 15 - 6, 1));

            for ($h = 0; $h < 24; $h += 2) {
                $hTemp = round($baseTemp + sin(($h - 6) / 24 * 2 * M_PI) * 4.2, 1);
                $hHum = (int)max(50, min(99, $humidityAvg - sin(($h - 6) / 24 * 2 * M_PI) * 14));
                $hRain = ($rain > 0 && ($h >= 14 && $h <= 18)) ? round($rain / 3, 1) : 0;

                $hourly[] = [
                    'time' => sprintf('%02d:00', $h),
                    'temp' => $hTemp,
                    'humidity' => $hHum,
                    'rain' => $hRain,
                    'soil_moist' => (int)max(50, min(99, 70 + cos($seed + $h) * 5)),
                    'wind' => round(1.5 + abs(sin($h)) * 2, 1),
                    'is_real' => false,
                ];
            }

            $tempAvg = round($baseTemp, 1);
            $humAvg = $humidityAvg;
            $totalRain = $rain;
        }

        return response()->json([
            'success' => true,
            'station_name' => $st->name,
            'station_code' => $st->code,
            'zone_name' => $st->garden->name ?? 'Vùng trồng Bắc Ninh',
            'date_display' => $carbonDate->format('d/m/Y'),
            'temp_avg' => $tempAvg,
            'humidity_avg' => $humAvg,
            'total_rain' => $totalRain,
            'records_count' => $dayReadings->count(),
            'hourly' => $hourly,
        ]);
    }

    /**
     * Bóc tách các chỉ số cảm biến từ gói JSON
     */
    protected function extractReadingValues(array $data): array
    {
        $res = [];

        // Hỗ trợ dạng mảng readings: [{"device_code": "TEMP_AIR_01", "value": 26.8}, ...]
        if (isset($data['readings']) && is_array($data['readings'])) {
            foreach ($data['readings'] as $item) {
                $code = strtoupper($item['device_code'] ?? '');
                $val = $item['value'] ?? null;
                if ($val === null) continue;

                if (str_contains($code, 'TEMP_AIR') || $code === 'TEMP') $res['temp'] = (float) $val;
                elseif (str_contains($code, 'HUM_AIR') || $code === 'HUM') $res['humidity'] = (float) $val;
                elseif (str_contains($code, 'SOIL_MOIST')) $res['soil_moist'] = (float) $val;
                elseif (str_contains($code, 'SOIL_PH') || $code === 'PH') $res['soil_ph'] = (float) $val;
                elseif (str_contains($code, 'SOIL_TEMP')) $res['soil_temp'] = (float) $val;
                elseif (str_contains($code, 'LIGHT') || $code === 'LUX') $res['light'] = (int) $val;
                elseif (str_contains($code, 'RAIN')) $res['rain'] = (float) $val;
                elseif (str_contains($code, 'WIND')) $res['wind'] = (float) $val;
            }
        }

        // Hỗ trợ dạng JSON key-value phẳng: {"temp_air": 26.8, "humidity_air": 85.9, ...}
        if (isset($data['temp_air'])) $res['temp'] = (float) $data['temp_air'];
        if (isset($data['temperature'])) $res['temp'] = (float) $data['temperature'];
        if (isset($data['humidity_air'])) $res['humidity'] = (float) $data['humidity_air'];
        if (isset($data['humidity'])) $res['humidity'] = (float) $data['humidity'];
        if (isset($data['rain'])) $res['rain'] = (float) $data['rain'];
        if (isset($data['light'])) $res['light'] = (int) $data['light'];
        if (isset($data['wind_speed'])) $res['wind'] = (float) $data['wind_speed'];
        if (isset($data['wind'])) $res['wind'] = (float) $data['wind'];
        if (isset($data['soil_ph'])) $res['soil_ph'] = (float) $data['soil_ph'];
        if (isset($data['soil_temperature'])) $res['soil_temp'] = (float) $data['soil_temperature'];
        if (isset($data['soil_temp'])) $res['soil_temp'] = (float) $data['soil_temp'];
        if (isset($data['soil_moisture'])) $res['soil_moist'] = (float) $data['soil_moisture'];
        if (isset($data['soil_moist'])) $res['soil_moist'] = (float) $data['soil_moist'];

        return $res;
    }
}

