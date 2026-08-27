<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Content\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::with('author');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $news = $query->latest()->paginate(10);

        return view('content.news', compact('news'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:news,slug',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'status' => 'required|string|in:draft,published',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . time();
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('uploads/news', 'public');
        }

        $validated['author_id'] = auth()->id() ?? 1;
        $validated['published_at'] = $validated['status'] === 'published' ? now() : null;

        News::create($validated);

        return redirect()->route('content.news.manage')->with('success', 'Thêm bài viết tin tức thành công.');
    }

    public function update(Request $request, $id)
    {
        $item = News::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'status' => 'nullable|string|in:draft,published',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'published' && !$item->published_at) {
            $validated['published_at'] = now();
        }

        $item->update($validated);

        return redirect()->route('content.news.manage')->with('success', 'Cập nhật tin tức thành công.');
    }

    public function destroy($id)
    {
        $item = News::findOrFail($id);
        $item->delete();

        return redirect()->route('content.news.manage')->with('success', 'Xóa bài viết tin tức thành công.');
    }
}
