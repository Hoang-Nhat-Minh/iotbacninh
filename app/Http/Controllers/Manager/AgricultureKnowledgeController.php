<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Content\KnowledgeArticle;
use App\Models\Content\KnowledgeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgricultureKnowledgeController extends Controller
{
    public function index(Request $request)
    {
        $query = KnowledgeArticle::with('category');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('knowledge_category_id', $request->input('category_id'));
        }

        $knowledge = $query->latest()->paginate(10);
        $categories = KnowledgeCategory::all();

        return view('content.knowledge', compact('knowledge', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'knowledge_category_id' => 'required|exists:knowledge_categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|string|in:draft,published',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . time();
        $validated['author_id'] = auth()->id() ?? 1;

        KnowledgeArticle::create($validated);

        return redirect()->route('content.knowledge.manage')->with('success', 'Thêm cẩm nang kiến thức nông nghiệp thành công.');
    }

    public function update(Request $request, $id)
    {
        $item = KnowledgeArticle::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'knowledge_category_id' => 'nullable|exists:knowledge_categories,id',
            'status' => 'nullable|string|in:draft,published',
        ]);

        $item->update($validated);

        return redirect()->route('content.knowledge.manage')->with('success', 'Cập nhật kiến thức thành công.');
    }

    public function destroy($id)
    {
        $item = KnowledgeArticle::findOrFail($id);
        $item->delete();

        return redirect()->route('content.knowledge.manage')->with('success', 'Xóa bài viết kiến thức thành công.');
    }
}
