<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account\SystemSetting;
use App\Models\Ai\DiseaseDiagnosis;
use App\Models\Ai\PestPrediction;
use App\Models\Farm\Garden;
use Illuminate\Http\Request;

class AutoAlertController extends Controller
{
    public function downyMildew(Request $request)
    {
        $setting = SystemSetting::firstOrCreate(
            ['key' => 'auto_downy_mildew_enabled'],
            ['value' => '1']
        );
        $recentDiagnoses = DiseaseDiagnosis::with('garden')
            ->latest()
            ->take(10)
            ->get();

        return view('ai.auto_downy_mildew', compact('setting', 'recentDiagnoses'));
    }

    public function toggleDownyMildew(Request $request)
    {
        $setting = SystemSetting::firstOrCreate(
            ['key' => 'auto_downy_mildew_enabled'],
            ['value' => '1']
        );

        $newVal = $setting->value === '1' ? '0' : '1';
        $setting->update(['value' => $newVal]);

        return response()->json([
            'success' => true,
            'is_enabled' => $newVal === '1',
            'message' => 'Thay đổi trạng thái cảnh báo sương mai tự động thành công.',
        ]);
    }

    public function pestPrediction(Request $request)
    {
        $setting = SystemSetting::firstOrCreate(
            ['key' => 'auto_pest_prediction_enabled'],
            ['value' => '1']
        );
        $gardens = Garden::with('user')->get();
        $recentPredictions = PestPrediction::with('garden')
            ->latest()
            ->take(10)
            ->get();

        return view('ai.auto_pest_prediction', compact('setting', 'gardens', 'recentPredictions'));
    }

    public function togglePestPrediction(Request $request)
    {
        $setting = SystemSetting::firstOrCreate(
            ['key' => 'auto_pest_prediction_enabled'],
            ['value' => '1']
        );

        $newVal = $setting->value === '1' ? '0' : '1';
        $setting->update(['value' => $newVal]);

        return response()->json([
            'success' => true,
            'is_enabled' => $newVal === '1',
            'message' => 'Thay đổi trạng thái cảnh báo sâu đục cuống tự động thành công.',
        ]);
    }
}
