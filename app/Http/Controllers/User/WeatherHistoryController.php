<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Iot\MonitoringStation;
use App\Models\Iot\SensorReading;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WeatherHistoryController extends Controller
{
    public function index(Request $request)
    {
        $stations = MonitoringStation::with('garden')->get();

        $selectedStationId = $request->input('station_id');
        $selectedStation = $stations->firstWhere('id', $selectedStationId) ?? $stations->first();

        $stId = $selectedStation ? $selectedStation->id : 1;
        $dailyWeather = [];

        $totalTemp = 0;
        $totalHumidity = 0;
        $totalRain = 0;
        $rainyDays = 0;

        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dateDisplay = $date->format('d/m/Y');
            $dayOfWeek = $date->locale('vi')->dayName;

            // Generate realistic deterministic weather data per day & station
            $seed = ($stId * 100) + ($i * 7);
            $baseTemp = 27.5 + sin($seed) * 3.5;
            $tempMin = round($baseTemp - 3.2, 1);
            $tempMax = round($baseTemp + 4.1, 1);
            $tempAvg = round($baseTemp, 1);

            $humidityAvg = (int)max(60, min(98, 78 + cos($seed) * 15));
            $rain = max(0, round(sin($seed * 2) * 18 - 5, 1));
            $soilMoist = (int)max(55, min(95, 68 + ($rain > 0 ? 15 : 0) + cos($seed) * 5));
            $wind = round(1.8 + abs(sin($seed)) * 2.5, 1);
            $light = (int)max(5000, min(45000, 28000 + cos($seed * 3) * 12000));

            if ($rain > 15) {
                $condition = 'Mưa to rào nặng hạt';
                $icon = 'bi-cloud-lightning-rain-fill text-primary';
                $rainyDays++;
            } elseif ($rain > 0) {
                $condition = 'Mưa rào rải rác';
                $icon = 'bi-cloud-rain-fill text-info';
                $rainyDays++;
            } elseif ($humidityAvg > 88) {
                $condition = 'Âm u, độ ẩm cao';
                $icon = 'bi-cloud-fill text-secondary';
            } elseif ($tempMax > 34) {
                $condition = 'Nắng nóng kéo dài';
                $icon = 'bi-sun-fill text-warning';
            } else {
                $condition = 'Nắng nhẹ, thời tiết mát';
                $icon = 'bi-cloud-sun-fill text-warning';
            }

            $totalTemp += $tempAvg;
            $totalHumidity += $humidityAvg;
            $totalRain += $rain;

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
                'wind' => $wind,
                'light' => number_format($light),
                'condition' => $condition,
                'icon' => $icon,
            ];
        }

        $summaryStats = [
            'avg_temp' => round($totalTemp / 30, 1),
            'avg_humidity' => round($totalHumidity / 30, 1),
            'total_rain' => round($totalRain, 1),
            'rainy_days' => $rainyDays,
        ];

        return view('iot.weather_history', compact('stations', 'selectedStation', 'dailyWeather', 'summaryStats'));
    }

    public function detail($stationId, $date)
    {
        $st = MonitoringStation::with('garden')->findOrFail($stationId);
        $carbonDate = Carbon::parse($date);
        $seed = ($stationId * 100) + (Carbon::now()->diffInDays($carbonDate) * 7);

        $baseTemp = 27.5 + sin($seed) * 3.5;
        $humidityAvg = (int)max(60, min(98, 78 + cos($seed) * 15));
        $rain = max(0, round(sin($seed * 2) * 18 - 5, 1));

        $hourly = [];
        for ($h = 0; $h < 24; $h += 2) {
            $hTemp = round($baseTemp + sin(($h - 6) / 24 * 2 * M_PI) * 4.5, 1);
            $hHum = (int)max(50, min(99, $humidityAvg - sin(($h - 6) / 24 * 2 * M_PI) * 15));
            $hRain = ($rain > 0 && ($h >= 14 && $h <= 18)) ? round($rain / 3, 1) : 0;

            $hourly[] = [
                'time' => sprintf('%02d:00', $h),
                'temp' => $hTemp,
                'humidity' => $hHum,
                'rain' => $hRain,
                'soil_moist' => (int)max(50, min(99, 70 + cos($seed + $h) * 5)),
                'wind' => round(1.5 + abs(sin($h)) * 2, 1),
            ];
        }

        return response()->json([
            'success' => true,
            'station_name' => $st->name,
            'zone_name' => $st->garden->name ?? 'Vùng trồng Bắc Ninh',
            'date_display' => $carbonDate->format('d/m/Y'),
            'temp_avg' => round($baseTemp, 1),
            'humidity_avg' => $humidityAvg,
            'total_rain' => $rain,
            'hourly' => $hourly,
        ]);
    }
}
