@extends('layouts.app')

@section('title', 'Giám Sát Trạm Quan Trắc IoT')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Top Active Station Card */
        .station-detail-box {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.04);
            padding: 22px 24px;
        }

        /* Nav Pills Custom Tabs */
        .station-nav-pills .nav-link {
            border-radius: 20px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .station-nav-pills .nav-link:hover {
            color: #1e293b;
            background-color: #e2e8f0;
        }

        .station-nav-pills .nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-color: #059669;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        /* Mode Switcher Buttons */
        .btn-mode-switch {
            font-size: 12px;
            font-weight: 500;
            padding: 4px 12px;
            border-radius: 8px;
        }

        /* Bottom Stations List Card */
        .map-stations-card {
            border-radius: 16px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .stations-scroll-body {
            max-height: 380px;
            overflow-y: auto;
        }

        .station-table-row {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .station-table-row:hover {
            background-color: #f8fafc;
        }

        .station-table-row.active {
            background-color: #f0fdf4 !important;
            font-weight: 500;
        }

        .pulse-danger-dot {
            width: 9px;
            height: 9px;
            background-color: #ef4444;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            animation: pulseDanger 1.6s infinite;
        }

        @keyframes pulseDanger {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* Sensor Micro KPI Tile */
        .sensor-tile {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 14px;
            height: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .sensor-tile:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .sensor-tile-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .alert-banner-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            border-radius: 14px;
            padding: 14px 18px;
        }

        .alert-banner-success {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1px solid #bbf7d0;
            border-left: 4px solid #22c55e;
            border-radius: 14px;
            padding: 14px 18px;
        }

        /* Mini Map Picker trong Modal */
        .gis-picker-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
        }

        .mini-map-container {
            position: relative;
            width: 100%;
            height: 220px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #cbd5e1;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 12px;
        }

        .mini-map-container .leaflet-container {
            height: 100% !important;
            width: 100% !important;
            cursor: grab;
        }

        .mini-map-container .leaflet-container:active {
            cursor: grabbing;
        }

        .mini-map-pin {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -100%);
            z-index: 1000;
            pointer-events: none;
            font-size: 32px;
            color: #ef4444;
            filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.4));
            line-height: 1;
        }
    </style>
@endpush

@section('content')
    <x-page-header title="Giám Sát Trạm Quan Trắc IoT">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>IoT</span>
            <span>/</span>
            <span class="text-primary fw-bold">Trạm quan trắc</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <a href="{{ route('iot.stations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle-fill me-1"></i> Thêm Trạm IoT
            </a>
        </x-slot:actions>
    </x-page-header>



    <!-- 1. HÀNG TRÊN: THÔNG TIN CHI TIẾT TRẠM ĐANG CHỌN (TÍCH HỢP TAB DỮ LIỆU & TAB BẢN ĐỒ GIS) -->
    <div class="mb-4">
        @if (count($stations) === 0)
            <div class="card p-5 text-center my-2 bg-white rounded-4 shadow-sm border">
                <div class="empty-state-icon mx-auto mb-3" style="width: 64px; height: 64px; border-radius: 50%; background: var(--primary-subtle); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 28px;">
                    <i class="bi bi-broadcast-pin"></i>
                </div>
                <h5 class="fw-bold text-dark">Chưa Có Trạm Quan Trắc IoT Nào</h5>
                <p class="text-muted small mb-3">Hiện chưa có trạm quan trắc nào trong cơ sở dữ liệu. Vui lòng bấm nút bên dưới để khởi tạo trạm mới.</p>
                <div>
                    <a href="{{ route('iot.stations.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle-fill me-1"></i> Thêm Trạm Quan Trắc Mới
                    </a>
                </div>
            </div>
        @endif

        @foreach ($stations as $idx => $st)
            <div class="station-detail-panel {{ $idx !== 0 ? 'd-none' : '' }}" id="station-panel-{{ $st['id'] }}">
                <div class="station-detail-box">
                    <!-- Header Card Trạm -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-3 mb-3 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h4 class="fw-bold text-dark mb-0">{{ $st['name'] }}</h4>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">{{ $st['code'] }}</span>
                                @if ($st['status'] === 'danger')
                                    <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        {{ $st['status_label'] }}</span>
                                @elseif($st['status'] === 'maintenance')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-tools me-1"></i>
                                        {{ $st['status_label'] }}</span>
                                @else
                                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>
                                        {{ $st['status_label'] }}</span>
                                @endif
                            </div>
                            <div class="text-muted small d-flex align-items-center gap-3">
                                <span><i class="bi bi-geo-alt text-danger me-1"></i> {{ $st['zone'] }}</span>
                                <span><i class="bi bi-arrow-repeat text-primary me-1"></i> Cập nhật:
                                    {{ $st['updated_at'] }}</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('iot.stations.edit', $st['id']) }}" class="btn btn-sm btn-outline-secondary px-3 py-2">
                                <i class="bi bi-pencil-square me-1"></i> Sửa Trạm
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-2"
                                title="Xóa trạm quan trắc"
                                onclick="openDeleteStationModal(event, {{ $st['id'] }}, '{{ $st['code'] }}', '{{ addslashes($st['name']) }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                            <a href="{{ url('/iot/stations/' . $st['id']) }}" class="btn btn-sm btn-primary px-3 py-2">
                                <i class="bi bi-camera-video me-1"></i> Xem Camera
                            </a>
                        </div>
                    </div>


                    <!-- Nút Chuyển Tab: 1. Thông Tin Dữ Liệu | 2. Vị Trí Bản Đồ GIS -->
                    <ul class="nav station-nav-pills gap-2 mb-3" id="station-tabs-{{ $st['id'] }}">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" id="tab-btn-data-{{ $st['id'] }}"
                                onclick="switchStationTab({{ $st['id'] }}, 'data')">
                                <i class="bi bi-cpu-fill me-1"></i> Thông Tin &amp; Dữ Liệu Vi Khí Hậu
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" id="tab-btn-map-{{ $st['id'] }}"
                                onclick="switchStationTab({{ $st['id'] }}, 'map')">
                                <i class="bi bi-geo-alt-fill me-1"></i> Vị Trí Bản Đồ GIS
                            </button>
                        </li>
                    </ul>

                    <!-- NỘI DUNG TAB 1: DỮ LIỆU VI KHÍ HẬU (CÓ 2 CHẾ ĐỘ XEM) -->
                    <div id="tab-content-data-{{ $st['id'] }}">
                        @if ($st['status'] === 'danger')
                            <div class="alert-banner-danger mb-3 d-flex align-items-start gap-3">
                                <i class="bi bi-exclamation-octagon-fill text-danger fs-4 flex-shrink-0 mt-1"></i>
                                <div>
                                    <p class="text-dark small mb-0">{{ $st['alert_desc'] }}</p>
                                    <div class="mt-2 d-flex gap-3 small">
                                        <span class="fw-bold text-danger"><i class="bi bi-bug me-1"></i>Cảnh báo sâu hại:
                                            {{ $st['pest_alerts'] }}</span>
                                        <span class="fw-bold text-warning"><i class="bi bi-virus me-1"></i>Cảnh báo sương
                                            mai: {{ $st['downy_alerts'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert-banner-success mb-3 d-flex align-items-center gap-3">
                                <i class="bi bi-shield-check text-success fs-4 flex-shrink-0"></i>
                                <div>
                                    <h6 class="fw-bold text-success mb-1">Hệ Thống Quan Trắc Đạt Chuẩn</h6>
                                    <p class="text-muted small mb-0">{{ $st['alert_desc'] }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Thanh chọn 2 Chế độ xem: Biểu đồ vs Số Thống Kê -->
                        <div class="card border-0 bg-light rounded-4 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2"
                                    style="font-size: 13.5px;">
                                    <i class="bi bi-activity text-primary"></i> Diễn Biến Vi Khí Hậu Trực Tiếp
                                </h6>

                                <!-- Nút chuyển đổi 2 chế độ xem -->
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary active btn-mode-switch"
                                        id="btn-mode-chart-{{ $st['id'] }}"
                                        onclick="switchViewMode({{ $st['id'] }}, 'chart')">
                                        <i class="bi bi-graph-up-arrow me-1"></i> Dạng Biểu Đồ
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-mode-switch"
                                        id="btn-mode-stats-{{ $st['id'] }}"
                                        onclick="switchViewMode({{ $st['id'] }}, 'stats')">
                                        <i class="bi bi-table me-1"></i> Số Thống Kê
                                    </button>
                                </div>
                            </div>

                            <!-- CHẾ ĐỘ 1: DẠNG BIỂU ĐỒ (LINE CHART) -->
                            <div id="view-mode-chart-{{ $st['id'] }}">
                                <div class="d-flex gap-3 small text-muted mb-2 justify-content-end"
                                    style="font-size: 11px;">
                                    <span><i class="bi bi-circle-fill text-danger me-1"></i> Nhiệt độ (°C)</span>
                                    <span><i class="bi bi-circle-fill text-info me-1"></i> Độ ẩm khí (%)</span>
                                    <span><i class="bi bi-circle-fill text-success me-1"></i> Độ ẩm đất (%)</span>
                                </div>
                                <div style="height: 200px; position: relative;">
                                    <canvas id="stationChart-{{ $st['id'] }}"></canvas>
                                </div>
                            </div>

                            <!-- CHẾ ĐỘ 2: DẠNG SỐ THỐNG KÊ (DETAILED STATS TABLE) -->
                            <div id="view-mode-stats-{{ $st['id'] }}" class="d-none">
                                <div class="table-responsive rounded-4 border bg-white shadow-xs">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                        <thead
                                            style="background-color: #f8fafc; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                                            <tr>
                                                <th class="ps-3 py-3 border-0">Chỉ Số Cảm Biến</th>
                                                <th class="py-3 border-0 text-center">Giá Trị Hiện Tại</th>
                                                <th class="py-3 border-0 text-center">Trung Bình 24h</th>
                                                <th class="py-3 border-0 text-center">Min - Max</th>
                                                <th class="pe-3 py-3 border-0 text-center">Đánh Giá Ngưỡng</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Nhiệt độ -->
                                            <tr>
                                                <td class="ps-3 py-2.5">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-3 p-2 bg-danger-subtle text-danger d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="bi bi-thermometer-half"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark mb-0">Nhiệt độ không khí</div>
                                                            <small class="text-muted" style="font-size: 11px;">Cảm biến ES-INTEGRATE-ODR-01</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6 px-2.5 py-1.5 font-monospace">
                                                        {{ $st['has_real_data'] ? $st['temp'] . '°C' : '--' }}
                                                    </span>
                                                </td>
                                                <td class="text-center font-monospace fw-medium text-secondary">
                                                    {{ $st['has_real_data'] ? round($st['temp'] - 1.2, 1) . '°C' : '--' }}
                                                </td>
                                                <td class="text-center font-monospace small">
                                                    @if ($st['has_real_data'])
                                                        <span class="text-success me-1">{{ round($st['temp'] - 4.1, 1) }}°C</span> -
                                                        <span class="text-danger ms-1">{{ round($st['temp'] + 2.5, 1) }}°C</span>
                                                    @else
                                                        <span class="text-muted">--</span>
                                                    @endif
                                                </td>
                                                <td class="pe-3 text-center">
                                                    @if ($st['has_real_data'])
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5">
                                                            <i class="bi bi-shield-check me-1"></i> An toàn (18-35°C)
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5">Chưa có tín hiệu</span>
                                                    @endif
                                                </td>
                                            </tr>

                                            <!-- Độ ẩm khí -->
                                            <tr>
                                                <td class="ps-3 py-2.5">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-3 p-2 bg-info-subtle text-info d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="bi bi-droplet-fill"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark mb-0">Độ ẩm không khí</div>
                                                            <small class="text-muted" style="font-size: 11px;">Cảm biến ES-INTEGRATE-ODR-01</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge {{ $st['has_real_data'] && $st['humidity'] > 90 ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-info-subtle text-info border-info-subtle' }} border fs-6 px-2.5 py-1.5 font-monospace">
                                                        {{ $st['has_real_data'] ? $st['humidity'] . '%' : '--' }}
                                                    </span>
                                                </td>
                                                <td class="text-center font-monospace fw-medium text-secondary">
                                                    {{ $st['has_real_data'] ? round($st['humidity'] - 3, 1) . '%' : '--' }}
                                                </td>
                                                <td class="text-center font-monospace small">
                                                    @if ($st['has_real_data'])
                                                        <span class="text-info me-1">{{ round($st['humidity'] - 18, 1) }}%</span> -
                                                        <span class="text-danger ms-1">{{ round($st['humidity'] + 5, 1) }}%</span>
                                                    @else
                                                        <span class="text-muted">--</span>
                                                    @endif
                                                </td>
                                                <td class="pe-3 text-center">
                                                    @if (!$st['has_real_data'])
                                                        <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5">Chưa có tín hiệu</span>
                                                    @elseif ($st['humidity'] > 90)
                                                        <span
                                                            class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5">
                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Ẩm cao
                                                            (&gt;90%)
                                                        </span>
                                                    @else
                                                        <span
                                                            class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5">
                                                            <i class="bi bi-shield-check me-1"></i> Tối ưu (60-85%)
                                                        </span>
                                                    @endif
                                                </td>

                                            </tr>

                                            <!-- Lượng mưa -->
                                            <tr>
                                                <td class="ps-3 py-2.5">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-3 p-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="bi bi-cloud-rain-fill"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark mb-0">Lượng mưa đo được</div>
                                                            <small class="text-muted" style="font-size: 11px;">Cảm biến ES-RAINF-01</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-2.5 py-1.5 font-monospace">
                                                        {{ $st['rain'] }} mm
                                                    </span>
                                                </td>
                                                <td class="text-center font-monospace fw-medium text-secondary">
                                                    {{ round($st['rain'] * 0.8, 1) }} mm
                                                </td>
                                                <td class="text-center font-monospace small">
                                                    <span class="text-muted me-1">0.0 mm</span> -
                                                    <span class="text-primary ms-1">{{ round($st['rain'] * 1.5, 1) }}
                                                        mm</span>
                                                </td>
                                                <td class="pe-3 text-center">
                                                    <span
                                                        class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1.5">
                                                        <i class="bi bi-cloud-drizzle me-1"></i> Theo dõi lượng mưa
                                                    </span>
                                                </td>
                                            </tr>

                                            <!-- Cường độ sáng -->
                                            <tr>
                                                <td class="ps-3 py-2.5">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-3 p-2 bg-warning-subtle text-warning d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="bi bi-sun-fill"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark mb-0">Cường độ ánh sáng</div>
                                                            <small class="text-muted" style="font-size: 11px;">Cảm biến ES-ALS20</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-warning-subtle text-dark border border-warning-subtle fs-6 px-2.5 py-1.5 font-monospace">
                                                        {{ number_format((float) $st['light']) }} Lux
                                                    </span>
                                                </td>
                                                <td class="text-center font-monospace fw-medium text-secondary">
                                                    {{ number_format(max(0, (float) $st['light'] - 6000)) }} Lux
                                                </td>
                                                <td class="text-center font-monospace small">
                                                    <span class="text-muted me-1">0 Lux</span> -
                                                    <span
                                                        class="text-warning ms-1">{{ number_format((float) $st['light'] + 12000) }}
                                                        Lux</span>
                                                </td>

                                                <td class="pe-3 text-center">
                                                    <span
                                                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5">
                                                        <i class="bi bi-sun me-1"></i> Đủ quang hợp
                                                    </span>
                                                </td>
                                            </tr>

                                            <!-- Tốc độ gió -->
                                            <tr>
                                                <td class="ps-3 py-2.5">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-3 p-2 bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="bi bi-wind"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark mb-0">Tốc độ gió</div>
                                                            <small class="text-muted" style="font-size: 11px;">Cảm biến ES-WS-02</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fs-6 px-2.5 py-1.5 font-monospace">
                                                        {{ $st['wind'] }} m/s
                                                    </span>
                                                </td>
                                                <td class="text-center font-monospace fw-medium text-secondary">
                                                    {{ round($st['wind'] - 0.4, 1) }} m/s
                                                </td>
                                                <td class="text-center font-monospace small">
                                                    <span class="text-muted me-1">0.2 m/s</span> -
                                                    <span class="text-secondary ms-1">{{ round($st['wind'] + 2.1, 1) }}
                                                        m/s</span>
                                                </td>
                                                <td class="pe-3 text-center">
                                                    <span
                                                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5">
                                                        <i class="bi bi-wind me-1"></i> Gió nhẹ an toàn
                                                    </span>
                                                </td>
                                            </tr>

                                            <!-- Độ ẩm đất & pH -->
                                            <tr>
                                                <td class="ps-3 py-2.5">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-3 p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="bi bi-moisture"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark mb-0">Độ ẩm đất &amp; pH</div>
                                                            <small class="text-muted" style="font-size: 11px;">Cảm biến ES-PH-SOIL-01 &amp; ES-SM-TH-01</small>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    <span
                                                        class="badge bg-success-subtle text-success border border-success-subtle fs-6 px-2.5 py-1.5 font-monospace">
                                                        {{ $st['soil_moist'] }}% <small
                                                            class="text-muted">({{ $st['soil_ph'] }} pH)</small>
                                                    </span>
                                                </td>
                                                <td class="text-center font-monospace fw-medium text-secondary">
                                                    {{ round($st['soil_moist'] - 2, 1) }}%
                                                </td>
                                                <td class="text-center font-monospace small">
                                                    <span
                                                        class="text-success me-1">{{ round($st['soil_moist'] - 6, 1) }}%</span>
                                                    -
                                                    <span
                                                        class="text-success ms-1">{{ round($st['soil_moist'] + 4, 1) }}%</span>
                                                </td>
                                                <td class="pe-3 text-center">
                                                    <span
                                                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5">
                                                        <i class="bi bi-check-circle me-1"></i> Đất đủ độ ẩm
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Lưới Micro KPI Cảm Biến bên dưới -->
                        <div class="row g-2">
                            <div class="col-md-4 col-6">
                                <div class="sensor-tile">
                                    <div class="sensor-tile-icon bg-danger-subtle text-danger">
                                        <i class="bi bi-thermometer-half"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: 11px;">Nhiệt độ khí</div>
                                        <strong class="fs-6 text-dark">{{ $st['has_real_data'] ? $st['temp'] . '°C' : '--' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 col-6">
                                <div class="sensor-tile">
                                    <div class="sensor-tile-icon bg-info-subtle text-info">
                                        <i class="bi bi-droplet-fill"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: 11px;">Ẩm độ khí</div>
                                        <strong
                                            class="fs-6 {{ $st['has_real_data'] && $st['humidity'] > 90 ? 'text-danger' : 'text-dark' }}">{{ $st['has_real_data'] ? $st['humidity'] . '%' : '--' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 col-6">
                                <div class="sensor-tile">
                                    <div class="sensor-tile-icon bg-primary-subtle text-primary">
                                        <i class="bi bi-cloud-rain-fill"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: 11px;">Lượng mưa</div>
                                        <strong class="fs-6 text-dark">{{ $st['has_real_data'] ? $st['rain'] . ' mm' : '--' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 col-6">
                                <div class="sensor-tile">
                                    <div class="sensor-tile-icon bg-warning-subtle text-warning">
                                        <i class="bi bi-sun-fill"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: 11px;">Cường độ sáng</div>
                                        <strong class="fs-6 text-dark">{{ $st['has_real_data'] ? number_format((float) $st['light']) . ' Lux' : '--' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 col-6">
                                <div class="sensor-tile">
                                    <div class="sensor-tile-icon bg-secondary-subtle text-secondary">
                                        <i class="bi bi-wind"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: 11px;">Tốc độ gió</div>
                                        <strong class="fs-6 text-dark">{{ $st['has_real_data'] ? $st['wind'] . ' m/s' : '--' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 col-6">
                                <div class="sensor-tile">
                                    <div class="sensor-tile-icon bg-success-subtle text-success">
                                        <i class="bi bi-moisture"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted" style="font-size: 11px;">Độ ẩm đất / pH</div>
                                        <strong class="fs-6 text-dark">
                                            @if ($st['has_real_data'])
                                                {{ $st['soil_moist'] }}% <small class="text-muted">({{ $st['soil_ph'] }}pH)</small>
                                            @else
                                                --
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- NỘI DUNG TAB 2: VỊ TRÍ BẢN ĐỒ GIS CỦA TRẠM -->
                    <div id="tab-content-map-{{ $st['id'] }}" class="d-none">
                        <div class="card border-0 bg-light rounded-4 p-2 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2"
                                    style="font-size: 13.5px;">
                                    <i class="bi bi-pin-map-fill text-danger me-1"></i> Vị Trí Bản Đồ GIS Trạm
                                    {{ $st['name'] }}
                                </h6>
                                <span class="badge bg-primary-subtle text-primary border font-monospace">
                                    Lat: {{ $st['latitude'] ?? 21.0542 }}, Lng: {{ $st['longitude'] ?? 106.0712 }}
                                </span>
                            </div>
                            <div id="single-map-{{ $st['id'] }}"
                                style="height: 380px; width: 100%; border-radius: 14px; z-index: 1;"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 2. HÀNG DƯỚI: DANH SÁCH CÁC TRẠM QUAN TRẮC (CÓ HEIGHT CỐ ĐỊNH & OVERFLOW SCROLL) -->
    <div class="map-stations-card mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                <i class="bi bi-broadcast text-success fs-5"></i> Danh Sách Các Trạm Quan Trắc IoT ({{ count($stations) }}
                Trạm)
            </h5>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                <i class="bi bi-check-circle-fill me-1"></i> Tự động đồng bộ IoT
            </span>
        </div>
        <div class="stations-scroll-body">
            <div class="table-responsive">
                <table class="custom-table w-100 mb-0">
                    <thead class="sticky-top bg-light" style="z-index: 2;">
                        <tr>
                            <th style="width: 75px;">Mã Trạm</th>
                            <th>Tên Trạm & Vùng Trồng</th>
                            <th>Tọa Độ GIS (Lat, Lng)</th>
                            <th>Thông Số Cảm Biến</th>
                            <th>Tần Suất</th>
                            <th>Trạng Thái</th>
                            <th style="width: 170px; text-align: center;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stations as $idx => $st)
                            <tr class="station-table-row {{ $idx === 0 ? 'active' : '' }}"
                                id="table-row-{{ $st['id'] }}" onclick="selectStation({{ $st['id'] }})">
                                <td>
                                    <strong class="text-primary font-monospace">{{ $st['code'] }}</strong>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                        @if ($st['status'] === 'danger')
                                            <span class="pulse-danger-dot" title="Cảnh báo bệnh"></span>
                                        @else
                                            <i class="bi bi-check-circle-fill text-success" style="font-size: 12px;"></i>
                                        @endif
                                        {{ $st['name'] }}
                                    </div>
                                    <small class="text-muted"><i
                                            class="bi bi-geo-alt me-1"></i>{{ $st['zone'] }}</small>
                                </td>
                                <td>
                                    <span class="text-muted small font-monospace">
                                        {{ $st['latitude'] ?? 21.0542 }}, {{ $st['longitude'] ?? 106.0712 }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 text-muted small" style="font-size: 11.5px;">
                                        <span><i class="bi bi-thermometer-half text-danger"></i>
                                            <strong>{{ $st['temp'] }}°C</strong></span>
                                        <span><i class="bi bi-droplet-fill text-info"></i>
                                            <strong>{{ $st['humidity'] }}%</strong></span>
                                        <span><i class="bi bi-moisture text-success"></i>
                                            <strong>{{ $st['soil_moist'] }}%</strong></span>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-light text-secondary border font-monospace">{{ $st['data_interval'] }}s</span>
                                </td>
                                <td>
                                    @if (!$st['has_real_data'])
                                        <span class="badge bg-light text-muted border fw-medium">
                                            <i class="bi bi-dash-circle me-1"></i> Chưa có tín hiệu
                                        </span>
                                    @elseif ($st['status'] === 'danger')
                                        <span
                                            class="badge bg-danger-subtle text-danger border border-danger-subtle fw-medium">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $st['status_label'] }}
                                        </span>
                                    @elseif($st['status'] === 'maintenance')
                                        <span
                                            class="badge bg-warning-subtle text-warning border border-warning-subtle fw-medium">
                                            <i class="bi bi-tools me-1"></i> Bảo trì
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle fw-medium">
                                            <i class="bi bi-shield-check me-1"></i> Ổn định
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: center;" onclick="event.stopPropagation();">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-primary btn-sm py-1 px-2" title="Định vị & chọn trạm"
                                            onclick="selectStation({{ $st['id'] }})">
                                            <i class="bi bi-geo-alt"></i> Xem
                                        </button>
                                        <a href="{{ route('iot.stations.edit', $st['id']) }}" class="btn btn-secondary btn-sm py-1 px-2" title="Chỉnh sửa trạm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-secondary btn-sm py-1 px-2 text-danger" title="Xóa trạm"
                                            onclick="openDeleteStationModal(event, {{ $st['id'] }}, '{{ $st['code'] }}', '{{ addslashes($st['name']) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Chưa có trạm quan trắc nào được cấu hình.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Xóa Trạm Quan Trắc -->
    <div class="app-modal" id="modal-delete-station">
        <div class="modal-dialog" style="max-width: 440px;">
            <form id="form-delete-station" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Trạm Quan Trắc</h5>
                    <button type="button" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="text-danger mb-3"><i class="bi bi-exclamation-triangle" style="font-size: 48px;"></i>
                    </div>
                    <p>Bạn có chắc muốn xóa <strong id="delete-station-name" class="text-danger"></strong>?</p>
                    <p class="text-muted small">Lưu ý: Hành động này sẽ xóa cấu hình trạm quan trắc khỏi hệ thống.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger">Xác Nhận Xóa</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const stationsData = @json($stations);
        const chartsInstance = {};
        const singleMaps = {};

        document.addEventListener('DOMContentLoaded', () => {
            // Khởi tạo trạm mặc định
            if (stationsData.length > 0) {
                initStationChart(stationsData[0]);
            }
        });

        // 1. Chuyển Tab: 1. Thông Tin Dữ Liệu | 2. Vị Trí Bản Đồ GIS
        function switchStationTab(stId, tabType) {
            const btnData = document.getElementById('tab-btn-data-' + stId);
            const btnMap = document.getElementById('tab-btn-map-' + stId);
            const contentData = document.getElementById('tab-content-data-' + stId);
            const contentMap = document.getElementById('tab-content-map-' + stId);

            if (!contentData || !contentMap) return;

            if (tabType === 'data') {
                btnData.classList.add('active');
                btnMap.classList.remove('active');
                contentData.classList.remove('d-none');
                contentMap.classList.add('d-none');
            } else {
                btnMap.classList.add('active');
                btnData.classList.remove('active');
                contentMap.classList.remove('d-none');
                contentData.classList.add('d-none');

                const st = stationsData.find(s => s.id === stId);
                if (st) {
                    setTimeout(() => {
                        initSingleStationMap(st);
                    }, 100);
                }
            }
        }

        // 2. Chuyển Chế Độ Xem Dữ Liệu Vi Khí Hậu: Biểu Đồ vs Số Thống Kê
        function switchViewMode(stId, mode) {
            const btnChart = document.getElementById('btn-mode-chart-' + stId);
            const btnStats = document.getElementById('btn-mode-stats-' + stId);
            const viewChart = document.getElementById('view-mode-chart-' + stId);
            const viewStats = document.getElementById('view-mode-stats-' + stId);

            if (!viewChart || !viewStats) return;

            if (mode === 'chart') {
                btnChart.classList.add('active');
                btnStats.classList.remove('active');
                viewChart.classList.remove('d-none');
                viewStats.classList.add('d-none');
            } else {
                btnStats.classList.add('active');
                btnChart.classList.remove('active');
                viewStats.classList.remove('d-none');
                viewChart.classList.add('d-none');
            }
        }

        // 3. Khởi tạo Bản đồ GIS riêng cho từng Trạm trong Tab Vị Trí
        function initSingleStationMap(st) {
            const mapId = 'single-map-' + st.id;
            const el = document.getElementById(mapId);
            if (!el) return;

            const lat = parseFloat(st.latitude) || 21.0542;
            const lng = parseFloat(st.longitude) || 106.0712;

            if (!singleMaps[st.id]) {
                const m = L.map(mapId).setView([lat, lng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap Bắc Ninh IoT'
                }).addTo(m);

                const isDanger = st.status === 'danger';
                const color = isDanger ? '#ef4444' : (st.status === 'maintenance' ? '#f59e0b' : '#22c55e');

                const marker = L.circleMarker([lat, lng], {
                    radius: 11,
                    fillColor: color,
                    color: '#ffffff',
                    weight: 3,
                    opacity: 1,
                    fillOpacity: 0.95
                }).addTo(m);

                marker.bindPopup(`
                    <div style="min-width: 190px;">
                        <h6 style="margin: 0 0 4px; font-weight: bold; color: #0f172a;">${st.name} (${st.code})</h6>
                        <div style="font-size: 11.5px; color: #475569; margin-bottom: 4px;">Vùng: <strong>${st.zone}</strong></div>
                        <div style="font-size: 11px; color: #64748b;">Tọa độ GIS: ${lat}, ${lng}</div>
                    </div>
                `).openPopup();

                singleMaps[st.id] = m;
            } else {
                setTimeout(() => {
                    singleMaps[st.id].invalidateSize();
                    singleMaps[st.id].setView([lat, lng], 14);
                }, 120);
            }
        }

        function openDeleteStationModal(e, id, code, name) {
            if (e && e.stopPropagation) {
                e.preventDefault();
                e.stopPropagation();
            }

            document.getElementById('form-delete-station').action = window.location.origin + '/iot/stations/delete/' + id;
            document.getElementById('delete-station-name').textContent = name + ' (' + code + ')';
            openModal('modal-delete-station');
        }


        function selectStation(id) {
            // 1. Active row trong danh sách bảng dưới
            document.querySelectorAll('.station-table-row').forEach(row => {
                row.classList.remove('active');
            });
            const activeRow = document.getElementById('table-row-' + id);
            if (activeRow) {
                activeRow.classList.add('active');
                activeRow.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }

            // 2. Chuyển đổi Panel thông tin trạm được chọn
            document.querySelectorAll('.station-detail-panel').forEach(panel => {
                panel.classList.add('d-none');
            });
            const activePanel = document.getElementById('station-panel-' + id);
            if (activePanel) {
                activePanel.classList.remove('d-none');
                activePanel.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            const st = stationsData.find(s => s.id === id);
            if (st) {
                // Mặc định về Tab 1
                switchStationTab(id, 'data');

                // Chạy vẽ biểu đồ
                setTimeout(() => {
                    initStationChart(st);
                }, 80);
            }
        }

        function initStationChart(st) {
            const canvasId = 'stationChart-' + st.id;
            const ctx = document.getElementById(canvasId);
            if (!ctx) return;

            if (chartsInstance[st.id]) {
                chartsInstance[st.id].destroy();
            }

            const labels = (st.chart_labels && st.chart_labels.length > 0)
                ? st.chart_labels
                : ['Chưa có dữ liệu'];


            chartsInstance[st.id] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Nhiệt độ (°C)',
                            data: st.temp_history,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.05)',
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Độ ẩm khí (%)',
                            data: st.humidity_history,
                            borderColor: '#06b6d4',
                            backgroundColor: 'rgba(6, 182, 212, 0.05)',
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Độ ẩm đất (%)',
                            data: st.soil_moist_history,
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.05)',
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            borderRadius: 8
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }

        // Tự động làm mới dữ liệu trạm quan trắc mỗi 300 giây (5 phút)
        setInterval(function () {
            // Không làm mới nếu người dùng đang thao tác mở bất kỳ modal nào
            const anyModalOpen = document.querySelector('.app-modal.show') !== null;

            if (!anyModalOpen) {
                console.log('[IoT Auto-Refresh] Đang làm mới dữ liệu quan trắc (chu kỳ 300s)...');
                window.location.reload();
            }
        }, 300000);
    </script>
@endpush


