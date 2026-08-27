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
        return $this->saveMessage($request);
    }

    public function saveMessage(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $conversationId = $request->input('conversation_id');
        $role = $request->input('role', 'user');
        $content = $request->input('content') ?? $request->input('message');

        if (empty($content)) {
            return response()->json(['success' => false, 'message' => 'Nội dung tin nhắn không được để trống'], 422);
        }

        // Auto-create or find conversation
        $conversation = null;
        if ($conversationId) {
            $conversation = ChatbotConversation::where('id', $conversationId)->where('user_id', $userId)->first();
        }

        if (!$conversation) {
            $title = \Illuminate\Support\Str::limit(trim(strip_tags($content)), 40, '...');
            $conversation = ChatbotConversation::create([
                'user_id' => $userId,
                'title' => $title ?: 'Chủ đề mới',
            ]);
            $conversationId = $conversation->id;
        }

        $message = ChatbotMessage::create([
            'conversation_id' => $conversationId,
            'role' => in_array($role, ['user', 'assistant']) ? $role : 'user',
            'content' => $content,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversationId,
            'conversation_title' => $conversation->title,
            'data' => $message,
        ]);
    }

    public function streamChat(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $query = $request->input('query');
        $topK = $request->input('top_k', 5);
        $conversationId = $request->input('conversation_id', null);

        $baseUrl = config('services.rag.base_url', 'http://127.0.0.1:9059/api/v1');
        $token = session('rag_jwt_token') ?? config('services.rag.default_token', '');

        $payload = json_encode([
            'query' => $query,
            'top_k' => (int) $topK,
            'conversation_id' => $conversationId,
        ]);

        return response()->stream(function () use ($baseUrl, $token, $payload) {
            // Turn off output buffering if active
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $ch = curl_init();
            $url = rtrim($baseUrl, '/') . '/chat/stream';

            $headers = [
                'Content-Type: application/json',
                'Accept: text/event-stream',
            ];
            if (!empty($token)) {
                $headers[] = 'Authorization: Bearer ' . $token;
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
                echo $data;
                flush();
                return strlen($data);
            });

            $success = curl_exec($ch);
            if (!$success) {
                $error = curl_error($ch);
                echo "data: " . json_encode(['type' => 'error', 'content' => 'Không thể kết nối đến máy chủ AI: ' . $error]) . "\n\n";
                flush();
            }
            curl_close($ch);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}

