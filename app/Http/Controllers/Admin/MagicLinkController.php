<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MagicLinkController extends Controller
{
    /**
     * Danh sách magic link đã tạo.
     */
    public function index(Request $request)
    {
        $query = MagicLink::with(['user', 'creator'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('token', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->input('is_active') === '1');
        }

        $magicLinks = $query->paginate(15)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.magic-links.index', compact('magicLinks', 'users'));
    }

    /**
     * Hiển thị form tạo magic link mới.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();

        return view('admin.magic-links.create', compact('users'));
    }

    /**
     * Lưu magic link mới.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $magicLink = MagicLink::create([
            'user_id' => $validated['user_id'],
            'token' => Str::random(64),
            'name' => $validated['name'] ?? null,
            'is_active' => true,
            'expires_at' => $validated['expires_at'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'used_count' => 0,
            'created_by' => Auth::id(),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return redirect()->route('admin.magic-links.index')
            ->with('success', 'Tạo magic link thành công. Link: ' . $magicLink->url);
    }

    /**
     * Hiển thị form chỉnh sửa magic link.
     */
    public function edit(MagicLink $magicLink)
    {
        $users = User::orderBy('name')->get();

        return view('admin.magic-links.edit', compact('magicLink', 'users'));
    }

    /**
     * Cập nhật magic link.
     */
    public function update(Request $request, MagicLink $magicLink)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
            'max_uses' => 'nullable|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $magicLink->update([
            'user_id' => $validated['user_id'],
            'name' => $validated['name'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()->route('admin.magic-links.index')
            ->with('success', 'Cập nhật magic link thành công.');
    }

    /**
     * Bật/tắt trạng thái magic link (active <-> inactive).
     */
    public function toggle(MagicLink $magicLink)
    {
        $magicLink->update([
            'is_active' => !$magicLink->is_active,
        ]);

        $status = $magicLink->is_active ? 'kích hoạt' : 'vô hiệu hóa';

        return redirect()->route('admin.magic-links.index')
            ->with('success', "Đã {$status} magic link.");
    }

    /**
     * Xóa magic link.
     */
    public function destroy(MagicLink $magicLink)
    {
        $magicLink->delete();

        return redirect()->route('admin.magic-links.index')
            ->with('success', 'Xóa magic link thành công.');
    }
}