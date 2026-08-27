<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Notification\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Notification::with('user');

        if ($user && $user->isUser()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('is_all', true);
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $notifications = $query->latest()->paginate(15);
        $users = ($user && ($user->isAdmin() || $user->isManager())) 
            ? User::where('id', '!=', $user->id)->get() 
            : collect();

        return view('notifications.index', compact('notifications', 'users'));
    }

    public function store(Request $request)
    {
        $isAll = $request->boolean('is_all');

        $validated = $request->validate([
            'is_all' => 'nullable',
            'user_ids' => $isAll ? 'nullable|array' : 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|string|in:pest_alert,weather_alert,system,general',
            'priority' => 'required|string|in:low,medium,high',
        ], [
            'user_ids.required' => 'Vui lòng chọn ít nhất một người nhận hoặc chọn Gửi cho tất cả.',
            'user_ids.min' => 'Vui lòng chọn ít nhất một người nhận.',
        ]);

        if ($isAll) {
            Notification::create([
                'is_all' => true,
                'user_id' => null,
                'title' => $validated['title'],
                'content' => $validated['content'],
                'type' => $validated['type'],
                'priority' => $validated['priority'],
                'created_by' => Auth::id(),
            ]);
        } else {
            foreach ($validated['user_ids'] as $userId) {
                Notification::create([
                    'is_all' => false,
                    'user_id' => $userId,
                    'title' => $validated['title'],
                    'content' => $validated['content'],
                    'type' => $validated['type'],
                    'priority' => $validated['priority'],
                    'created_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('notifications.index')->with('success', 'Phát thông báo thành công.');
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        if (Auth::user()->isUser() && $notification->user_id !== Auth::id() && !$notification->is_all) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $notification->read_at = now();
        $notification->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu thông báo là đã đọc.',
                'read_at' => $notification->read_at->format('d/m/Y H:i')
            ]);
        }

        return redirect()->route('notifications.index')->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);

        if (Auth::user()->isUser() && $notification->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xóa thông báo này.');
        }

        $notification->delete();

        return redirect()->route('notifications.index')->with('success', 'Xóa thông báo thành công.');
    }
}
