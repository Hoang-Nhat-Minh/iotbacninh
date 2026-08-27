<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSupportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $myRequests = SupportRequest::where('user_id', $user ? $user->id : 0)
            ->latest()
            ->paginate(10);

        return view('support.user_support', compact('user', 'myRequests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'content' => 'required|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending';

        SupportRequest::create($validated);

        return redirect()->route('support.index')->with('success', 'Gửi yêu cầu hỗ trợ thành công. Cán bộ quản lý sẽ phản hồi sớm nhất.');
    }
}
