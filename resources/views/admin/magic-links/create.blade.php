@extends('layouts.app')

@section('title', 'Tạo Magic Link Mới')

@section('content')
    <x-page-header title="Tạo Magic Link Mới">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ route('admin.magic-links.index') }}">Magic Link</a>
            <span>/</span>
            <span class="text-primary fw-bold">Tạo mới</span>
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.magic-links.store') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Chọn người dùng <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Chọn người dùng --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->username }}) - {{ $user->role->slug ?? '' }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Người dùng sẽ được tự động đăng nhập khi nhấn vào magic link.</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên / Mô tả link</label>
                            <input type="text" name="name" class="form-control" placeholder="Ví dụ: Link cho ông Nguyễn Văn A" value="{{ old('name') }}">
                            <div class="form-text">Tên giúp bạn dễ quản lý, không hiển thị trên link.</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Hết hạn vào</label>
                            <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                            <div class="form-text">Để trống nếu muốn magic link <strong>hoạt động mãi</strong> cho đến khi admin tắt.</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Số lần sử dụng tối đa</label>
                            <input type="number" name="max_uses" class="form-control" min="1" placeholder="Ví dụ: 1" value="{{ old('max_uses') }}">
                            <div class="form-text">Để trống nếu muốn dùng không giới hạn số lần cho đến khi admin tắt.</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-link-45deg"></i> Tạo Magic Link
                            </button>
                            <a href="{{ route('admin.magic-links.index') }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection