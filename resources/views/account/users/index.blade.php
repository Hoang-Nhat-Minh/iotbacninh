@extends('layouts.app')

@section('title', 'Quản Lý Tài Khoản')

@section('content')
    <x-page-header title="Quản Lý Tài Khoản">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Tài khoản</span>
            <span>/</span>
            <span class="text-primary fw-bold">Danh sách người dùng</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <button type="button" class="btn btn-primary" onclick="openModal('modal-create-user')">
                <i class="bi bi-person-plus-fill"></i> Thêm Tài Khoản
            </button>
        </x-slot:actions>
    </x-page-header>



    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('account.users') }}" class="row g-3 align-items-center mb-4">
                <div class="col-lg-5 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Họ tên, SĐT hoặc Tên đăng nhập..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3">
                    <select name="role_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả vai trò --</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Đã khóa</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    @if (request('search') || request('role_id') || request('status'))
                        <a href="{{ route('account.users') }}" class="btn btn-secondary" title="Đặt lại bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Họ và Tên</th>
                            <th>Tên đăng nhập</th>
                            <th>Số điện thoại</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th style="width: 140px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $u)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $users->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $u->name }}</div>
                                            <div class="text-muted small">{{ $u->email ?? 'Chưa có email' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-dark fw-medium">{{ $u->username }}</td>
                                <td class="text-secondary">{{ $u->phone }}</td>
                                <td>
                                    @if ($u->role->slug === 'admin')
                                        <span class="badge-status badge-role"><i class="bi bi-shield-lock-fill"></i> Quản
                                            trị viên</span>
                                    @elseif($u->role->slug === 'manager')
                                        <span class="badge-status badge-role"><i class="bi bi-person-workspace"></i> Nhà
                                            quản lý</span>
                                    @else
                                        <span class="badge-status badge-role"><i class="bi bi-person-fill"></i> Người
                                            dùng</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($u->status === 'active')
                                        <span class="badge-status badge-active">
                                            <i class="bi bi-check-circle-fill"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge-status badge-locked">
                                            <i class="bi bi-lock-fill"></i> Đã khóa
                                        </span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $u->created_at ? $u->created_at->format('d/m/Y') : '10/08/2026' }}</td>
                                <td style="text-align: center;">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-secondary btn-icon btn-sm btn-edit-user"
                                            title="Sửa tài khoản" data-id="{{ $u->id }}"
                                            data-name="{{ $u->name }}" data-phone="{{ $u->phone }}"
                                            data-username="{{ $u->username }}" data-email="{{ $u->email }}"
                                            data-role="{{ $u->role_id }}" data-status="{{ $u->status }}">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </button>

                                        <button type="button" class="btn btn-secondary btn-icon btn-sm btn-toggle-user"
                                            title="{{ $u->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}"
                                            data-id="{{ $u->id }}" data-name="{{ $u->name }}"
                                            data-status="{{ $u->status }}">
                                            <i
                                                class="bi {{ $u->status === 'active' ? 'bi-lock-fill text-warning' : 'bi-unlock-fill text-success' }}"></i>
                                        </button>

                                        @if (Auth::id() !== $u->id)
                                            <button type="button" class="btn btn-secondary btn-icon btn-sm btn-delete-user"
                                                title="Xóa tài khoản" data-id="{{ $u->id }}"
                                                data-name="{{ $u->name }}">
                                                <i class="bi bi-trash3-fill text-danger"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Không tìm thấy tài khoản nào</h6>
                                        <p class="text-muted small mb-0">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-3"
                style="border-color: var(--border-color) !important;">
                <span class="text-muted" style="font-size: 14px;">
                    Hiển thị {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} trên tổng số
                    {{ $users->total() }} tài khoản
                </span>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Modal Thêm Tài Khoản Mới -->
    <div class="app-modal" id="modal-create-user">
        <div class="modal-dialog">
            <form action="{{ route('account.users.store') }}" method="POST" id="form-create-user">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus text-primary"></i> Thêm Tài Khoản Mới</h5>
                    <button type="button" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="Ví dụ: Nguyễn Văn An" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Số điện thoại <span
                                    class="text-danger">*</span></label>
                            <input type="tel" name="phone" id="phone" class="form-control"
                                placeholder="0987654321" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Tên đăng nhập <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="username" id="username" class="form-control"
                                placeholder="an_nguyen" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email liên hệ</label>
                            <input type="email" name="email" id="email" class="form-control"
                                placeholder="an@bacninh.gov.vn">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Mật khẩu khởi tạo <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Tối thiểu 6 ký tự" required>
                        </div>
                        <div class="col-md-6">
                            <label for="role_id" class="form-label">Vai trò phân quyền <span
                                    class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-select" required>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->id }}" {{ $r->slug === 'user' ? 'selected' : '' }}>
                                        {{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="status" class="form-label">Trạng thái tài khoản</label>
                        <select name="status" id="status" class="form-select">
                            <option value="active" selected>Hoạt động ngay</option>
                            <option value="locked">Tạm khóa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Lưu Tài Khoản
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Modal Sửa Tài Khoản -->
    <div class="app-modal" id="modal-edit-user">
        <div class="modal-dialog">
            <form action="" method="POST" id="form-edit-user">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square text-primary"></i> Sửa Thông Tin Tài Khoản</h5>
                    <button type="button" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">Họ và tên <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_phone" class="form-label">Số điện thoại <span
                                    class="text-danger">*</span></label>
                            <input type="tel" name="phone" id="edit_phone" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="edit_username" class="form-label">Tên đăng nhập</label>
                            <input type="text" id="edit_username" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email liên hệ</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="edit_role_id" class="form-label">Vai trò phân quyền <span
                                    class="text-danger">*</span></label>
                            <select name="role_id" id="edit_role_id" class="form-select" required>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Trạng thái</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active">Hoạt động</option>
                                <option value="locked">Tạm khóa</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="edit_password" class="form-label">Mật khẩu mới (Bỏ trống nếu giữ nguyên)</label>
                        <input type="password" name="password" id="edit_password" class="form-control"
                            placeholder="Nhập mật khẩu mới nếu muốn thay đổi">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Modal Khóa / Mở Khóa Tài Khoản -->
    <div class="app-modal" id="modal-toggle-status">
        <div class="modal-dialog" style="max-width: 440px;">
            <form action="" method="POST" id="form-toggle-status">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="toggle-status-title"><i
                            class="bi bi-shield-exclamation text-warning"></i>
                        Xác Nhận Khóa Tài Khoản</h5>
                    <button type="button" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3" id="toggle-icon-container">
                        <i class="bi bi-lock-fill text-warning" style="font-size: 48px;"></i>
                    </div>
                    <p class="mb-1" id="toggle-status-message">Bạn có chắc chắn muốn khóa tài khoản này không?</p>
                    <h6 class="fw-bold text-dark mt-2" id="toggle-user-name">Nguyễn Văn An</h6>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary" id="toggle-confirm-btn">Xác Nhận</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4. Modal Xóa Tài Khoản -->
    <div class="app-modal" id="modal-delete-user">
        <div class="modal-dialog" style="max-width: 440px;">
            <form action="" method="POST" id="form-delete-user">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-trash3 text-danger"></i> Xác Nhận Xóa Tài Khoản</h5>
                    <button type="button" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle" style="font-size: 48px;"></i>
                    </div>
                    <p class="mb-1">Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản:</p>
                    <h6 class="fw-bold text-danger mt-2" id="delete-user-name">Nguyễn Văn An</h6>
                    <small class="text-muted mt-2 d-block">Hành động này không thể hoàn tác.</small>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger">Xác Nhận Xóa</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sửa tài khoản
            document.querySelectorAll('.btn-edit-user').forEach(btn => {
                btn.addEventListener('click', function() {
                    const d = this.dataset;
                    document.getElementById('form-edit-user').action = window.location.origin +
                        '/account/users/' + d.id;
                    document.getElementById('edit_name').value = d.name || '';
                    document.getElementById('edit_phone').value = d.phone || '';
                    document.getElementById('edit_username').value = d.username || '';
                    document.getElementById('edit_email').value = d.email || '';
                    document.getElementById('edit_role_id').value = d.role || '';
                    document.getElementById('edit_status').value = d.status || 'active';
                    document.getElementById('edit_password').value = '';
                    openModal('modal-edit-user');
                });
            });

            // Khóa / Mở khóa tài khoản
            document.querySelectorAll('.btn-toggle-user').forEach(btn => {
                btn.addEventListener('click', function() {
                    const d = this.dataset;
                    document.getElementById('form-toggle-status').action = window.location.origin +
                        '/account/users/' + d.id + '/toggle-status';
                    document.getElementById('toggle-user-name').textContent = d.name || '';

                    const title = document.getElementById('toggle-status-title');
                    const msg = document.getElementById('toggle-status-message');
                    const iconBox = document.getElementById('toggle-icon-container');
                    const confirmBtn = document.getElementById('toggle-confirm-btn');

                    if (d.status === 'active') {
                        title.innerHTML =
                            '<i class="bi bi-lock-fill text-warning"></i> Khóa Tài Khoản';
                        msg.textContent =
                            'Người dùng sẽ không thể đăng nhập vào hệ thống khi tài khoản bị khóa.';
                        iconBox.innerHTML =
                            '<i class="bi bi-lock-fill text-warning" style="font-size: 48px;"></i>';
                        confirmBtn.textContent = 'Khóa Tài Khoản';
                        confirmBtn.className = 'btn btn-warning';
                    } else {
                        title.innerHTML =
                            '<i class="bi bi-unlock-fill text-success"></i> Mở Khóa Tài Khoản';
                        msg.textContent = 'Kích hoạt lại quyền truy cập cho người dùng này.';
                        iconBox.innerHTML =
                            '<i class="bi bi-unlock-fill text-success" style="font-size: 48px;"></i>';
                        confirmBtn.textContent = 'Mở Khóa Tài Khoản';
                        confirmBtn.className = 'btn btn-success';
                    }
                    openModal('modal-toggle-status');
                });
            });

            // Xóa tài khoản
            document.querySelectorAll('.btn-delete-user').forEach(btn => {
                btn.addEventListener('click', function() {
                    const d = this.dataset;
                    document.getElementById('form-delete-user').action = window.location.origin +
                        '/account/users/' + d.id;
                    document.getElementById('delete-user-name').textContent = d.name || '';
                    openModal('modal-delete-user');
                });
            });
        });
    </script>
@endpush
