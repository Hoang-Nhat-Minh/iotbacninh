<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user() ?? User::first();
        $settings = $user ? $user->settings : null;

        return view('account.profile.index', compact('user', 'settings'));
    }

    public function update(Request $request)
    {
        $user = Auth::user() ?? User::first();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'email' => 'nullable|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('account.profile')->with('success', 'Cập nhật thông tin cá nhân thành công.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:5120',
        ]);

        $user = Auth::user() ?? User::first();
        $path = $request->file('avatar')->store('uploads/avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return redirect()->route('account.profile')->with('success', 'Cập nhật ảnh đại diện thành công.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user() ?? User::first();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('account.profile')->with('success', 'Đổi mật khẩu thành công.');
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user() ?? User::first();

        $data = [
            'weather_alert_enabled' => $request->boolean('weather_alert_enabled'),
            'disease_alert_enabled' => $request->boolean('disease_alert_enabled'),
        ];

        if ($request->has('language')) {
            $data['language'] = $request->input('language');
        }

        if ($request->has('theme')) {
            $data['theme'] = $request->input('theme');
        }

        UserSetting::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return response()->json(['success' => true, 'message' => 'Cập nhật cài đặt ứng dụng thành công.']);
    }
}
