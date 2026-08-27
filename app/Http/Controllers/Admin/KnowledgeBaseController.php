<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chatbot\ChatbotKnowledgeBase;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $query = ChatbotKnowledgeBase::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('question_pattern', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%")
                    ->orWhere('intent', 'like', "%{$search}%");
            });
        }

        if ($request->filled('intent')) {
            $query->where('intent', $request->input('intent'));
        }

        $items = $query->latest()->paginate(15);

        return view('chatbot.knowledge_base', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'intent' => 'required|string|max:100',
            'question_pattern' => 'required|string',
            'answer' => 'required|string',
            'entities' => 'nullable|array',
            'status' => 'required|string|in:active,inactive',
        ]);

        ChatbotKnowledgeBase::create($validated);

        return redirect()->back()->with('success', 'Thêm tri thức Chatbot AI thành công.');
    }

    public function update(Request $request, $id)
    {
        $item = ChatbotKnowledgeBase::findOrFail($id);

        $validated = $request->validate([
            'intent' => 'required|string|max:100',
            'question_pattern' => 'required|string',
            'answer' => 'required|string',
            'entities' => 'nullable|array',
            'status' => 'required|string|in:active,inactive',
        ]);

        $item->update($validated);

        return redirect()->back()->with('success', 'Cập nhật tri thức Chatbot AI thành công.');
    }

    public function destroy($id)
    {
        $item = ChatbotKnowledgeBase::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Xóa tri thức Chatbot thành công.');
    }
}
