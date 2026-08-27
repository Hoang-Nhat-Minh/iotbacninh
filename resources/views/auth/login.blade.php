@extends('layouts.auth')

@section('title', 'Đăng Nhập')
@section('auth_title', 'Hệ Thống IoT Bắc Ninh')
@section('auth_subtitle', 'Đăng nhập vào cổng quản lý nông nghiệp và cảnh báo sâu bệnh')

@section('content')
    <form action="{{ url('/login') }}" method="POST" id="form-login">
        @csrf

        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2 px-3"
                style="font-size: 14px; border-radius: var(--radius-md);">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <div class="mb-3">
            <label for="username" class="form-label">Tên đăng nhập hoặc Số điện thoại <span
                    class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0" style="border-color: var(--border-color);"><i
                        class="bi bi-person text-muted"></i></span>
                <input type="text" name="username" id="username" class="form-control border-start-0 ps-0"
                    placeholder="Ví dụ: 0987654321 hoặc admin" value="admin" required autofocus
                    value="{{ old('username') }}">
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label mb-0">Mật khẩu <span class="text-danger">*</span></label>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0" style="border-color: var(--border-color);"><i
                        class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" id="password" class="form-control border-start-0 border-end-0 ps-0"
                    placeholder="Nhập mật khẩu" value="123456" required>
                <button class="btn btn-secondary border-start-0" type="button" id="btn-toggle-password"
                    style="border-color: var(--border-color);" title="Hiện / Ẩn mật khẩu">
                    <i class="bi bi-eye text-muted" id="icon-toggle-password"></i>
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label text-secondary" for="remember" style="font-size: 14px;">Ghi nhớ tài
                    khoản</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 mb-3" style="font-size: 16px;">
            <i class="bi bi-box-arrow-in-right"></i> Đăng Nhập
        </button>
    </form>

    <div class="text-center pt-3 border-top" style="border-color: var(--border-color) !important;">
        <div class="mb-2">
            <span class="text-muted" style="font-size: 14px;">Chưa có tài khoản vườn trồng? </span>
            <a href="{{ url('/register') }}" class="fw-bold" style="font-size: 14px;">Đăng ký ngay</a>
        </div>
        <div>
            <a href="{{ route('labeler.login') }}" class="text-secondary small fw-bold text-decoration-none"
                style="font-size: 13.5px;">
                <i class="bi bi-tags-fill me-1 text-primary"></i> Đăng nhập với tư cách Data Labeler
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('btn-toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('icon-toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
@endpush
