@extends('layouts.app')

@section('title', 'Thông Tin Cá Nhân')

@section('content')
    <x-page-header title="Hồ Sơ Cá Nhân">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Tài khoản</span>
            <span>/</span>
            <span class="text-primary fw-bold">Thông tin cá nhân</span>
        </x-slot:breadcrumbs>
    </x-page-header>



    <div class="row g-4">
        <div class="col-lg-4 col-md-5">
            <div class="card text-center">
                <div class="card-body py-4">
                    <form action="{{ url('/account/profile/avatar') }}" method="POST" enctype="multipart/form-data"
                        id="form-avatar">
                        @csrf
                        <div class="avatar-preview-wrapper">
                            @if ($user && $user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="avatar-preview-img"
                                    id="avatar-preview-target">
                            @else
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=240&h=240"
                                    alt="Avatar" class="avatar-preview-img" id="avatar-preview-target">
                            @endif
                            <label for="avatar-file-input" class="avatar-upload-label" title="Chọn ảnh đại diện mới">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                            <input type="file" name="avatar" id="avatar-file-input" class="d-none" accept="image/*"
                                onchange="document.getElementById('form-avatar').submit()">
                        </div>
                    </form>

                    <h4 class="fw-bold mb-1" style="font-size: 18px;">{{ $user->name ?? 'Người Dùng' }}</h4>
                    <div class="mb-3">
                        <span class="badge-status badge-role">
                            <i class="bi bi-shield-check"></i> {{ $user->role->name ?? 'Người dùng' }}
                        </span>
                        <span class="badge-status badge-active ms-1">
                            <i class="bi bi-check-circle-fill"></i>
                            {{ $user->status === 'active' ? 'Hoạt động' : 'Đã khóa' }}
                        </span>
                    </div>

                    <div class="text-muted text-start pt-3 border-top"
                        style="border-color: var(--border-color); font-size: 14px;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-at text-primary fs-5"></i>
                            <span>Tên đăng nhập: <strong>{{ $user->username ?? '' }}</strong></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-telephone text-primary fs-5"></i>
                            <span>Số điện thoại: <strong>{{ $user->phone ?? '' }}</strong></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-envelope text-primary fs-5"></i>
                            <span>Email: <strong>{{ $user->email ?? 'Chưa cập nhật' }}</strong></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-event text-primary fs-5"></i>
                            <span>Ngày tham gia:
                                <strong>{{ $user->created_at ? $user->created_at->format('d/m/Y') : '10/08/2026' }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="bi bi-sliders text-primary"></i> Cấu Hình Ứng Dụng</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-semibold" style="font-size: 14px;">Giao diện Sáng / Tối</div>
                            <div class="text-muted" style="font-size: 12px;">Bật/tắt chế độ tối (Dark mode)</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input switch-app-setting" type="checkbox"
                                {{ ($settings && $settings->theme === 'dark') ? 'checked' : '' }}
                                id="switch-theme-dark" style="cursor: pointer; width: 40px; height: 20px;">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-semibold" style="font-size: 14px;">Cảnh báo sâu bệnh</div>
                            <div class="text-muted" style="font-size: 12px;">Nhận tin cảnh báo sâu bệnh tự động</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input switch-app-setting" type="checkbox"
                                {{ ($settings && $settings->disease_alert_enabled) ? 'checked' : '' }}
                                id="switch-disease-alert" style="cursor: pointer; width: 40px; height: 20px;">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-semibold" style="font-size: 14px;">Cảnh báo thời tiết</div>
                            <div class="text-muted" style="font-size: 12px;">Thông báo mưa bão, sương mai</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input switch-app-setting" type="checkbox"
                                {{ ($settings && $settings->weather_alert_enabled) ? 'checked' : '' }}
                                id="switch-weather-alert" style="cursor: pointer; width: 40px; height: 20px;">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold" style="font-size: 14px;">Ngôn ngữ</div>
                            <div class="text-muted" style="font-size: 12px;">Ngôn ngữ hiển thị</div>
                        </div>
                        <span class="badge bg-light text-dark border px-2 py-1">Tiếng Việt</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="bi bi-pencil-square text-primary"></i> Chỉnh Sửa Thông Tin Cá Nhân</h5>
                </div>
                <div class="card-body">
                    <form action="{{ url('/account/profile') }}" method="POST" id="form-profile-update">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="profile-name" class="form-label">Họ và tên <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" id="profile-name" class="form-control"
                                    value="{{ old('name', $user->name ?? '') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="profile-username" class="form-label">Tên đăng nhập</label>
                                <input type="text" id="profile-username" class="form-control bg-light"
                                    value="{{ $user->username ?? '' }}" readonly
                                    title="Tên đăng nhập không thể thay đổi">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="profile-phone" class="form-label">Số điện thoại <span
                                        class="text-danger">*</span></label>
                                <input type="tel" name="phone" id="profile-phone" class="form-control"
                                    value="{{ old('phone', $user->phone ?? '') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="profile-email" class="form-label">Địa chỉ email</label>
                                <input type="email" name="email" id="profile-email" class="form-control"
                                    value="{{ old('email', $user->email ?? '') }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top"
                            style="border-color: var(--border-color);">
                            <button type="reset" class="btn btn-secondary">
                                <i class="bi bi-arrow-counterclockwise"></i> Khôi phục ban đầu
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle"></i> Lưu Thay Đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="bi bi-shield-lock text-primary"></i> Đổi Mật Khẩu Đăng Nhập</h5>
                </div>
                <div class="card-body">
                    <form action="{{ url('/account/profile/password') }}" method="POST" id="form-password-update">
                        @csrf

                        <div class="mb-3">
                            <label for="current-password" class="form-label">Mật khẩu hiện tại <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="current_password" id="current-password" class="form-control"
                                placeholder="Nhập mật khẩu đang dùng" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="new-password" class="form-label">Mật khẩu mới <span
                                        class="text-danger">*</span></label>
                                <input type="password" name="new_password" id="new-password" class="form-control"
                                    placeholder="Tối thiểu 6 ký tự" required>
                            </div>

                            <div class="col-md-6">
                                <label for="new-password-confirmation" class="form-label">Xác nhận mật khẩu mới <span
                                        class="text-danger">*</span></label>
                                <input type="password" name="new_password_confirmation" id="new-password-confirmation"
                                    class="form-control" placeholder="Nhập lại mật khẩu mới" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 pt-3 border-top"
                            style="border-color: var(--border-color);">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-key-fill"></i> Cập Nhật Mật Khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.switch-app-setting').forEach(sw => {
                sw.addEventListener('change', function() {
                    const diseaseAlert = document.getElementById('switch-disease-alert').checked ? 1 : 0;
                    const weatherAlert = document.getElementById('switch-weather-alert').checked ? 1 : 0;
                    const isDark = document.getElementById('switch-theme-dark')?.checked;
                    const theme = isDark ? 'dark' : 'light';

                    if (isDark !== undefined) {
                        document.documentElement.setAttribute('data-theme', theme);
                    }

                    fetch('{{ url('/account/profile/settings') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            disease_alert_enabled: diseaseAlert,
                            weather_alert_enabled: weatherAlert,
                            theme: theme
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message || 'Đã lưu cấu hình ứng dụng thành công!', 'success');
                        }
                    })
                    .catch(err => {
                        showToast('Có lỗi xảy ra khi lưu cấu hình.', 'danger');
                    });
                });
            });
        });
    </script>
@endpush
