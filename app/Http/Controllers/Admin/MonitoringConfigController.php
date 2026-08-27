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
        $dataInterval = SystemSetting::where('key', 'data_send_interval')->value('value') ?? 60;
        $isSystemActive = SystemSetting::where('key', 'monitoring_system_active')->value('value') ?? 1;

        return view('system.monitoring_config', compact('stations', 'dataInterval', 'isSystemActive'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'data_send_interval' => 'required|integer|min:10|max:3600',
            'is_active' => 'nullable|boolean',
        ]);

        SystemSetting::updateOrCreate(
            ['key' => 'data_send_interval'],
            ['value' => $validated['data_send_interval']]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'monitoring_system_active'],
            ['value' => $request->has('is_active') ? 1 : 0]
        );

        return redirect()->route('system.monitoring_config')->with('success', 'Cập nhật cấu hình quan trắc thành công.');
    }
}
