<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::pluck('value', 'key')->toArray();

        return view('system.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', 'favicon', 'logo']);

        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('uploads/system', 'public');
            SystemSetting::updateOrCreate(['key' => 'favicon'], ['value' => $path]);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('uploads/system', 'public');
            SystemSetting::updateOrCreate(['key' => 'logo'], ['value' => $path]);
        }

        return redirect()->route('system.settings')->with('success', 'Cập nhật cấu hình thông tin hệ thống thành công.');
    }
}
