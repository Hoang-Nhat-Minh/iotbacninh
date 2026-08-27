@extends('layouts.app')

@section('title', 'Quản Lý Thông Báo Hệ Thống')

@section('content')
    <x-page-header title="Hộp Thư Thông Báo & Cảnh Báo">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Thông báo</span>
            <span>/</span>
            <span class="text-primary fw-bold">Danh sách thông báo</span>
        </x-slot:breadcrumbs>

        @if (Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isManager()))
            <x-slot:actions>
                <button type="button" class="btn btn-primary" onclick="openModal('modal-add-notification')">
                    <i class="bi bi-send-plus-fill"></i> Tạo Thông Báo
                </button>
            </x-slot:actions>
        @endif
    </x-page-header>



    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('notifications.index') }}" class="row g-3 align-items-center mb-4">
                <div class="col-lg-5 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Tiêu đề, nội dung cảnh báo..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3">
                    <select name="priority" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả mức độ --</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Cao (Khẩn cấp)</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Trung bình</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Thấp</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}"
                        onchange="this.form.submit()">
                </div>

                <div class="col-lg-2 col-md-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Lọc</button>
                    @if (request('search') || request('priority') || request('date'))
                        <a href="{{ route('notifications.index') }}" class="btn btn-secondary" title="Đặt lại bộ lọc"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">STT</th>
                            <th>Tiêu đề thông báo</th>
                            <th>Loại thông báo</th>
                            <th>Mức ưu tiên</th>
                            <th>Người nhận</th>
                            <th>Trạng thái</th>
                            <th>Thời gian gửi</th>
                            <th style="width: 130px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $idx => $n)
                            <tr class="{{ !$n->read_at ? 'table-warning-subtle fw-medium' : '' }}">
                                <td class="text-secondary fw-semibold">{{ $notifications->firstItem() + $idx }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $n->title }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 340px;">
                                        {{ $n->content }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        {{ $n->type === 'pest_alert' ? 'Cảnh báo sâu bệnh' : ($n->type === 'weather_alert' ? 'Cảnh báo thời tiết' : 'Hệ thống') }}
                                    </span>
                                </td>
                                <td>
                                    @if ($n->priority === 'high')
                                        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i>
                                            Cao</span>
                                    @elseif($n->priority === 'medium')
                                        <span class="badge bg-warning text-dark"><i
                                                class="bi bi-exclamation-circle-fill"></i> Trung bình</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bi bi-info-circle-fill"></i> Thấp</span>
                                    @endif
                                </td>
                                <td class="text-dark fw-medium">
                                    @if ($n->is_all || !$n->user_id)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="bi bi-broadcast"></i> Tất cả người dùng
                                        </span>
                                    @else
                                        <span class="text-dark fw-medium">
                                            <i class="bi bi-person text-secondary me-1"></i> {{ $n->user->name ?? 'Người dùng ID #' . $n->user_id }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($n->read_at)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-check-all"></i> Đã đọc
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1">
                                            <i class="bi bi-envelope-fill text-warning me-1"></i> Chưa đọc
                                        </span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $n->created_at ? $n->created_at->format('d/m/Y H:i') : '' }}</td>
                                <td style="text-align: center;">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-secondary btn-icon btn-sm" title="Xem chi tiết thông báo"
                                            onclick="openDetailNotificationModal({{ json_encode([
                                                'id' => $n->id,
                                                'title' => $n->title,
                                                'content' => $n->content,
                                                'type' => $n->type === 'pest_alert' ? 'Cảnh báo sâu bệnh' : ($n->type === 'weather_alert' ? 'Cảnh báo thời tiết' : 'Hệ thống'),
                                                'priority' => $n->priority,
                                                'recipient' => ($n->is_all || !$n->user_id) ? 'Tất cả người dùng' : ($n->user->name ?? ('ID #' . $n->user_id)),
                                                'created_at' => $n->created_at ? $n->created_at->format('d/m/Y H:i') : '',
                                                'is_read' => !is_null($n->read_at)
                                            ]) }})">
                                            <i class="bi bi-eye text-primary"></i>
                                        </button>

                                        @if (!$n->read_at)
                                            <form action="{{ route('notifications.read', $n->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Đánh dấu là đã đọc">
                                                    <i class="bi bi-check2-circle text-success"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn btn-secondary btn-icon btn-sm" title="Xóa thông báo"
                                            onclick="openDeleteNotificationModal({{ $n->id }}, '{{ addslashes($n->title) }}')">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-bell-slash"></i></div>
                                        <h6 class="fw-bold mb-1">Chưa có thông báo nào</h6>
                                        <p class="text-muted small mb-0">Nhấn nút phát thông báo để gửi tin tới người dùng
                                        </p>
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
                    Hiển thị {{ $notifications->firstItem() ?? 0 }} - {{ $notifications->lastItem() ?? 0 }} trên tổng số
                    {{ $notifications->total() }} thông báo
                </span>
                <div>
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="app-modal" id="modal-add-notification">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-send text-primary"></i> Gửi Thông Báo Mới</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form action="{{ route('notifications.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="form-check form-switch mb-3 p-2 rounded border bg-light d-flex align-items-center gap-2">
                            <input class="form-check-input ms-1 me-2" type="checkbox" name="is_all" id="check-is-all" value="1"
                                {{ old('is_all', $errors->any() ? old('is_all') : '1') == '1' ? 'checked' : '' }} onchange="toggleRecipientSelection()">
                            <label class="form-check-label fw-bold text-primary mb-0" for="check-is-all" style="cursor: pointer;">
                                <i class="bi bi-broadcast"></i> Gửi cho tất cả người dùng hệ thống
                            </label>
                        </div>
                        
                        <div id="wrapper-recipient-select">
                            <label class="form-label fw-semibold">Chọn người nhận (Cho phép chọn nhiều) <span class="text-danger">*</span></label>
                            <select name="user_ids[]" id="select-user-ids" class="form-select" multiple size="4">
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" {{ is_array(old('user_ids')) && in_array($u->id, old('user_ids')) ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->phone ?? $u->username }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted mt-1"><i class="bi bi-info-circle"></i> Giữ phím <strong>Ctrl</strong> (Windows) hoặc <strong>Cmd</strong> (Mac) để chọn/bỏ chọn nhiều người dùng.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tiêu đề thông báo <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control"
                            placeholder="Nhập tiêu đề ngắn gọn, rõ ràng" value="{{ old('title') }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Loại thông báo</label>
                            <select name="type" class="form-select">
                                <option value="pest_alert" {{ old('type') == 'pest_alert' ? 'selected' : '' }}>Cảnh báo sâu bệnh</option>
                                <option value="weather_alert" {{ old('type') == 'weather_alert' ? 'selected' : '' }}>Cảnh báo thời tiết</option>
                                <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>Thông báo chung</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mức độ ưu tiên</label>
                            <select name="priority" class="form-select">
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Cao (Khẩn cấp)</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Trung bình</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Thấp</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nội dung chi tiết <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="4" placeholder="Nội dung khuyến cáo, hướng dẫn xử lý..."
                            required>{{ old('content') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill"></i> Phát Thông
                        Báo</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-delete-notification">
        <div class="modal-dialog" style="max-width: 420px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Thông Báo</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-delete-notification" action="" method="POST">
                @csrf
                <div class="modal-body text-center py-4">
                    <p>Bạn có chắc muốn xóa thông báo: <br><strong id="delete-noti-title" class="text-danger"></strong>?
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Ngay</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Xem Chi Tiết Thông Báo -->
    <div class="app-modal" id="modal-detail-notification">
        <div class="modal-dialog" style="max-width: 580px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bell-fill text-primary"></i> Chi Tiết Thông Báo</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <div class="modal-body py-3">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div>
                        <span class="badge bg-light text-dark border px-2 py-1" id="detail-noti-type">Loại</span>
                        <span class="badge ms-1" id="detail-noti-priority">Ưu tiên</span>
                    </div>
                    <div id="detail-noti-status"></div>
                </div>

                <h5 class="fw-bold text-dark mb-2" id="detail-noti-title"></h5>
                <div class="text-muted small mb-3">
                    <i class="bi bi-clock me-1"></i> <span id="detail-noti-time"></span>
                    <span class="ms-3"><i class="bi bi-person me-1"></i> Gửi tới: <strong id="detail-noti-recipient"></strong></span>
                </div>

                <div class="p-3 rounded border bg-light text-dark" style="font-size: 14.5px; line-height: 1.6; white-space: pre-wrap;" id="detail-noti-content">
                </div>
            </div>
            <div class="modal-footer">
                <form id="form-mark-read-modal" action="" method="POST" class="m-0 d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle"></i> Đánh dấu đã đọc
                    </button>
                </form>
                <button type="button" class="btn btn-secondary btn-modal-close">Đóng</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openDetailNotificationModal(data) {
            document.getElementById('detail-noti-title').textContent = data.title;
            document.getElementById('detail-noti-type').textContent = data.type;
            document.getElementById('detail-noti-time').textContent = data.created_at;
            document.getElementById('detail-noti-recipient').textContent = data.recipient;
            document.getElementById('detail-noti-content').textContent = data.content;

            const priorityBadge = document.getElementById('detail-noti-priority');
            if (data.priority === 'high') {
                priorityBadge.className = 'badge bg-danger ms-1';
                priorityBadge.textContent = 'Cao (Khẩn cấp)';
            } else if (data.priority === 'medium') {
                priorityBadge.className = 'badge bg-warning text-dark ms-1';
                priorityBadge.textContent = 'Trung bình';
            } else {
                priorityBadge.className = 'badge bg-secondary ms-1';
                priorityBadge.textContent = 'Thấp';
            }

            const statusContainer = document.getElementById('detail-noti-status');
            const markReadForm = document.getElementById('form-mark-read-modal');

            if (data.is_read) {
                statusContainer.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-check-all"></i> Đã đọc</span>';
                markReadForm.style.display = 'none';
            } else {
                statusContainer.innerHTML = '<span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1"><i class="bi bi-envelope-fill text-warning me-1"></i> Chưa đọc</span>';
                markReadForm.style.display = 'inline-block';
                markReadForm.action = window.location.origin + '/notifications/read/' + data.id;
            }

            openModal('modal-detail-notification');
        }

        function openDeleteNotificationModal(id, title) {
            document.getElementById('form-delete-notification').action = window.location.origin + '/notifications/delete/' +
                id;
            document.getElementById('delete-noti-title').textContent = title;
            openModal('modal-delete-notification');
        }

        function toggleRecipientSelection() {
            const isAllCheck = document.getElementById('check-is-all');
            const recipientWrapper = document.getElementById('wrapper-recipient-select');
            const selectUserIds = document.getElementById('select-user-ids');

            if (isAllCheck && recipientWrapper) {
                if (isAllCheck.checked) {
                    recipientWrapper.style.display = 'none';
                    if (selectUserIds) selectUserIds.removeAttribute('required');
                } else {
                    recipientWrapper.style.display = 'block';
                    if (selectUserIds) selectUserIds.setAttribute('required', 'required');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            toggleRecipientSelection();
            @if ($errors->any())
                openModal('modal-add-notification');
            @endif
        });
    </script>
@endpush
