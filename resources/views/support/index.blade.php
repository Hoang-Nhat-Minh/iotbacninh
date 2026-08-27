@extends('layouts.app')

@section('title', 'Quản Lý Hòm Thư Liên Hệ & Hỗ Trợ')

@section('content')
    <x-page-header title="Hòm Thư Yêu Cầu Hỗ Trợ">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Hỗ trợ</span>
            <span>/</span>
            <span class="text-primary fw-bold">Hòm thư</span>
        </x-slot:breadcrumbs>
    </x-page-header>



    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('support.manage') }}" class="row g-3 align-items-center mb-4">
                <div class="col-lg-6 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Tên người gửi, số điện thoại, nội dung yêu cầu..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chưa phản hồi (Mới)
                        </option>
                        <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Đã phản hồi</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Lọc</button>
                    @if (request('search') || request('status'))
                        <a href="{{ route('support.manage') }}" class="btn btn-secondary" title="Đặt lại bộ lọc"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Người gửi & Liên hệ</th>
                            <th>Nội dung yêu cầu hỗ trợ</th>
                            <th>Trạng thái</th>
                            <th>Thời gian gửi</th>
                            <th style="width: 140px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $idx => $r)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $requests->firstItem() + $idx }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $r->name }}</div>
                                    <div class="text-muted small"><i class="bi bi-telephone"></i> {{ $r->phone }}
                                        &bull; {{ $r->email ?? 'Chưa có email' }}</div>
                                </td>
                                <td style="max-width: 380px;">
                                    <div class="text-dark" style="font-size: 14px; line-height: 1.5;">{{ $r->content }}
                                    </div>
                                    @if ($r->reply_content)
                                        <div class="mt-2 p-2 rounded bg-light border"
                                            style="font-size: 12px; color: var(--primary);">
                                            <i class="bi bi-reply-fill"></i> <strong>Đã phản hồi:</strong>
                                            {{ $r->reply_content }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($r->status === 'pending')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Đang
                                            chờ xử lý</span>
                                    @else
                                        <span class="badge bg-success"><i class="bi bi-check-all"></i> Đã phản hồi</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $r->created_at ? $r->created_at->format('d/m/Y H:i') : '' }}</td>
                                <td style="text-align: center;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-primary btn-sm px-2 py-1" title="Phản hồi yêu cầu"
                                            onclick="openReplyModal({{ $r->id }}, '{{ addslashes($r->name) }}', '{{ addslashes($r->content) }}', '{{ addslashes($r->reply_content ?? '') }}')">
                                            <i class="bi bi-reply-fill"></i> Phản hồi
                                        </button>
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Xóa đơn"
                                            onclick="openDeleteSupportModal({{ $r->id }}, '{{ addslashes($r->name) }}')">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-envelope-open"></i></div>
                                        <h6 class="fw-bold mb-1">Hòm thư hỗ trợ đang trống</h6>
                                        <p class="text-muted small mb-0">Các thắc mắc và phản hồi từ nông dân sẽ hiển thị
                                            tại đây</p>
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
                    Hiển thị {{ $requests->firstItem() ?? 0 }} - {{ $requests->lastItem() ?? 0 }} trên tổng số
                    {{ $requests->total() }} yêu cầu
                </span>
                <div>
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="app-modal" id="modal-reply-support">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-reply-fill text-primary"></i> Phản Hồi Yêu Cầu Của Người Dân</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form action="{{ route('support.reply') }}" method="POST">
                @csrf
                <input type="hidden" name="support_request_id" id="reply-request-id">
                <div class="modal-body">
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="fw-bold text-dark mb-1">Người gửi: <span id="reply-sender-name"
                                class="text-primary"></span></div>
                        <div class="text-secondary small" id="reply-sender-content"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nội dung phản hồi gửi lại cho người dân <span
                                class="text-danger">*</span></label>
                        <textarea name="content" id="reply-text-area" class="form-control" rows="4"
                            placeholder="Nhập nội dung giải đáp, hướng dẫn chi tiết..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill"></i> Gửi Phản Hồi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-delete-support">
        <div class="modal-dialog" style="max-width: 420px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Đơn Hỗ Trợ</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-delete-support" action="" method="POST">
                @csrf
                <div class="modal-body text-center py-4">
                    <p>Bạn có chắc muốn xóa đơn yêu cầu hỗ trợ của: <br><strong id="delete-sender-name"
                            class="text-danger"></strong>?</p>
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
        function openReplyModal(id, name, content, existingReply) {
            document.getElementById('reply-request-id').value = id;
            document.getElementById('reply-sender-name').textContent = name;
            document.getElementById('reply-sender-content').textContent = content;
            document.getElementById('reply-text-area').value = existingReply || '';
            openModal('modal-reply-support');
        }

        function openDeleteSupportModal(id, name) {
            document.getElementById('form-delete-support').action = window.location.origin + '/support/delete/' + id;
            document.getElementById('delete-sender-name').textContent = name;
            openModal('modal-delete-support');
        }
    </script>
@endpush
