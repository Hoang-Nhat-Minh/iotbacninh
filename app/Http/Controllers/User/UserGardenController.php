<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Farm\Garden;
use App\Models\Iot\MonitoringStation;
use App\Models\Iot\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserGardenController extends Controller
{
    public function map()
    {
        $userId = Auth::id();
        $gardens = Garden::with('stations')->when($userId, function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->get();

        return view('gardens.map', compact('gardens'));
    }

    public function weatherHistory($stationId, Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        $weatherHistory = SensorReading::whereHas('device', function ($q) use ($stationId) {
            $q->where('monitoring_station_id', $stationId);
        })->where('recorded_at', '>=', $startDate)
            ->latest('recorded_at')
            ->get();

        return response()->json($weatherHistory);
    }
}
