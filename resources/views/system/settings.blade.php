@extends('layouts.app')

@section('title', 'Cài Đặt Thông Tin Hệ Thống')

@section('content')
<x-page-header title="Cài Đặt Thông Tin Hệ Thống">
    <x-slot:breadcrumbs>
        <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
        <span>/</span>
        <span>Hệ thống</span>
        <span>/</span>
        <span class="text-primary fw-bold">Cài đặt chung</span>
    </x-slot:breadcrumbs>
</x-page-header>



<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-gear-wide-connected text-primary"></i> Thông Tin Chung Hệ Thống</h5>
            </div>
            <div class="card-body">
                <form action="{{ url('/system/settings/update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Tên hệ thống hiển thị <span class="text-danger">*</span></label>
                        <input type="text" name="system_name" class="form-control" value="{{ $settings['system_name'] ?? 'Hệ Thống IoT Nông Nghiệp & Cảnh Báo Sâu Bệnh Tỉnh Bắc Ninh' }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Đơn vị chủ quản</label>
                            <input type="text" name="organization" class="form-control" value="{{ $settings['organization'] ?? 'Sở Khoa Học và Công Nghệ Tỉnh Bắc Ninh' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tổng đài hỗ trợ kỹ thuật</label>
                            <input type="text" name="hotline" class="form-control" value="{{ $settings['hotline'] ?? '1800 6888' }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email quản trị viên</label>
                        <input type="email" name="admin_email" class="form-control" value="{{ $settings['admin_email'] ?? 'khcn@bacninh.gov.vn' }}">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Bản quyền Footer</label>
                        <input type="text" name="copyright" class="form-control" value="{{ $settings['copyright'] ?? '© 2026 Hệ Thống IoT Nông Nghiệp Bắc Ninh. Bảo lưu mọi quyền.' }}">
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top" style="border-color: var(--border-color);">
                        <button type="reset" class="btn btn-secondary">Khôi phục</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle"></i> Lưu Cài Đặt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-image text-primary"></i> Biểu Tượng & Favicon</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="p-3 border rounded mb-2 d-inline-block bg-light">
                        <div class="brand-icon mx-auto" style="width: 64px; height: 64px; font-size: 32px;">
                            <i class="bi bi-flower1"></i>
                        </div>
                    </div>
                    <div class="text-muted small">Biểu tượng Favicon & Logo hiện tại</div>
                </div>

                <div class="mb-3 text-start">
                    <label class="form-label" style="font-size: 13px;">Tải lên Favicon mới (.ico, .png)</label>
                    <input type="file" name="favicon" class="form-control form-control-sm" accept=".ico,.png">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
