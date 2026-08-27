@extends('layouts.app')

@section('title', 'Trợ Lý AI Tư Vấn Nông Nghiệp')

@push('styles')
<style>
    .ai-markdown-content p { margin-bottom: 0.6rem; }
    .ai-markdown-content p:last-child { margin-bottom: 0; }
    .ai-markdown-content ul, .ai-markdown-content ol { margin-left: 1.25rem; margin-bottom: 0.6rem; }
    .ai-markdown-content table { width: 100%; border-collapse: collapse; margin: 0.5rem 0; font-size: 13px; }
    .ai-markdown-content table, .ai-markdown-content th, .ai-markdown-content td { border: 1px solid rgba(0,0,0,0.1); }
    .ai-markdown-content th, .ai-markdown-content td { padding: 6px 10px; }
    .ai-markdown-content th { background-color: rgba(0,0,0,0.03); }
    .ai-markdown-content code { background-color: rgba(0,0,0,0.06); padding: 2px 5px; border-radius: 4px; font-family: monospace; font-size: 13px; }
    .ai-markdown-content pre { background-color: #1e293b; color: #f8fafc; padding: 10px; border-radius: 6px; overflow-x: auto; margin-bottom: 0.6rem; }
    .ai-markdown-content pre code { background-color: transparent; color: inherit; padding: 0; }
    .ai-markdown-content blockquote { border-left: 3px solid var(--primary); padding-left: 10px; margin: 0.5rem 0; color: #64748b; font-style: italic; }
    
    .citation-badge {
        font-size: 11px !important;
        font-weight: 600;
        vertical-align: super;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .citation-badge:hover {
        transform: scale(1.08);
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    
    .cursor-blink {
        display: inline-block;
        width: 6px;
        height: 15px;
        background-color: var(--primary);
        vertical-align: middle;
        margin-left: 2px;
        animation: blink 0.8s infinite;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0; }
    }
    
    .mic-recording {
        animation: pulse-red 1.2s infinite;
        background-color: #dc3545 !important;
        color: white !important;
        border-color: #dc3545 !important;
    }
    @keyframes pulse-red {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.6); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
</style>
@endpush

@section('content')
<x-page-header title="Trợ Lý AI Tư Vấn Nông Nghiệp">
    <x-slot:breadcrumbs>
        <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span class="text-primary fw-bold">Trợ lý AI</span>
    </x-slot:breadcrumbs>
</x-page-header>

<div class="row g-3" style="min-height: 600px;">
    <!-- Cột danh sách chủ đề -->
    <div class="col-lg-3 col-md-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center p-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-chat-left-text text-primary"></i> Chủ Đề Tư Vấn</h6>
                <button class="btn btn-primary btn-sm px-2 py-1" onclick="openModal('modal-add-topic')" title="Tạo chủ đề mới">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="topic-list">
                    @forelse($topics as $t)
                        <div class="list-group-item list-group-item-action p-3 {{ $currentTopic && $currentTopic->id === $t->id ? 'active border-start border-4 border-primary bg-light text-dark' : '' }}" id="topic-item-{{ $t->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <a href="?topic_id={{ $t->id }}" class="fw-bold text-truncate text-decoration-none {{ $currentTopic && $currentTopic->id === $t->id ? 'text-primary' : 'text-dark' }}" style="font-size: 13px;">
                                    {{ $t->title }}
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-link btn-sm text-dark p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item small" href="javascript:void(0)" onclick="openEditTopicModal({{ $t->id }}, '{{ addslashes($t->title) }}')"><i class="bi bi-pencil me-1"></i> Đổi tên</a></li>
                                        <li><a class="dropdown-item small text-danger" href="javascript:void(0)" onclick="openDeleteTopicModal({{ $t->id }}, '{{ addslashes($t->title) }}')"><i class="bi bi-trash me-1"></i> Xóa</a></li>
                                    </ul>
                                </div>
                            </div>
                            <small class="text-muted">{{ $t->created_at ? $t->created_at->format('d/m/Y H:i') : '' }}</small>
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted small" id="no-topic-placeholder">
                            Chưa có chủ đề nào. Nhấn dấu (+) hoặc gửi câu hỏi để bắt đầu hỏi đáp.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Cột khung chat AI -->
    <div class="col-lg-9 col-md-8">
        <div class="card h-100 d-flex flex-column shadow-sm">
            <div class="card-header p-3 d-flex justify-content-between align-items-center bg-white border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-icon" style="width: 38px; height: 38px; font-size: 20px;">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Trợ Lý Core AI Nông Nghiệp Bắc Ninh</h6>
                        <small class="text-success" id="ai-status-indicator">
                            <i class="bi bi-circle-fill" style="font-size: 8px;"></i> Sẵn sàng kết nối RAG & Giọng nói
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 11px;">
                        <i class="bi bi-cpu text-primary me-1"></i> RAG AI Server
                    </span>
                </div>
            </div>

            <!-- Khung cuộn tin nhắn -->
            <div class="card-body p-4 flex-grow-1 overflow-auto" id="chat-messages-container" style="max-height: 480px; min-height: 400px;">
                <!-- Lời chào mặc định -->
                <div class="d-flex gap-3 mb-4">
                    <div class="brand-icon flex-shrink-0" style="width: 36px; height: 36px; font-size: 18px;">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div class="p-3 rounded border" style="background-color: var(--bg-body); max-width: 85%;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark small">Trợ lý Core AI</span>
                            <button class="btn btn-sm btn-link p-0 text-secondary" onclick="speakText(this)" title="Nghe đọc lời chào">
                                <i class="bi bi-volume-up-fill fs-6"></i>
                            </button>
                        </div>
                        <div class="text-secondary ai-markdown-content" style="font-size: 14px; line-height: 1.6;">
                            Xin chào! Tôi là Trợ lý AI nông nghiệp thông minh tỉnh Bắc Ninh. Bạn có thể hỏi về triệu chứng sâu bệnh, kỹ thuật bón phân, lịch gieo trồng hoặc ấn biểu tượng <strong>Micro</strong> để hỏi đáp trực tiếp bằng giọng nói!
                        </div>
                    </div>
                </div>

                <!-- Tin nhắn từ cơ sở dữ liệu -->
                @if($messages)
                    @foreach($messages as $msg)
                        @if($msg->role === 'user' || $msg->sender === 'user')
                            <div class="d-flex gap-3 justify-content-end mb-4 message-row user-row">
                                <div class="p-3 rounded text-white shadow-sm" style="background-color: var(--primary); max-width: 80%;">
                                    <div class="fw-bold small mb-1 opacity-75">Bạn</div>
                                    <div style="font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $msg->content }}</div>
                                </div>
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            </div>
                        @else
                            <div class="d-flex gap-3 mb-4 message-row ai-row">
                                <div class="brand-icon flex-shrink-0" style="width: 36px; height: 36px; font-size: 18px;">
                                    <i class="bi bi-robot"></i>
                                </div>
                                <div class="p-3 rounded border shadow-sm" style="background-color: var(--bg-body); max-width: 85%;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark small">Trợ lý Core AI</span>
                                        <button class="btn btn-sm btn-link p-0 text-secondary hover-primary" onclick="speakText(this)" title="Nghe đọc">
                                            <i class="bi bi-volume-up-fill fs-6"></i>
                                        </button>
                                    </div>
                                    <div class="text-secondary ai-markdown-content history-markdown" style="font-size: 14px; line-height: 1.6;">{{ $msg->content }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Khung nhập liệu & Nút gửi -->
            <div class="card-footer p-3 bg-white border-top">
                <form id="chat-input-form" onsubmit="event.preventDefault(); sendChatMessage();">
                    <div class="input-group shadow-sm">
                        <button class="btn btn-outline-secondary" type="button" id="btn-voice-record" title="Ghi âm giọng nói hỏi đáp" onclick="toggleVoiceRecording()">
                            <i class="bi bi-mic-fill text-danger" id="voice-icon"></i>
                        </button>
                        <input type="text" id="chat-text-input" class="form-control" placeholder="Nhập câu hỏi kỹ thuật nông nghiệp hoặc bấm micro để nói..." autocomplete="off">
                        <button class="btn btn-primary px-4" type="submit" id="btn-chat-send">
                            <i class="bi bi-send-fill me-1"></i> Gửi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Chủ Đề -->
<div class="app-modal" id="modal-add-topic">
    <div class="modal-dialog" style="max-width: 440px;">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-plus-circle text-primary"></i> Tạo Chủ Đề Tư Vấn Mới</h5>
            <button type="button" class="modal-close-btn">&times;</button>
        </div>
        <form action="{{ url('/chatbot/topics/store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tiêu đề chủ đề <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Ví dụ: Kỹ thuật tỉa cành vụ thu" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                <button type="submit" class="btn btn-primary">Tạo Chủ Đề</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Đổi Tên Chủ Đề -->
<div class="app-modal" id="modal-edit-topic">
    <div class="modal-dialog" style="max-width: 440px;">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-pencil text-primary"></i> Đổi Tiêu Đề Chủ Đề</h5>
            <button type="button" class="modal-close-btn">&times;</button>
        </div>
        <form id="form-edit-topic" action="" method="POST">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tiêu đề mới</label>
                    <input type="text" name="title" id="edit-topic-title" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Xóa Chủ Đề -->
<div class="app-modal" id="modal-delete-topic">
    <div class="modal-dialog" style="max-width: 420px;">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Chủ Đề</h5>
            <button type="button" class="modal-close-btn">&times;</button>
        </div>
        <form id="form-delete-topic" action="" method="POST">
            @csrf
            <div class="modal-body text-center py-4">
                <p>Bạn có chắc muốn xóa toàn bộ lịch sử tư vấn chủ đề: <br><strong id="delete-topic-name" class="text-danger"></strong>?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                <button type="submit" class="btn btn-danger">Xóa Ngay</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Thư viện Marked.js & DOMPurify để render Markdown & Bảo mật XSS an toàn -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>

<script>
let currentTopicId = @json($currentTopic ? $currentTopic->id : null);
let isWaitingForResponse = false;
let speechRecognition = null;
let isRecordingVoice = false;

// 1. Cấu hình RAG Backend & Token
const getRagConfig = () => {
    const baseUrlMeta = document.querySelector('meta[name="rag-api-base"]')?.content;
    const tokenMeta = document.querySelector('meta[name="rag-api-token"]')?.content;
    const csrfMeta = document.querySelector('meta[name="csrf-token"]')?.content;
    return {
        baseUrl: baseUrlMeta || 'http://117.6.44.206:9059/api/v1',
        token: tokenMeta || '',
        csrfToken: csrfMeta || ''
    };
};

// 2. Format Markdown & Citations Tooltips (theo FRONTEND_GUIDE.md)
function formatAnswerWithCitations(answer, sources) {
    if (!answer) return '';
    
    // Regex tìm các chuỗi dạng [Source 1], [Source 2]...
    const regex = /\[Source (\d+)\]/g;
    
    let processedText = answer.replace(regex, (match, numberStr) => {
        const index = parseInt(numberStr, 10) - 1;
        if (sources && sources[index]) {
            const src = sources[index];
            const sourceContent = src.content || '';
            const docId = src.document_id ? `<small class="text-muted d-block mt-1">ID: ${src.document_id}</small>` : '';
            const cleanContent = sourceContent.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            
            return `<sup class="badge bg-primary-subtle text-primary border border-primary-subtle citation-badge ms-1"
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        data-bs-html="true"
                        title="<strong>Trích dẫn [Nguồn ${numberStr}]:</strong><br>${cleanContent}${docId}">
                        [Nguồn ${numberStr}]
                    </sup>`;
        }
        return match;
    });

    const rawHtml = marked.parse(processedText);
    return DOMPurify.sanitize(rawHtml);
}

// Khởi tạo render markdown cho các tin nhắn lịch sử sẵn có
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.history-markdown').forEach(el => {
        const text = el.textContent || '';
        el.innerHTML = formatAnswerWithCitations(text, null);
    });
    initTooltips();
    scrollToBottom();
});

function initTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

function scrollToBottom() {
    const container = document.getElementById('chat-messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

// 3. Logic Gửi Tin Nhắn & Xử lý SSE Streaming
async function sendChatMessage() {
    if (isWaitingForResponse) return;
    
    const input = document.getElementById('chat-text-input');
    const text = input.value.trim();
    if (!text) return;
    
    // Clear input
    input.value = '';
    
    // Hiển thị tin nhắn người dùng lên giao diện
    appendUserBubble(text);
    
    // Chuẩn bị khung tin nhắn AI dạng Stream
    const aiBubbleId = 'ai-bubble-' + Date.now();
    const aiContentEl = appendAiBubbleSkeleton(aiBubbleId);
    
    // Khóa nút gửi
    setLoadingState(true);
    
    const { baseUrl, token, csrfToken } = getRagConfig();
    
    // 3.1. Lưu tin nhắn người dùng vào DB Laravel qua AJAX
    try {
        const saveRes = await fetch('{{ url("/chatbot/message") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: currentTopicId,
                role: 'user',
                content: text
            })
        });
        const saveJson = await saveRes.json();
        if (saveJson.success && saveJson.conversation_id && !currentTopicId) {
            currentTopicId = saveJson.conversation_id;
            // Nếu là chủ đề mới, cập nhật URL mà không reload
            window.history.replaceState(null, '', `?topic_id=${currentTopicId}`);
        }
    } catch (e) {
        console.warn('Lỗi lưu tin nhắn user vào database:', e);
    }
    
    // 3.2. Gọi Stream API tới AI RAG Backend
    let accumulatedText = "";
    let capturedSources = [];
    
    const headers = {
        'Content-Type': 'application/json'
    };
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    
    try {
        const requestBody = {
            query: text,
            top_k: 5
        };
        if (currentTopicId) {
            requestBody.conversation_id = String(currentTopicId);
        }
        
        const response = await fetch(`${baseUrl}/chat/stream`, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(requestBody)
        });
        
        if (!response.ok) {
            let errorMsg = "Không thể kết nối đến máy chủ AI.";
            try {
                const errData = await response.json();
                errorMsg = errData.error?.message || errData.message || errorMsg;
            } catch (_) {}
            
            aiContentEl.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Lỗi phản hồi từ AI: ${errorMsg}</span>`;
            setLoadingState(false);
            return;
        }
        
        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let buffer = '';
        
        while (true) {
            const { value, done } = await reader.read();
            if (done) break;
            
            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop(); // Giữ lại dòng chưa hoàn thiện
            
            for (const line of lines) {
                const trimmed = line.trim();
                if (trimmed.startsWith('data: ')) {
                    const dataStr = trimmed.substring(6).trim();
                    if (!dataStr || dataStr === '[DONE]') continue;
                    
                    try {
                        const data = JSON.parse(dataStr);
                        if (data.type === 'token') {
                            accumulatedText += data.content;
                            // Render Markdown tạm thời kèm con trỏ nhấp nháy
                            aiContentEl.innerHTML = DOMPurify.sanitize(marked.parse(accumulatedText)) + '<span class="cursor-blink"></span>';
                            scrollToBottom();
                        } else if (data.type === 'done') {
                            capturedSources = data.sources || [];
                        }
                    } catch (errParse) {
                        // Nếu stream gửi dạng text thô
                        accumulatedText += dataStr;
                        aiContentEl.innerHTML = DOMPurify.sanitize(marked.parse(accumulatedText)) + '<span class="cursor-blink"></span>';
                        scrollToBottom();
                    }
                }
            }
        }
        
        // Hoàn tất stream: Format Citations & Render đẹp
        aiContentEl.innerHTML = formatAnswerWithCitations(accumulatedText, capturedSources);
        
        // Thêm footer danh sách nguồn tài liệu tham khảo nếu có
        if (capturedSources && capturedSources.length > 0) {
            let sourcesFooter = `<div class="mt-3 pt-2 border-top small text-muted">
                <div class="fw-bold mb-1 text-dark"><i class="bi bi-journal-bookmark text-primary"></i> Tài liệu trích dẫn (${capturedSources.length}):</div>
                <div class="d-flex flex-wrap gap-1">`;
            
            capturedSources.forEach((src, idx) => {
                const srcNum = idx + 1;
                const preview = (src.content || '').substring(0, 120).replace(/"/g, '&quot;') + '...';
                sourcesFooter += `<span class="badge bg-light text-dark border citation-badge" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-html="true"
                                        title="${preview}">
                                    [Nguồn ${srcNum}] ${src.document_id ? src.document_id : 'Tài liệu KT'}
                                </span>`;
            });
            sourcesFooter += `</div></div>`;
            aiContentEl.innerHTML += DOMPurify.sanitize(sourcesFooter);
        }
        
        initTooltips();
        scrollToBottom();
        
        // 3.3. Lưu câu trả lời của AI vào DB Laravel qua AJAX
        if (currentTopicId && accumulatedText) {
            fetch('{{ url("/chatbot/message") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    conversation_id: currentTopicId,
                    role: 'assistant',
                    content: accumulatedText
                })
            }).catch(e => console.warn('Lỗi lưu tin nhắn AI vào database:', e));
        }
        
    } catch (err) {
        console.error('Lỗi khi gọi RAG AI Stream:', err);
        aiContentEl.innerHTML = `<span class="text-danger"><i class="bi bi-wifi-off me-1"></i> Không thể kết nối tới máy chủ AI RAG (<code>${baseUrl}</code>). Vui lòng kiểm tra lại dịch vụ backend.</span>`;
    } finally {
        setLoadingState(false);
        scrollToBottom();
    }
}

// 4. UI Helper Functions
function appendUserBubble(text) {
    const container = document.getElementById('chat-messages-container');
    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex gap-3 justify-content-end mb-4 message-row user-row';
    
    wrapper.innerHTML = `
        <div class="p-3 rounded text-white shadow-sm" style="background-color: var(--primary); max-width: 80%;">
            <div class="fw-bold small mb-1 opacity-75">Bạn</div>
            <div style="font-size: 14px; line-height: 1.6; white-space: pre-wrap;">${escapeHtml(text)}</div>
        </div>
        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
            <i class="bi bi-person-fill"></i>
        </div>
    `;
    container.appendChild(wrapper);
    scrollToBottom();
}

function appendAiBubbleSkeleton(bubbleId) {
    const container = document.getElementById('chat-messages-container');
    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex gap-3 mb-4 message-row ai-row';
    
    wrapper.innerHTML = `
        <div class="brand-icon flex-shrink-0" style="width: 36px; height: 36px; font-size: 18px;">
            <i class="bi bi-robot"></i>
        </div>
        <div class="p-3 rounded border shadow-sm" style="background-color: var(--bg-body); max-width: 85%;">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-bold text-dark small">Trợ lý Core AI</span>
                <button class="btn btn-sm btn-link p-0 text-secondary hover-primary" onclick="speakText(this)" title="Nghe đọc">
                    <i class="bi bi-volume-up-fill fs-6"></i>
                </button>
            </div>
            <div class="text-secondary ai-markdown-content" id="${bubbleId}" style="font-size: 14px; line-height: 1.6;">
                <span class="text-muted"><i class="spinner-border spinner-border-sm me-1"></i> Đang truy vấn tri thức RAG...</span>
            </div>
        </div>
    `;
    container.appendChild(wrapper);
    scrollToBottom();
    return document.getElementById(bubbleId);
}

function setLoadingState(isLoading) {
    isWaitingForResponse = isLoading;
    const sendBtn = document.getElementById('btn-chat-send');
    const input = document.getElementById('chat-text-input');
    
    if (isLoading) {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang trả lời...';
        input.disabled = true;
    } else {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Gửi';
        input.disabled = false;
        input.focus();
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// 5. Tính Năng Giọng Nói (Web Speech API)
function toggleVoiceRecording() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        showToast('Trình duyệt của bạn không hỗ trợ nhận diện giọng nói (Web Speech API). Hãy dùng Chrome/Edge.', 'warning');
        return;
    }

    const btn = document.getElementById('btn-voice-record');
    const input = document.getElementById('chat-text-input');

    if (isRecordingVoice && speechRecognition) {
        speechRecognition.stop();
        return;
    }

    speechRecognition = new SpeechRecognition();
    speechRecognition.lang = 'vi-VN';
    speechRecognition.interimResults = true;
    speechRecognition.continuous = false;

    speechRecognition.onstart = function() {
        isRecordingVoice = true;
        btn.classList.add('mic-recording');
        showToast('Đang lắng nghe... Hãy nói câu hỏi kỹ thuật nông nghiệp', 'info');
    };

    speechRecognition.onresult = function(event) {
        let transcript = '';
        for (let i = event.resultIndex; i < event.results.length; ++i) {
            transcript += event.results[i][0].transcript;
        }
        input.value = transcript;
    };

    speechRecognition.onerror = function(event) {
        console.warn('Lỗi ghi âm:', event.error);
        showToast('Lỗi nhận diện âm thanh: ' + event.error, 'danger');
        stopVoiceRecordingUI();
    };

    speechRecognition.onend = function() {
        stopVoiceRecordingUI();
        if (input.value.trim().length > 0) {
            showToast('Đã nhận diện giọng nói! Đang gửi câu hỏi...', 'success');
            sendChatMessage();
        }
    };

    speechRecognition.start();
}

function stopVoiceRecordingUI() {
    isRecordingVoice = false;
    const btn = document.getElementById('btn-voice-record');
    if (btn) btn.classList.remove('mic-recording');
}

function speakText(buttonEl) {
    if (!('speechSynthesis' in window)) {
        showToast('Trình duyệt không hỗ trợ Text-to-Speech', 'warning');
        return;
    }

    if (window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
        showToast('Đã dừng phát âm thanh', 'info');
        return;
    }

    const bubble = buttonEl.closest('.ai-row, .card-body')?.querySelector('.ai-markdown-content');
    if (!bubble) return;

    // Lấy plain text không dính tag HTML
    const cleanText = bubble.innerText || bubble.textContent;
    if (!cleanText.trim()) return;

    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.lang = 'vi-VN';
    utterance.rate = 1.0;
    
    utterance.onstart = function() {
        buttonEl.innerHTML = '<i class="bi bi-volume-mute-fill text-danger fs-6"></i>';
        showToast('Đang phát giọng đọc...', 'info');
    };

    utterance.onend = function() {
        buttonEl.innerHTML = '<i class="bi bi-volume-up-fill fs-6"></i>';
    };

    utterance.onerror = function() {
        buttonEl.innerHTML = '<i class="bi bi-volume-up-fill fs-6"></i>';
    };

    window.speechSynthesis.speak(utterance);
}

// 6. Modal Handlers
function openEditTopicModal(id, title) {
    document.getElementById('form-edit-topic').action = window.location.origin + '/chatbot/topics/update/' + id;
    document.getElementById('edit-topic-title').value = title;
    openModal('modal-edit-topic');
}

function openDeleteTopicModal(id, title) {
    document.getElementById('form-delete-topic').action = window.location.origin + '/chatbot/topics/delete/' + id;
    document.getElementById('delete-topic-name').textContent = title;
    openModal('modal-delete-topic');
}
</script>
@endpush

