@extends('layouts.app')

@section('title', 'Trang Tổng Quan - Hệ Thống IoT Bắc Ninh')
@section('page_title', 'Trang Tổng Quan')

@section('content')
    <!-- High-Priority Alerts Block -->
    @if (isset($highPriorityAlert))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger d-flex align-items-center p-3 shadow-sm border-start border-4 border-danger mb-4"
                    role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-danger"></i>
                    <div>
                        <h5 class="alert-heading fw-bold mb-1">{{ $highPriorityAlert['title'] }}</h5>
                        <p class="mb-0">{{ $highPriorityAlert['message'] }}
                        </p>
                        <a href="{{ url('/notifications') }}" class="btn btn-sm btn-danger mt-2 fw-bold">Xem Chi Tiết Cảnh Báo
                            <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Statistics Grid -->
    <div class="row">
        <!-- Card 1 -->
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card h-100 border-start border-4 border-success bg-white">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold text-uppercase mb-2">Vùng Trồng</h6>
                            <h2 class="fw-bold mb-0 text-success">{{ $stats['zones_count'] }} Vùng</h2>
                        </div>
                        <div class="bg-success-subtle text-success rounded p-3">
                            <i class="bi bi-grid-3x3-gap-fill fs-2"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Tổng diện tích:
                            {{ $stats['zones_area'] }} héc-ta</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card h-100 border-start border-4 border-primary bg-white">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold text-uppercase mb-2">Trạm Quan Trắc</h6>
                            <h2 class="fw-bold mb-0 text-primary">{{ $stats['stations_count'] }} Trạm</h2>
                        </div>
                        <div class="bg-primary-subtle text-primary rounded p-3">
                            <i class="bi bi-cpu-fill fs-2"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Tất cả đang hoạt động
                            tốt</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card h-100 border-start border-4 border-warning bg-white">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold text-uppercase mb-2">Cảnh Báo Kích Hoạt</h6>
                            <h2 class="fw-bold mb-0 text-warning">{{ $stats['alerts_count'] }} Cảnh Báo</h2>
                        </div>
                        <div class="bg-warning-subtle text-warning rounded p-3">
                            <i class="bi bi-bell-fill fs-2"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-danger fw-bold"><i
                                class="bi bi-arrow-up-right me-1"></i>{{ $stats['alerts_high_count'] }} cảnh báo mức độ
                            CAO</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card h-100 border-start border-4 border-info bg-white">
                <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold text-uppercase mb-2">Lịch Chăm Sóc Hôm Nay</h6>
                            <h2 class="fw-bold mb-0 text-info">{{ $stats['care_today_count'] }} Vùng</h2>
                        </div>
                        <div class="bg-info-subtle text-info rounded p-3">
                            <i class="bi bi-calendar-check-fill fs-2"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted"><i class="bi bi-clock me-1"></i>Xem nhật ký chăm sóc</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Primary Dashboard Panels -->
    <div class="row">
        <!-- Left panel: Sensor Overview -->
        <div class="col-xl-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-thermometer-half text-success me-2"></i>Chỉ Số Cảm Biến Thời Gian Thực</span>
                    <span class="badge bg-success">Cập nhật 5 phút trước</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border-top">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th>Tên Trạm</th>
                                    <th>Nhiệt Độ (°C)</th>
                                    <th>Độ Ẩm Khí Quyển (%)</th>
                                    <th>Độ Ẩm Đất (%)</th>
                                    <th>Cường Độ Sáng (lux)</th>
                                    <th>Trạng Thái</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stations as $station)
                                    <tr>
                                        <td class="fw-bold">{{ $station['name'] }}</td>
                                        <td
                                            class="{{ $station['status_class'] === 'danger' ? 'text-danger fw-bold' : '' }}">
                                            {{ $station['temp'] }} °C</td>
                                        <td
                                            class="{{ $station['status_class'] === 'danger' ? 'text-danger fw-bold' : '' }}">
                                            {{ $station['humidity'] }}%</td>
                                        <td>{{ $station['soil_moisture'] }}%</td>
                                        <td>{{ $station['light'] }} lx</td>
                                        <td><span
                                                class="badge bg-{{ $station['status_class'] }}-subtle text-{{ $station['status_class'] }} border border-{{ $station['status_class'] }}-subtle px-2 py-1">{{ $station['status'] }}</span>
                                        </td>
                                        <td><a href="{{ url('/stations/' . $station['id']) }}"
                                                class="btn btn-sm btn-outline-success">Xem
                                                Trạm</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right panel: Quick Actions and System Status -->
        <div class="col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <span><i class="bi bi-lightning-charge-fill text-success me-2"></i>Thao Tác Nhanh</span>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ url('/map') }}"
                            class="btn btn-light border py-3 text-start fs-6 d-flex align-items-center">
                            <i class="bi bi-map-fill text-success fs-3 me-3"></i>
                            <div>
                                <div class="fw-bold">Mở Bản Đồ Vùng Trồng</div>
                                <small class="text-muted">Kiểm tra ranh giới & vị trí camera</small>
                            </div>
                        </a>

                        <a href="{{ url('/care') }}"
                            class="btn btn-light border py-3 text-start fs-6 d-flex align-items-center">
                            <i class="bi bi-pencil-square text-success fs-3 me-3"></i>
                            <div>
                                <div class="fw-bold">Ghi Nhật Ký Chăm Sóc</div>
                                <small class="text-muted">Lưu lịch sử bón phân, tưới tiêu</small>
                            </div>
                        </a>

                        <a href="{{ url('/chatbot') }}"
                            class="btn btn-light border py-3 text-start fs-6 d-flex align-items-center">
                            <i class="bi bi-robot text-success fs-3 me-3"></i>
                            <div>
                                <div class="fw-bold">Hỏi Trợ Lý Nông Nghiệp</div>
                                <small class="text-muted">Hỏi đáp AI, chuẩn đoán sâu bệnh</small>
                            </div>
                        </a>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3"><i class="bi bi-gear-fill me-2 text-secondary"></i>Cấu Hình Hệ Thống IoT</h6>
                    <div class="bg-light p-3 rounded border">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Trạng thái IoT:</span>
                            <span class="badge bg-success">Đang hoạt động</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tần suất gửi dữ liệu:</span>
                            <strong class="text-dark">15 phút / lần</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Chẩn đoán AI tự động:</span>
                            <strong class="text-success">Đã kích hoạt</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
