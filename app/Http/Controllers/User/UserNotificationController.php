<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    public function index()
    {
        $userId = Auth::id() ?? 1;
        $notifications = Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('is_all', true);
        })
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id() ?? 1)->findOrFail($id);
        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id() ?? 1)->findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }
}
