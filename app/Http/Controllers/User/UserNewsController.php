<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Content\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNewsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = News::where('status', 'published')->with('author');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('bookmarked') && $user) {
            $bookmarkedIds = $user->bookmarkedNews()->pluck('news_id')->toArray();
            $query->whereIn('id', $bookmarkedIds);
        }

        $news = $query->latest('published_at')->latest()->paginate(9)->withQueryString();

        $bookmarkedIds = $user ? $user->bookmarkedNews()->pluck('news_id')->toArray() : [];

        return view('content.user_news', compact('news', 'bookmarkedIds'));
    }

    public function toggleBookmark($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện.'], 401);
        }

        $item = News::findOrFail($id);
        $user->bookmarkedNews()->toggle($id);

        $isBookmarked = $user->bookmarkedNews()->where('news_id', $id)->exists();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_bookmarked' => $isBookmarked,
                'message' => $isBookmarked ? 'Đã đánh dấu là tin tức quan trọng.' : 'Đã bỏ đánh dấu tin tức.'
            ]);
        }

        return back()->with('success', $isBookmarked ? 'Đã đánh dấu là tin tức quan trọng.' : 'Đã bỏ đánh dấu tin tức.');
    }
}
