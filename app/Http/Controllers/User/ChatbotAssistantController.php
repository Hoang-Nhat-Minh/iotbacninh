<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Chatbot\ChatbotConversation;
use App\Models\Chatbot\ChatbotMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotAssistantController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $topics = ChatbotConversation::where('user_id', $userId)->latest()->get();
        
        $currentTopicId = $request->input('topic_id');
        $currentTopic = $currentTopicId ? $topics->where('id', $currentTopicId)->first() : $topics->first();
        $messages = $currentTopic ? $currentTopic->messages()->oldest()->get() : collect();

        return view('chatbot.index', compact('topics', 'currentTopic', 'messages'));
    }

    public function storeTopic(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $validated['user_id'] = Auth::id() ?? 1;
        ChatbotConversation::create($validated);

        return redirect()->route('chatbot.index')->with('success', 'Tạo chủ đề tư vấn mới thành công.');
    }

    public function updateTopic(Request $request, $id)
    {
        $topic = ChatbotConversation::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $topic->update($validated);

        return redirect()->route('chatbot.index')->with('success', 'Đổi tên chủ đề thành công.');
    }

    public function destroyTopic($id)
    {
        $topic = ChatbotConversation::findOrFail($id);
        $topic->delete();

        return redirect()->route('chatbot.index')->with('success', 'Xóa chủ đề thành công.');
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:chatbot_conversations,id',
            'message' => 'required|string',
        ]);

        ChatbotMessage::create([
            'conversation_id' => $validated['conversation_id'],
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $reply = ChatbotMessage::create([
            'conversation_id' => $validated['conversation_id'],
            'role' => 'assistant',
            'content' => 'Core AI đã tiếp nhận câu hỏi và tra cứu tri thức kỹ thuật nông nghiệp...',
        ]);

        return response()->json(['success' => true, 'data' => $reply]);
    }
}
