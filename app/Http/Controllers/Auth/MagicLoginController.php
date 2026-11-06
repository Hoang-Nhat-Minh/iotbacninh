<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\MagicLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicLoginController extends Controller
{
    /**
     * Xử lý đăng nhập qua magic link (không cần password).
     */
    public function __invoke(Request $request, $token)
    {
        $magicLink = MagicLink::where('token', $token)->with('user')->first();

        if (!$magicLink || !$magicLink->isValid()) {
            return redirect()->route('login')
                ->with('error', 'Link đăng nhập không hợp lệ, đã bị vô hiệu hóa hoặc đã hết lượt sử dụng.');
        }

        $user = $magicLink->user;

        if (!$user || $user->status !== 'active' || $user->isLocked()) {
            return redirect()->route('login')
                ->with('error', 'Tài khoản liên kết với link này hiện không thể đăng nhập.');
        }

        // Đăng nhập tự động user (không sử dụng remember_token vì bảng users không có cột này)
        Auth::login($user);
        $request->session()->regenerate();

        // Ghi log truy cập (tái sử dụng model AccessLog đã có sẵn)
        $deviceType = 'desktop';
        $userAgent = $request->userAgent() ?? '';
        if (preg_match('/(android|iphone|ipad|mobile)/i', $userAgent)) {
            $deviceType = 'mobile';
        }

        AccessLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $userAgent,
            'device_type' => $deviceType,
            'location' => 'Bắc Ninh, Việt Nam',
            'login_at' => now(),
        ]);

        // Ghi nhận lần sử dụng magic link
        $magicLink->recordUse($request->ip() ?? '127.0.0.1', $userAgent);

        return redirect()->intended('/iot/stations')
            ->with('success', 'Đã đăng nhập tự động qua magic link.');
    }
}