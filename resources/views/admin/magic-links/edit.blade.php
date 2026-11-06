@extends('layouts.app')

@section('title', 'Chỉnh Sửa Magic Link')

@section('content')
    <x-page-header title="Chỉnh Sửa Magic Link">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ route('admin.magic-links.index') }}">Magic Link</a>
            <span>/</span>
            <span class="text-primary fw-bold">Chỉnh sửa</span>
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.magic-links.update', $magicLink->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Chọn người dùng <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Chọn người dùng --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', $magicLink->user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->username }}) - {{ $user->role->slug ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tên / Mô tả link</label>
                            <input type="text" name="name" class="form-control" placeholder="Ví dụ: Link cho ông Nguyễn Văn A" value="{{ old('name', $magicLink->name) }}">
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Hết hạn vào</label>
                            <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at', $magicLink->expires_at ? \Carbon\Carbon::parse($magicLink->expires_at)->format('Y-m-d\H:i') : '') }}">
                            <div class="form-text">Để trống nếu muốn magic link hoạt động mãi.</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Số lần sử dụng tối đa</label>
                            <input type="number" name="max_uses" class="form-control" min="1" placeholder="Ví dụ: 1" value="{{ old('max_uses', $magicLink->max_uses) }}">
                            <div class="form-text">Để trống nếu muốn dùng không giới hạn số lần.</div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ old('is_active', $magicLink->is_active) ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ !old('is_active', $magicLink->is_active) ? 'selected' : '' }}>Vô hiệu hóa</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Lưu Thay Đổi
                            </button>
                            <a href="{{ route('admin.magic-links.index') }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection