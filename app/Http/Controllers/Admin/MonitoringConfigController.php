<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Iot\MonitoringStation;
use App\Models\Account\SystemSetting;
use Illuminate\Http\Request;

class MonitoringConfigController extends Controller
{
    public function index()
    {
        $stations = MonitoringStation::with('garden')->get();
        $isSystemActive = SystemSetting::where('key', 'monitoring_system_active')->value('value') ?? 1;

        return view('system.monitoring_config', compact('stations', 'isSystemActive'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'is_active' => 'nullable|boolean',
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'monitoring_system_active'],
            ['value' => $request->has('is_active') ? 1 : 0]
        );

        return redirect()->route('system.monitoring_config')->with('success', 'Cập nhật cấu hình quan trắc thành công.');
    }
}
