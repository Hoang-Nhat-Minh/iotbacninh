@extends('layouts.app')

@section('title', 'Dự Báo Sâu Đục Cuống Tự Động')

@section('content')
<x-page-header title="Dự Báo Sâu Đục Cuống Tự Động">
    <x-slot:breadcrumbs>
        <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span class="text-primary fw-bold">Dự báo sâu đục cuống</span>
    </x-slot:breadcrumbs>
</x-page-header>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-5">
                <label class="form-label" style="font-size: 13px;">Tìm kiếm vùng trồng</label>
                <div class="input-group">
                    <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" placeholder="Nhập mã vùng (VT-01) hoặc tên vườn...">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-size: 13px;">Lọc theo trạng thái nguy cơ</label>
                <select class="form-select">
                    <option value="">-- Tất cả mức độ --</option>
                    <option value="danger" selected>Nguy cơ cao (Sắp bùng phát trong 48h)</option>
                    <option value="warning">Đang tích lũy nhiệt độ</option>
                    <option value="safe">An toàn</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end" style="margin-top: 32px;">
                <button class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Lọc Dữ Liệu</button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="p-3 border rounded h-100" style="background-color: var(--bg-body);">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Vườn Bưởi Thuận Thành (VT-01)</h5>
                            <span class="text-muted small">Trạm quan trắc TT-01 &bull; Chủ vườn: Lê Hoàng Cường</span>
                        </div>
                        <span class="badge bg-danger"><i class="bi bi-exclamation-octagon-fill"></i> Báo động đỏ</span>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Giai đoạn: <strong>Sâu non đục quả (Tuổi 2-3)</strong></span>
                            <span>Tích lũy: <strong>485 / 500 GDD (97%)</strong></span>
                        </div>
                        <div class="progress mb-3" style="height: 12px; border-radius: 6px;">
                            <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" role="progressbar" style="width: 97%;"></div>
                        </div>
                    </div>

                    <div class="p-3 bg-white rounded border mb-3">
                        <div class="d-flex align-items-center gap-2 text-danger fw-bold mb-1">
                            <i class="bi bi-calendar-x"></i> Dự báo thời gian bùng phát đỉnh điểm: 16/08/2026
                        </div>
                        <div class="text-secondary small">
                            <strong>Khuyến nghị từ chuyên gia:</strong> Cần tiến hành bao trái hoặc phun trừ sâu sinh học BT vào chiều tối ngày 15/08 để ngăn ấu trùng chui sâu vào cuống quả.
                        </div>
                    </div>

                    <button class="btn btn-sm btn-primary w-100" onclick="showToast('Đã gửi thông báo cảnh báo tức thì tới ứng dụng của chủ vườn Lê Hoàng Cường!', 'success')">
                        <i class="bi bi-bell"></i> Bắn Lại Thông Báo Cảnh Báo Cho Chủ Vườn
                    </button>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="p-3 border rounded h-100" style="background-color: var(--bg-body);">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Vùng Cà Chua Gia Bình (VT-02)</h5>
                            <span class="text-muted small">Trạm quan trắc GB-02 &bull; Chủ vườn: Phạm Đức Dũng</span>
                        </div>
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Bình thường</span>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Giai đoạn: <strong>Trứng mới nở (Tuổi 1)</strong></span>
                            <span>Tích lũy: <strong>140 / 500 GDD (28%)</strong></span>
                        </div>
                        <div class="progress mb-3" style="height: 12px; border-radius: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 28%;"></div>
                        </div>
                    </div>

                    <div class="p-3 bg-white rounded border mb-3">
                        <div class="d-flex align-items-center gap-2 text-success fw-bold mb-1">
                            <i class="bi bi-calendar-check"></i> Trạng thái: Ngưỡng an toàn
                        </div>
                        <div class="text-secondary small">
                            Nhiệt độ trung bình 28.5°C, độ ẩm đất tối ưu. Tiếp tục giám sát bẫy đèn và dữ liệu vi khí hậu từ trạm GB-02.
                        </div>
                    </div>

                    <button class="btn btn-sm btn-secondary w-100" disabled>
                        <i class="bi bi-check-all"></i> Đang Trong Ngưỡng Giám Sát Tự Động
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
