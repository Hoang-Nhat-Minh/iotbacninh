@extends('layouts.app')

@section('title', 'Kiểm Tra Vòng Đời Sâu Đục Cuống')

@section('content')
    <x-page-header title="Vòng Đời Sâu Đục Cuống">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>AI</span>
            <span>/</span>
            <span class="text-primary fw-bold">Vòng đời sâu hại</span>
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center mb-4">
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 13px;">Chọn vùng trồng cần kiểm tra</label>
                    <select id="select-lifecycle-zone" class="form-select" onchange="onZoneSelect(this.value)">
                        <option value="1">Vườn Bưởi Thuận Thành (VT-01) - Chủ vườn: Lê Hoàng Cường</option>
                        <option value="2">Vùng Cà Chua Gia Bình (VT-02) - Chủ vườn: Phạm Đức Dũng</option>
                        <option value="3">Vườn Cà Rốt Lương Tài (VT-03) - Chủ vườn: Trần Văn Hùng</option>
                        <option value="4">Khu Trồng Rau Quế Võ (VT-04) - Chủ vườn: Hoàng Thị Mai</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end" style="margin-top: 32px;">
                    <button type="button" class="btn btn-primary" id="btn-run-gdd" onclick="runAiGdd()">
                        <i class="bi bi-cpu-fill"></i> Truy Vấn Vi Khí Hậu & Gửi Sang Core AI
                    </button>
                </div>
            </div>

            <div class="p-4 border rounded" style="background-color: var(--bg-body);">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1" id="zone-display-name">Vườn Bưởi Thuận Thành (VT-01)</h5>
                        <span class="text-muted small">Dữ liệu lấy từ trạm IoT Thuận Thành (TT-01) &bull; Nhiệt độ trung
                            bình: 29.4°C</span>
                    </div>
                    <span class="badge bg-danger fs-6 px-3 py-2" id="zone-risk-badge"><i
                            class="bi bi-exclamation-triangle-fill"></i> Sắp bùng phát sâu non (97% GDD)</span>
                </div>

                <h6 class="fw-bold text-dark mb-3">Biểu Đồ Tiến Trình Vòng Đời (Tổng Nhiệt Hữu Hiệu GDD: 485 / 500 độ-ngày)
                </h6>
                <div class="row g-2 text-center mb-4">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-white">
                            <div class="text-muted small">Giai đoạn 1</div>
                            <h6 class="fw-bold text-success mb-1"><i class="bi bi-egg"></i> Trứng</h6>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Hoàn thành
                                (100%)</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-white shadow-sm border-danger">
                            <div class="text-danger small fw-bold">Giai đoạn 2 (Hiện tại)</div>
                            <h6 class="fw-bold text-danger mb-1"><i class="bi bi-bug-fill"></i> Sâu non tuổi 2-3</h6>
                            <span class="badge bg-danger text-white">Đang đục trái (97%)</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-white opacity-50">
                            <div class="text-muted small">Giai đoạn 3</div>
                            <h6 class="fw-bold text-muted mb-1"><i class="bi bi-shield"></i> Nhộng</h6>
                            <span class="badge bg-light text-muted border">Dự kiến: 18/08</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-white opacity-50">
                            <div class="text-muted small">Giai đoạn 4</div>
                            <h6 class="fw-bold text-muted mb-1"><i class="bi bi-feather"></i> Bướm trưởng thành</h6>
                            <span class="badge bg-light text-muted border">Dự kiến: 24/08</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-white border rounded">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-check text-success"></i> Biện Pháp Phòng Trừ
                        Khi Ở Giai Đoạn Này:</h6>
                    <p class="text-secondary small mb-1">
                        1. <strong>Thời điểm vàng phun trừ:</strong> Chiều tối ngày 15/08/2026 (trước khi ấu trùng tuổi 3
                        đục sâu qua vỏ quả bưởi).
                    </p>
                    <p class="text-secondary small mb-1">
                        2. <strong>Hoạt chất khuyến nghị:</strong> Sử dụng thuốc sinh học <em>Bacillus thuringiensis
                            (BT)</em> hoặc <em>Emamectin benzoate</em> để bảo vệ thiên địch và an toàn vệ sinh thực phẩm.
                    </p>
                    <p class="text-secondary small mb-0">
                        3. <strong>Vệ sinh vườn:</strong> Thu gom và tiêu hủy các quả rụng dưới gốc để cắt đứt nguồn lây lan
                        sang lứa tiếp theo.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function onZoneSelect(val) {
            if (val === '2') {
                document.getElementById('zone-display-name').textContent = 'Vùng Cà Chua Gia Bình (VT-02)';
                document.getElementById('zone-risk-badge').className = 'badge bg-success fs-6 px-3 py-2';
                document.getElementById('zone-risk-badge').innerHTML =
                    '<i class="bi bi-check-circle-fill"></i> An toàn (28% GDD)';
            } else {
                document.getElementById('zone-display-name').textContent = 'Vườn Bưởi Thuận Thành (VT-01)';
                document.getElementById('zone-risk-badge').className = 'badge bg-danger fs-6 px-3 py-2';
                document.getElementById('zone-risk-badge').innerHTML =
                    '<i class="bi bi-exclamation-triangle-fill"></i> Sắp bùng phát sâu non (97% GDD)';
            }
        }

        function runAiGdd() {
            const btn = document.getElementById('btn-run-gdd');
            btn.disabled = true;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span> Core AI đang chuẩn hóa & tính toán GDD...';

            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cpu-fill"></i> Truy Vấn Vi Khí Hậu & Gửi Sang Core AI';
                showToast('Core AI đã phân tích và cập nhật biểu đồ vòng đời thành công!', 'success');
            }, 1200);
        }
    </script>
@endpush
