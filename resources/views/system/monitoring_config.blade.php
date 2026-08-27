@extends('layouts.app')

@section('title', 'Cấu Hình Hệ Thống Quan Trắc IoT')

@section('content')
<x-page-header title="Cấu Hình Hệ Thống Quan Trắc">
    <x-slot:breadcrumbs>
        <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
        <span>/</span>
        <span>Hệ thống</span>
        <span>/</span>
        <span class="text-primary fw-bold">Cấu hình quan trắc</span>
    </x-slot:breadcrumbs>
</x-page-header>



<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-broadcast-pin text-primary"></i> Trạng Thái Vận Hành & Chu Kỳ Gửi</h5>
            </div>
            <div class="card-body">
                <form action="{{ url('/system/monitoring-config/update') }}" method="POST">
                    @csrf
                    <div class="p-3 mb-4 rounded border" style="background-color: var(--bg-body);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">Chế độ hoạt động trạm IoT</h6>
                                <p class="text-muted small mb-0">Khi tắt, toàn bộ hệ thống trạm sẽ chuyển sang trạng thái bảo trì và tạm dừng thu thập dữ liệu.</p>
                            </div>
                            <div class="form-check form-switch ms-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="switch-monitoring-status" {{ $isSystemActive ? 'checked' : '' }} value="1" style="width: 48px; height: 24px; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Khoảng thời gian trạm IoT gửi dữ liệu về Server <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <input type="number" name="data_send_interval" class="form-control" value="{{ $dataInterval }}" min="10" max="3600" required>
                            <span class="input-group-text">Giây</span>
                        </div>
                        <small class="text-muted">Khuyến nghị: 60 giây đối với dữ liệu vi khí hậu thông thường; 300 giây khi thời tiết ổn định.</small>
                    </div>

                    <div class="d-flex justify-content-end pt-3 border-top" style="border-color: var(--border-color);">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle"></i> Cập Nhật Cấu Hình
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-cpu text-primary"></i> Trạng Thái Các Trạm Quan Trắc</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($stations as $st)
                        <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $st->name }} ({{ $st->code }})</div>
                                <div class="text-muted small">Vùng: {{ $st->garden->name ?? 'Bắc Ninh' }} | Chu kỳ: {{ $st->data_interval ?? 60 }}s</div>
                            </div>
                            <span class="badge-status {{ $st->status === 'active' ? 'badge-active' : 'badge-locked' }}">
                                <i class="bi bi-circle-fill" style="font-size: 8px;"></i> {{ $st->status === 'active' ? 'Hoạt động' : 'Bảo trì' }}
                            </span>
                        </div>
                    @empty
                        <div class="p-3 text-muted text-center">Chưa có trạm quan trắc nào.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
