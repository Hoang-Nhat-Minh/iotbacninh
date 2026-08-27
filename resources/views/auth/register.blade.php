@extends('layouts.auth')

@section('title', 'Đăng Ký Tài Khoản')
@section('auth_title', 'Tạo Tài Khoản Mới')
@section('auth_subtitle', 'Đăng ký tài khoản cho chủ vườn để quản lý giám sát nông nghiệp')

@section('content')
<form action="{{ url('/register') }}" method="POST" id="form-register">
    @csrf

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2 px-3" style="font-size: 14px; border-radius: var(--radius-md);">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">Họ và tên chủ vườn <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0" style="border-color: var(--border-color);"><i class="bi bi-person text-muted"></i></span>
            <input type="text" name="name" id="name" class="form-control border-start-0 ps-0" placeholder="Ví dụ: Nguyễn Văn Bình" required value="{{ old('name') }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0" style="border-color: var(--border-color);"><i class="bi bi-telephone text-muted"></i></span>
                <input type="tel" name="phone" id="phone" class="form-control border-start-0 ps-0" placeholder="0912345678" required value="{{ old('phone') }}">
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0" style="border-color: var(--border-color);"><i class="bi bi-at text-muted"></i></span>
                <input type="text" name="username" id="username" class="form-control border-start-0 ps-0" placeholder="nguyenbinh" required value="{{ old('username') }}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0" style="border-color: var(--border-color);"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" placeholder="Ít nhất 6 ký tự" required>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0" style="border-color: var(--border-color);"><i class="bi bi-shield-check text-muted"></i></span>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control border-start-0 ps-0" placeholder="Nhập lại mật khẩu" required>
            </div>
        </div>
    </div>

    <div class="p-3 mb-4 rounded" style="background-color: var(--bg-body); border: 1px dashed var(--border-color);">
        <h6 class="fw-bold mb-2 text-primary" style="font-size: 14px;"><i class="bi bi-geo-alt"></i> Thông tin vườn trồng ban đầu</h6>
        
        <div class="mb-2">
            <label for="garden_name" class="form-label" style="font-size: 13px;">Tên vườn / Vùng trồng</label>
            <input type="text" name="garden_name" id="garden_name" class="form-control form-control-sm" placeholder="Ví dụ: Vườn bưởi Thuận Thành">
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <label for="garden_location" class="form-label" style="font-size: 13px;">Địa chỉ / Huyện xã</label>
                <input type="text" name="garden_location" id="garden_location" class="form-control form-control-sm" placeholder="Ví dụ: Gia Bình, Bắc Ninh">
            </div>
            <div class="col-md-6">
                <label for="crop_type" class="form-label" style="font-size: 13px;">Loại cây trồng chính</label>
                <input type="text" name="crop_type" id="crop_type" class="form-control form-control-sm" placeholder="Ví dụ: Cà chua, Bưởi, Lúa...">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 mb-3" style="font-size: 16px;">
        <i class="bi bi-person-plus-fill"></i> Hoàn Tất Đăng Ký
    </button>
</form>

<div class="text-center pt-3 border-top" style="border-color: var(--border-color) !important;">
    <span class="text-muted" style="font-size: 14px;">Đã có tài khoản? </span>
    <a href="{{ url('/login') }}" class="fw-bold" style="font-size: 14px;">Đăng nhập tại đây</a>
</div>
@endsection
