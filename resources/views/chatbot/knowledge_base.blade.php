@extends('layouts.app')

@section('title', 'Quản Lý Cơ Sở Tri Thức Chatbot AI')

@section('content')
    <x-page-header title="Cơ Sở Tri Thức Chatbot AI">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>AI</span>
            <span>/</span>
            <span class="text-primary fw-bold">Tri thức Chatbot</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <button type="button" class="btn btn-primary" onclick="openModal('modal-add-kb')">
                <i class="bi bi-plus-circle-fill"></i> Thêm Mẫu Tri Thức
            </button>
        </x-slot:actions>
    </x-page-header>



    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ url('/chatbot/knowledge-base') }}" class="row g-3 align-items-center mb-4">
                <div class="col-lg-6 col-md-6">
                    <label class="form-label" style="font-size: 13px;">Tìm kiếm mẫu câu hỏi / câu trả lời</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Từ khóa câu hỏi, triệu chứng bệnh, hoạt chất thuốc..."
                            value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3">
                    <label class="form-label" style="font-size: 13px;">Ý định (Intent)</label>
                    <input type="text" name="intent" class="form-control" placeholder="Mã intent..."
                        value="{{ request('intent') }}">
                </div>

                <div class="col-lg-3 col-md-3 d-flex align-items-end gap-2" style="margin-top: 32px;">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Lọc</button>
                    @if (request('search') || request('intent'))
                        <a href="{{ url('/chatbot/knowledge-base') }}" class="btn btn-secondary" title="Đặt lại bộ lọc"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Ý định (Intent)</th>
                            <th>Mẫu câu hỏi huấn luyện</th>
                            <th>Câu trả lời của AI</th>
                            <th>Trạng thái</th>
                            <th style="width: 120px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $idx => $k)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $items->firstItem() + $idx }}</td>
                                <td><span class="badge-status badge-role"><i class="bi bi-tag-fill"></i>
                                        {{ $k->intent }}</span></td>
                                <td class="fw-bold text-dark" style="font-size: 13px; max-width: 260px;">
                                    {{ $k->question_pattern }}</td>
                                <td style="max-width: 380px;">
                                    <div class="text-secondary small">{{ $k->answer }}</div>
                                </td>
                                <td>
                                    <span
                                        class="badge-status {{ $k->status === 'active' ? 'badge-active' : 'badge-locked' }}">
                                        <i
                                            class="bi {{ $k->status === 'active' ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' }}"></i>
                                        {{ $k->status === 'active' ? 'Đang áp dụng' : 'Tạm ẩn' }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Sửa tri thức"
                                            onclick="openEditKbModal({{ $k->id }}, '{{ $k->intent }}', '{{ addslashes($k->question_pattern) }}', '{{ addslashes($k->answer) }}')">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </button>
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Xóa tri thức"
                                            onclick="openDeleteKbModal({{ $k->id }}, '{{ addslashes($k->question_pattern) }}')">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-database"></i></div>
                                        <h6 class="fw-bold mb-1">Chưa có mẫu tri thức nào</h6>
                                        <p class="text-muted small mb-0">Thêm mẫu câu hỏi và câu trả lời để huấn luyện
                                            Chatbot AI</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top"
                style="border-color: var(--border-color) !important;">
                <span class="text-muted" style="font-size: 14px;">
                    Hiển thị {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} trên tổng số
                    {{ $items->total() }} bản ghi
                </span>
                <div>
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="app-modal" id="modal-add-kb">
        <div class="modal-dialog" style="max-width: 600px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle text-primary"></i> Thêm Mẫu Tri Thức Huấn Luyện AI</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form action="{{ url('/chatbot/knowledge-base/store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mã ý định (Intent Code) <span class="text-danger">*</span></label>
                        <input type="text" name="intent" class="form-control"
                            placeholder="Ví dụ: downy_mildew_treatment" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mẫu câu hỏi (Question Pattern) <span class="text-danger">*</span></label>
                        <input type="text" name="question_pattern" class="form-control"
                            placeholder="Ví dụ: Bị sương mai lá cà chua phun thuốc gì?" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Câu trả lời tư vấn của AI <span class="text-danger">*</span></label>
                        <textarea name="answer" class="form-control" rows="4" placeholder="Nội dung giải đáp chi tiết..." required></textarea>
                    </div>
                    <input type="hidden" name="status" value="active">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu Tri Thức</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-edit-kb">
        <div class="modal-dialog" style="max-width: 600px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil text-primary"></i> Sửa Mẫu Tri Thức</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-edit-kb" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mã ý định</label>
                        <input type="text" name="intent" id="edit-kb-intent" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mẫu câu hỏi</label>
                        <input type="text" name="question_pattern" id="edit-kb-pattern" class="form-control"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Câu trả lời</label>
                        <textarea name="answer" id="edit-kb-answer" class="form-control" rows="4" required></textarea>
                    </div>
                    <input type="hidden" name="status" value="active">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-delete-kb">
        <div class="modal-dialog" style="max-width: 420px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Tri Thức</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-delete-kb" action="" method="POST">
                @csrf
                <div class="modal-body text-center py-4">
                    <p>Bạn có chắc muốn xóa mẫu tri thức: <br><strong id="delete-kb-pattern"
                            class="text-danger"></strong>?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác Nhận Xóa</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openEditKbModal(id, intent, pattern, answer) {
            document.getElementById('form-edit-kb').action = window.location.origin + '/chatbot/knowledge-base/update/' +
                id;
            document.getElementById('edit-kb-intent').value = intent;
            document.getElementById('edit-kb-pattern').value = pattern;
            document.getElementById('edit-kb-answer').value = answer;
            openModal('modal-edit-kb');
        }

        function openDeleteKbModal(id, pattern) {
            document.getElementById('form-delete-kb').action = window.location.origin + '/chatbot/knowledge-base/delete/' +
                id;
            document.getElementById('delete-kb-pattern').textContent = pattern;
            openModal('modal-delete-kb');
        }
    </script>
@endpush
