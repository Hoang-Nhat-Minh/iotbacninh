@extends('layouts.app')

@section('title', 'Cảnh Báo Bệnh Sương Mai Tự Động')

@section('content')
<x-page-header title="Cảnh Báo Bệnh Sương Mai Tự Động">
    <x-slot:breadcrumbs>
        <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span class="text-primary fw-bold">Bệnh sương mai</span>
    </x-slot:breadcrumbs>
</x-page-header>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title"><i class="bi bi-robot text-primary"></i> Trạng Thái Hoạt Động Của Core AI</h5>
                <span class="badge-status badge-active"><i class="bi bi-activity"></i> Core AI Online</span>
            </div>
            <div class="card-body">
                <div class="p-3 rounded border mb-4" style="background-color: var(--bg-body);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Kích hoạt chế độ quét tự động định kỳ</h6>
                            <p class="text-muted small mb-0">Hệ thống sẽ lấy ảnh mới nhất từ 4 trạm quan trắc theo lịch trình và gửi sang Core AI.</p>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input" type="checkbox" id="switch-auto-ai" checked style="width: 48px; height: 24px; cursor: pointer;">
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-3">Quy Trình Xử Lý Core AI (Pipeline)</h6>
                <div class="row g-2 text-center">
                    <div class="col-3">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-primary fw-bold small"><i class="bi bi-cloud-arrow-up"></i> Bước 1</div>
                            <div style="font-size: 12px;">Tiếp nhận ảnh</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-primary fw-bold small"><i class="bi bi-gear-wide"></i> Bước 2</div>
                            <div style="font-size: 12px;">Tiền xử lý ảnh</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded bg-light">
                            <div class="text-primary fw-bold small"><i class="bi bi-search"></i> Bước 3</div>
                            <div style="font-size: 12px;">Phân tích nấm</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-2 border rounded" style="background-color: #fef2f2; border-color: #fecaca !important;">
                            <div class="text-danger fw-bold small"><i class="bi bi-bell-fill"></i> Bước 4</div>
                            <div style="font-size: 12px;">Gửi cảnh báo</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-pie-chart text-primary"></i> Thống Kê 24 Giờ Qua</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                    <span class="text-secondary">Tổng số ảnh camera đã quét:</span>
                    <strong class="text-dark">128 ảnh</strong>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                    <span class="text-secondary">Phát hiện nguy cơ sương mai:</span>
                    <strong class="text-danger">3 vụ việc</strong>
                </div>
                <div class="d-flex justify-content-between mb-3 pb-2 border-bottom">
                    <span class="text-secondary">Thông báo tự động đã gửi tới chủ vườn:</span>
                    <strong class="text-success">3 tin nhắn</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary">Độ chính xác mô hình AI:</span>
                    <strong class="text-primary">94.8%</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title"><i class="bi bi-clock-history text-primary"></i> Nhật Ký Quét & Cảnh Báo Tự Động Gần Đây</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="custom-table mb-0">
                <thead>
                    <tr>
                        <th>Thời gian quét</th>
                        <th>Trạm & Vùng trồng</th>
                        <th>Hình ảnh camera</th>
                        <th>Kết quả Core AI</th>
                        <th>Độ tin cậy</th>
                        <th>Trạng thái thông báo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>14/08/2026 06:30</td>
                        <td>
                            <strong>Trạm QV-04</strong>
                            <div class="text-muted small">Khu Trồng Rau Quế Võ (VT-04)</div>
                        </td>
                        <td>
                            <img src="https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=80&h=80" alt="leaf" class="rounded border" style="width: 48px; height: 48px; object-fit: cover;">
                        </td>
                        <td>
                            <span class="badge-status" style="background: #fee2e2; color: #b91c1c; border-color: #f87171;">
                                <i class="bi bi-exclamation-triangle-fill"></i> Phát hiện bệnh sương mai
                            </span>
                        </td>
                        <td><strong class="text-danger">96.2%</strong></td>
                        <td>
                            <span class="badge bg-success"><i class="bi bi-send-check"></i> Đã gửi chủ vườn (Hoàng Thị Mai)</span>
                        </td>
                    </tr>
                    <tr>
                        <td>14/08/2026 06:00</td>
                        <td>
                            <strong>Trạm TT-01</strong>
                            <div class="text-muted small">Vườn Bưởi Thuận Thành (VT-01)</div>
                        </td>
                        <td>
                            <img src="https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=80&h=80" alt="leaf" class="rounded border" style="width: 48px; height: 48px; object-fit: cover;">
                        </td>
                        <td>
                            <span class="badge-status badge-active">
                                <i class="bi bi-check-circle-fill"></i> Lá khỏe mạnh
                            </span>
                        </td>
                        <td><strong class="text-success">98.5%</strong></td>
                        <td><span class="text-muted small">Không có cảnh báo</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
