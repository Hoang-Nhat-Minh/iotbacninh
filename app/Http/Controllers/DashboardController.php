<?php

namespace App\Http\Controllers;

use App\Models\Farm\Garden;
use App\Models\Farm\CareHistory;
use App\Models\Iot\MonitoringStation;
use App\Models\Iot\SensorReading;
use App\Models\Ai\DiseaseDiagnosis;
use App\Models\Ai\PestPrediction;
use App\Models\Notification\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        //Testing 1
        //Testing 2
        //Testing 3
        //Final test CI/CD
        $user = Auth::user();

        // 1. Gardens Query
        $gardensQuery = Garden::with(['stations.devices', 'user']);
        if ($user && $user->isUser()) {
            $gardensQuery->where('user_id', $user->id);
        }
        $gardens = $gardensQuery->get();

        // 2. Monitoring Stations Query
        $stationsQuery = MonitoringStation::with('garden');
        if ($user && $user->isUser()) {
            $stationsQuery->whereHas('garden', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $stations = $stationsQuery->get();

        // 3. Telemetry & Metrics
        $totalGardens = $gardens->count();
        $totalAreaM2 = $gardens->sum('area_m2');
        $totalAreaHa = round($totalAreaM2 / 10000, 2);
        $totalStations = $stations->count();
        $activeStations = $stations->where('status', 'active')->count();

        // 4. Critical Alerts & Notifications
        $alertsQuery = Notification::latest();
        if ($user && $user->isUser()) {
            $alertsQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('is_all', true);
            });
        }
        $criticalAlerts = $alertsQuery->take(4)->get();

        // 5. Recent AI Disease Diagnoses & Pest Predictions
        $recentDiagnoses = DiseaseDiagnosis::with('garden')->latest()->take(4)->get();
        $recentPredictions = PestPrediction::with('garden')->latest()->take(4)->get();

        // 6. Recent Care Tasks & Activities
        $careLogsQuery = CareHistory::with(['category', 'garden', 'user']);
        if ($user && $user->isUser()) {
            $careLogsQuery->where('user_id', $user->id);
        }
        $recentCareLogs = $careLogsQuery->latest('performed_at')->take(5)->get();

        // 7. Microclimate Summary Averages
        $avgTemp = 26.5;
        $avgHumidity = 78.0;
        $avgPh = 6.4;

        return view('dashboard.index', compact(
            'gardens',
            'stations',
            'totalGardens',
            'totalAreaHa',
            'totalStations',
            'activeStations',
            'criticalAlerts',
            'recentDiagnoses',
            'recentPredictions',
            'recentCareLogs',
            'avgTemp',
            'avgHumidity',
            'avgPh'
        ));
    }
}
