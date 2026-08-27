<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Content\KnowledgeArticle;
use App\Models\Content\KnowledgeCategory;
use Illuminate\Http\Request;

class UserKnowledgeController extends Controller
{
    public function index(Request $request)
    {
        $query = KnowledgeArticle::where('status', 'published')->with('category', 'creator');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $articles = $query->latest()->paginate(9)->withQueryString();
        $categories = KnowledgeCategory::all();

        return view('content.user_knowledge', compact('articles', 'categories'));
    }
}
