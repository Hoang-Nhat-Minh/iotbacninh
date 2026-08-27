<?php

namespace App\Http\Controllers\Labeling;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabelerAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isAdmin() || $user->isManager()) {
                return redirect('/labeler/dashboard');
            }
        }

        return view('labeling.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $request->input('username');
        $password = $request->input('password');
        $remember = $request->has('remember');

        // Check if login input is phone or username or email
        $fieldType = is_numeric($loginInput) ? 'phone' : (filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username');

        if (Auth::attempt([$fieldType => $loginInput, 'password' => $password], $remember)) {
            $user = Auth::user();

            if ($user->isLocked()) {
                Auth::logout();
                return back()->withErrors(['username' => 'Tài khoản của bạn đã bị khóa.'])->onlyInput('username');
            }

            if (!$user->isAdmin() && !$user->isManager()) {
                Auth::logout();
                return back()->withErrors(['username' => 'Tài khoản của bạn không có quyền truy cập phân hệ AI Labeling. Chỉ dành cho Quản trị viên và Nhà quản lý.'])->onlyInput('username');
            }

            $request->session()->regenerate();
            return redirect('/labeler/dashboard');
        }

        return back()->withErrors([
            'username' => 'Tên đăng nhập, số điện thoại hoặc mật khẩu không chính xác.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/labeler/login');
    }
}
