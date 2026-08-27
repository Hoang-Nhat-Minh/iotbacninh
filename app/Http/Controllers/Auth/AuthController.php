<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Farm\Garden;
use App\Models\System\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])
            ->orWhere('phone', $credentials['username'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'username' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
            ])->withInput();
        }

        if ($user->isLocked()) {
            return back()->withErrors([
                'username' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Record Access Log
        $deviceType = 'desktop';
        $userAgent = $request->userAgent() ?? '';
        if (preg_match('/(android|iphone|ipad|mobile)/i', $userAgent)) {
            $deviceType = 'mobile';
        }

        $accessLog = AccessLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $userAgent,
            'device_type' => $deviceType,
            'location' => 'Bắc Ninh, Việt Nam',
            'login_at' => now(),
        ]);

        $request->session()->put('access_log_id', $accessLog->id);

        return redirect()->intended('/dashboard');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'garden_name' => 'nullable|string|max:255',
            'garden_location' => 'nullable|string|max:255',
            'crop_type' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role_id' => 3, // Role: User (Chủ vườn / Nông dân)
            'status' => 'active',
        ]);

        if (!empty($validated['garden_name'])) {
            Garden::create([
                'user_id' => $user->id,
                'code' => 'VT-' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                'name' => $validated['garden_name'],
                'crop_type' => $validated['crop_type'] ?? null,
                'location' => $validated['garden_location'] ?? null,
                'status' => 'active',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $deviceType = 'desktop';
        $userAgent = $request->userAgent() ?? '';
        if (preg_match('/(android|iphone|ipad|mobile)/i', $userAgent)) {
            $deviceType = 'mobile';
        }

        $accessLog = AccessLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $userAgent,
            'device_type' => $deviceType,
            'location' => 'Bắc Ninh, Việt Nam',
            'login_at' => now(),
        ]);
        $request->session()->put('access_log_id', $accessLog->id);

        return redirect()->route('dashboard')->with('success', 'Đăng ký tài khoản thành công!');
    }

    public function logout(Request $request)
    {
        $accessLogId = $request->session()->get('access_log_id');
        if ($accessLogId) {
            $accessLog = AccessLog::find($accessLogId);
            if ($accessLog) {
                $accessLog->logout_at = now();
                $accessLog->save();
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất khỏi hệ thống.');
    }
}
