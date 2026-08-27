@extends('layouts.app')

@section('title', 'Trợ Lý AI Tư Vấn Nông Nghiệp')

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
                        <div class="list-group-item list-group-item-action p-3 {{ $currentTopic && $currentTopic->id === $t->id ? 'active border-start border-4 border-primary bg-light text-dark' : '' }}">
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
                        <div class="p-3 text-center text-muted small">
                            Chưa có chủ đề nào. Nhấn dấu (+) để bắt đầu hỏi đáp.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9 col-md-8">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header p-3 d-flex justify-content-between align-items-center bg-white border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-icon" style="width: 38px; height: 38px; font-size: 20px;">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Trợ Lý Core AI Nông Nghiệp Bắc Ninh</h6>
                        <small class="text-success"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> Sẵn sàng trả lời giọng nói & văn bản</small>
                    </div>
                </div>
            </div>

            <div class="card-body p-4 flex-grow-1 overflow-auto" id="chat-messages-container" style="max-height: 480px;">
                <div class="d-flex gap-3 mb-4">
                    <div class="brand-icon flex-shrink-0" style="width: 36px; height: 36px; font-size: 18px;">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div class="p-3 rounded border" style="background-color: var(--bg-body); max-width: 80%;">
                        <div class="fw-bold text-dark small mb-1">Trợ lý Core AI</div>
                        <div class="text-secondary" style="font-size: 14px; line-height: 1.6;">
                            Xin chào! Tôi là Trợ lý AI nông nghiệp thông minh. Bác có thể đặt câu hỏi về triệu chứng sâu bệnh, quy trình bón phân, dự báo thời tiết hoặc ấn biểu tượng micro để hỏi bằng giọng nói trực tiếp nhé!
                        </div>
                    </div>
                </div>

                @if($messages)
                    @foreach($messages as $msg)
                        @if($msg->role === 'user' || $msg->sender === 'user')
                            <div class="d-flex gap-3 justify-content-end mb-4">
                                <div class="p-3 rounded text-white" style="background-color: var(--primary); max-width: 80%;">
                                    <div class="fw-bold small mb-1 opacity-75">Bạn</div>
                                    <div style="font-size: 14px; line-height: 1.6;">
                                        {{ $msg->content }}
                                    </div>
                                </div>
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            </div>
                        @else
                            <div class="d-flex gap-3 mb-4">
                                <div class="brand-icon flex-shrink-0" style="width: 36px; height: 36px; font-size: 18px;">
                                    <i class="bi bi-robot"></i>
                                </div>
                                <div class="p-3 rounded border" style="background-color: var(--bg-body); max-width: 80%;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark small">Trợ lý Core AI</span>
                                        <button class="btn btn-sm btn-link p-0 text-primary" onclick="showToast('Đang phát phản hồi âm thanh...', 'info')">
                                            <i class="bi bi-volume-up-fill fs-5"></i>
                                        </button>
                                    </div>
                                    <div class="text-secondary" style="font-size: 14px; line-height: 1.6;">
                                        {{ $msg->content }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <div class="card-footer p-3 bg-white border-top">
                <form id="chat-input-form" onsubmit="event.preventDefault(); sendChatMessage();">
                    <div class="input-group">
                        <button class="btn btn-secondary border" type="button" id="btn-voice-record" title="Ghi âm giọng nói hỏi đáp" onclick="toggleVoiceRecording()">
                            <i class="bi bi-mic-fill text-danger" id="voice-icon"></i>
                        </button>
                        <input type="text" id="chat-text-input" class="form-control" placeholder="Nhập câu hỏi kỹ thuật nông nghiệp tại đây...">
                        <button class="btn btn-primary px-4" type="submit">
                            <i class="bi bi-send-fill"></i> Gửi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
<script>
let isRecording = false;

function toggleVoiceRecording() {
    isRecording = !isRecording;
    const icon = document.getElementById('voice-icon');
    if (isRecording) {
        icon.classList.add('spinner-grow', 'spinner-grow-sm');
        showToast('Đang thu âm câu hỏi từ Micro... Nhấn lại để gửi tới Core AI', 'warning');
    } else {
        icon.classList.remove('spinner-grow', 'spinner-grow-sm');
        showToast('Core AI đã nhận diện giọng nói & đang truy vấn tri thức nông nghiệp!', 'success');
        document.getElementById('chat-text-input').value = 'Hướng dẫn thời điểm bao trái bưởi để tránh sâu đục cuống?';
    }
}

function sendChatMessage() {
    const input = document.getElementById('chat-text-input');
    const text = input.value.trim();
    if (!text) return;
    
    input.value = '';
    showToast('Core AI đang phân tích thực thể và tổng hợp tri thức...', 'info');
}

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
