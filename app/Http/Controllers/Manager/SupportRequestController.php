<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportRequest::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $requests = $query->latest()->paginate(15);

        return view('support.index', compact('requests'));
    }

    public function reply(Request $request)
    {
        $validated = $request->validate([
            'support_request_id' => 'required|exists:support_requests,id',
            'content' => 'required|string',
        ]);

        $item = SupportRequest::findOrFail($validated['support_request_id']);
        $item->reply_content = $validated['content'];
        $item->status = 'replied';
        $item->replied_at = now();
        $item->save();

        return redirect()->route('support.manage')->with('success', 'Gửi phản hồi cho người dân thành công.');
    }

    public function destroy($id)
    {
        $item = SupportRequest::findOrFail($id);
        $item->delete();

        return redirect()->route('support.manage')->with('success', 'Xóa đơn liên hệ hỗ trợ thành công.');
    }
}
