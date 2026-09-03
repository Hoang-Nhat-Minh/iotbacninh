@extends('layouts.app')

@section('title', 'Khảo sát hàng ngày')

@push('styles')
    <style>
        .survey-nav-tabs .nav-link {
            font-weight: 600;
            color: var(--text-muted);
            border: none;
            border-bottom: 3px solid transparent;
            padding: 0.75rem 1.25rem;
            border-radius: 0;
            transition: all 0.2s ease;
        }

        .survey-nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: transparent;
        }

        .survey-card {
            border-radius: 16px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .iot-kpi-box {
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: transform 0.15s ease;
        }

        .iot-kpi-box:hover {
            transform: translateY(-2px);
            background: #ffffff;
        }

        .object-type-card {
            cursor: pointer;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            transition: all 0.2s ease;
            background: #ffffff;
        }

        .object-type-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        .object-type-card.selected {
            border-color: var(--primary);
            background: rgba(34, 197, 94, 0.05);
        }

        .btn-range-pill {
            border-radius: 50px;
            padding: 0.35rem 0.85rem;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-range-pill:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-range-pill.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.25);
        }

        .image-preview-wrapper {
            position: relative;
            display: inline-block;
            max-width: 220px;
        }

        .image-preview-wrapper img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .image-preview-remove {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: #ffffff;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
@endpush

@section('content')
    <x-page-header title="Khảo sát hàng ngày">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>IoT & Quan trắc</span>
            <span>/</span>
            <span class="text-primary fw-bold">Khảo sát hàng ngày</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <span
                class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                <i class="bi bi-shield-check me-1"></i> Điều tra thực địa sâu bệnh
            </span>
        </x-slot:actions>
    </x-page-header>

    {{-- KHỐI THỐNG KÊ VÀ BIỂU ĐỒ TRỰC QUAN: CHỈ HIỂN THỊ VỚI ADMIN HOẶC MANAGER --}}
    @if (Auth::user()->isAdmin() || Auth::user()->isManager())
        <!-- 4 THẺ KPI TỔNG QUAN -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="survey-card p-3 d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px; font-size: 22px;">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Tổng Bản Ghi Khảo Sát</div>
                        <div class="fs-4 fw-bold text-dark">{{ number_format($totalSurveys) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="survey-card p-3 d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px; font-size: 22px;">
                        <i class="bi bi-calendar2-event"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Khảo Sát Hôm Nay</div>
                        <div class="fs-4 fw-bold text-dark">{{ number_format($todaySurveys) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="survey-card p-3 d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px; font-size: 22px;">
                        <i class="bi bi-bug-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Sâu Đục Cuống Quả</div>
                        <div class="fs-4 fw-bold text-dark">{{ number_format($pestSurveys) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="survey-card p-3 d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px; font-size: 22px;">
                        <i class="bi bi-virus"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Bệnh Hại Cây Trồng</div>
                        <div class="fs-4 fw-bold text-dark">{{ number_format($diseaseSurveys) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CÁC BIỂU ĐỒ TRỰC QUAN CHO ADMIN / MANAGER -->
        <div class="row g-3 mb-4">
            <!-- Biểu đồ 1: Diễn biến khảo sát 14 ngày gần nhất -->
            <div class="col-lg-7">
                <div class="card border-0 bg-white shadow-xs rounded-4 p-3.5 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                            <i class="bi bi-graph-up-arrow text-primary"></i> Diễn Biến Khảo Sát 14 Ngày Gần Nhất
                        </h6>
                        <div class="d-flex gap-3 small text-muted" style="font-size: 11.5px;">
                            <span><i class="bi bi-circle-fill text-warning me-1"></i> Sâu đục cuống</span>
                            <span><i class="bi bi-circle-fill text-danger me-1"></i> Bệnh hại</span>
                        </div>
                    </div>
                    <div style="height: 220px; position: relative;">
                        <canvas id="surveyTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ 2: Phân bố giai đoạn sâu & Mức độ gây hại -->
            <div class="col-lg-5">
                <div class="card border-0 bg-white shadow-xs rounded-4 p-3.5 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                            <i class="bi bi-pie-chart-fill text-success"></i> Phân Bố Giai Đoạn Sâu Phát Triển
                        </h6>
                    </div>
                    <div style="height: 220px; position: relative;">
                        <canvas id="surveyStageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- TABS ĐIỀU HƯỚNG: KHẢO SÁT MỚI & LỊCH SỬ KHẢO SÁT -->
    <div class="survey-card mb-4">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs survey-nav-tabs px-3" id="surveyTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-survey-form-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-survey-form" type="button" role="tab">
                        <i class="bi bi-plus-circle-fill text-primary me-1.5"></i> Thực Hiện Khảo Sát Mới
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-survey-history-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-survey-history" type="button" role="tab">
                        <i class="bi bi-clock-history text-secondary me-1.5"></i> Danh Sách Lịch Sử Khảo Sát
                        <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $surveys->total() }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="surveyTabsContent">
                <!-- ================= TAB 1: FORM KHẢO SÁT MỚI ================= -->
                <div class="tab-pane fade show active" id="tab-survey-form" role="tabpanel">
                    @if ($allowedStations->isEmpty())
                        <div class="alert alert-warning border-0 rounded-4 d-flex align-items-center gap-3 p-3">
                            <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Chưa có trạm quan trắc nào được phân quyền cho tài khoản của bạn
                                </h6>
                                <p class="mb-0 text-muted small">
                                    Bạn chưa sở hữu hoặc quản lý vườn/trạm quan trắc nào trong hệ thống. Vui lòng liên hệ
                                    Quản trị viên để gán quyền trạm quan trắc trước khi thực hiện khảo sát.
                                </p>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('degree-days.surveys.store') }}" method="POST" enctype="multipart/form-data"
                            id="degreeDaysSurveyForm">
                            @csrf

                            <!-- SECTION 1: THÔNG TIN KHẢO SÁT -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-primary text-white rounded-circle p-1"
                                        style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">1</span>
                                    <h6 class="fw-bold text-dark mb-0">Thông Tin Khảo Sát Thực Địa</h6>
                                </div>

                                <div class="row g-3">
                                    <!-- Thời gian khảo sát -->
                                    <div class="col-md-4">
                                        <label for="surveyed_at" class="form-label fw-semibold text-dark small mb-1">
                                            Thời gian khảo sát <span class="text-danger">*</span>
                                        </label>
                                        <input type="datetime-local"
                                            class="form-control form-control-sm rounded-3 @error('surveyed_at') is-invalid @enderror"
                                            id="surveyed_at" name="surveyed_at"
                                            value="{{ old('surveyed_at', now()->format('Y-m-d\TH:i')) }}" required>
                                        @error('surveyed_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Trạm quan trắc -->
                                    <div class="col-md-5">
                                        <label for="monitoring_station_id"
                                            class="form-label fw-semibold text-dark small mb-1">
                                            Trạm / Khu vực / Vườn <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            class="form-select form-select-sm rounded-3 @error('monitoring_station_id') is-invalid @enderror"
                                            id="monitoring_station_id" name="monitoring_station_id" required>
                                            @foreach ($allowedStations as $st)
                                                <option value="{{ $st->id }}"
                                                    {{ old('monitoring_station_id', $selectedStationId) == $st->id ? 'selected' : '' }}>
                                                    [{{ $st->code }}] {{ $st->name }} -
                                                    {{ $st->garden->name ?? 'Vùng trồng' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('monitoring_station_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Người khảo sát -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold text-dark small mb-1">Người khảo sát</label>
                                        <input type="text"
                                            class="form-control form-control-sm rounded-3 bg-light text-muted fw-medium"
                                            value="{{ Auth::user()->name }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4" style="border-color: #f1f5f9;">

                            <!-- SECTION 2: DỮ LIỆU IOT GẦN NHẤT -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary text-white rounded-circle p-1"
                                            style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">2</span>
                                        <h6 class="fw-bold text-dark mb-0">Dữ Liệu Vi Khí Hậu IoT Gần Nhất</h6>
                                    </div>
                                    <span class="badge bg-light text-muted border font-monospace"
                                        id="iot-recorded-time-badge" style="font-size: 11px;">
                                        <i class="bi bi-clock me-1"></i> <span
                                            id="iot-time-text">{{ $initialSnapshot['recorded_at_display'] ?? 'Đang tải...' }}</span>
                                    </span>
                                </div>

                                <div class="p-3 bg-light rounded-4 border">
                                    <div class="row g-2 text-center" id="iot-kpi-container">
                                        <!-- Nhiệt độ -->
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <div class="iot-kpi-box">
                                                <div class="text-muted small" style="font-size: 11.5px;"><i
                                                        class="bi bi-thermometer-half text-danger"></i> Nhiệt độ</div>
                                                <div class="fs-5 fw-bold text-danger" id="iot-temp-val">
                                                    {{ isset($initialSnapshot['values']['temp']) ? $initialSnapshot['values']['temp'] . ' °C' : '--' }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Độ ẩm -->
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <div class="iot-kpi-box">
                                                <div class="text-muted small" style="font-size: 11.5px;"><i
                                                        class="bi bi-droplet-half text-info"></i> Độ ẩm KK</div>
                                                <div class="fs-5 fw-bold text-info" id="iot-hum-val">
                                                    {{ isset($initialSnapshot['values']['humidity']) ? $initialSnapshot['values']['humidity'] . ' %' : '--' }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Lượng mưa -->
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <div class="iot-kpi-box">
                                                <div class="text-muted small" style="font-size: 11.5px;"><i
                                                        class="bi bi-cloud-rain-fill text-primary"></i> Lượng mưa</div>
                                                <div class="fs-5 fw-bold text-primary" id="iot-rain-val">
                                                    {{ isset($initialSnapshot['values']['rain']) ? $initialSnapshot['values']['rain'] . ' mm' : '0.0 mm' }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tốc độ gió -->
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <div class="iot-kpi-box">
                                                <div class="text-muted small" style="font-size: 11.5px;"><i
                                                        class="bi bi-wind text-secondary"></i> Tốc độ gió</div>
                                                <div class="fs-5 fw-bold text-secondary" id="iot-wind-val">
                                                    {{ isset($initialSnapshot['values']['wind']) ? $initialSnapshot['values']['wind'] . ' m/s' : '--' }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Độ ẩm đất -->
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <div class="iot-kpi-box">
                                                <div class="text-muted small" style="font-size: 11.5px;"><i
                                                        class="bi bi-moisture text-success"></i> Độ ẩm đất</div>
                                                <div class="fs-5 fw-bold text-success" id="iot-soil-val">
                                                    {{ isset($initialSnapshot['values']['soil_moist']) ? $initialSnapshot['values']['soil_moist'] . ' %' : '--' }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Ánh sáng -->
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <div class="iot-kpi-box">
                                                <div class="text-muted small" style="font-size: 11.5px;"><i
                                                        class="bi bi-brightness-high text-warning"></i> Ánh sáng</div>
                                                <div class="fs-5 fw-bold text-warning" id="iot-light-val">
                                                    {{ isset($initialSnapshot['values']['light']) ? number_format($initialSnapshot['values']['light']) : '--' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4" style="border-color: #f1f5f9;">

                            <!-- SECTION 3: QUAN SÁT THỰC ĐỊA -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-primary text-white rounded-circle p-1"
                                        style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">3</span>
                                    <h6 class="fw-bold text-dark mb-0">Quan Sát Thực Địa</h6>
                                </div>

                                <!-- Chọn Đối tượng khảo sát -->
                                <label class="form-label fw-semibold text-dark small mb-2">
                                    Chọn đối tượng khảo sát <span class="text-danger">*</span>
                                </label>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="object-type-card selected d-flex align-items-center gap-3"
                                            id="card-type-pest" onclick="selectObjectType('pest')">
                                            <input class="form-check-input mt-0 fs-5" type="radio" name="object_type"
                                                id="type_pest" value="pest" checked>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-0"><i
                                                        class="bi bi-bug-fill text-warning me-1.5"></i> Sâu Đục Cuống Quả
                                                </h6>
                                                <small class="text-muted">Quan sát trứng, sâu non, nhộng hoặc bướm trưởng
                                                    thành</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="object-type-card d-flex align-items-center gap-3"
                                            id="card-type-disease" onclick="selectObjectType('disease')">
                                            <input class="form-check-input mt-0 fs-5" type="radio" name="object_type"
                                                id="type_disease" value="disease">
                                            <div>
                                                <h6 class="fw-bold text-dark mb-0"><i
                                                        class="bi bi-virus text-danger me-1.5"></i> Bệnh Hại Cây Trồng</h6>
                                                <small class="text-muted">Quan sát bệnh trên lá, hoa, quả hoặc cành
                                                    thân</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- KHỐI TRƯỜNG DÀNH RIÊNG CHO SÂU ĐỤC CUỐNG QUẢ -->
                                <div id="fields-pest-group" class="p-3 bg-light rounded-4 border mb-3">
                                    <div class="row g-3">
                                        <!-- Giai đoạn phát triển -->
                                        <div class="col-md-6">
                                            <label for="development_stage"
                                                class="form-label fw-semibold text-dark small mb-1">
                                                Giai đoạn phát triển của sâu <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select form-select-sm rounded-3" id="development_stage"
                                                name="development_stage">
                                                <option value="none">Không phát hiện</option>
                                                <option value="egg">Trứng</option>
                                                <option value="larva" selected>Sâu non</option>
                                                <option value="pupa">Nhộng</option>
                                                <option value="adult">Trưởng thành</option>
                                                <option value="unknown">Không xác định</option>
                                            </select>
                                        </div>

                                        <!-- Mức độ phát sinh chung -->
                                        <div class="col-md-6">
                                            <label for="severity_pest"
                                                class="form-label fw-semibold text-dark small mb-1">
                                                Mức độ phát sinh <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select form-select-sm rounded-3" id="severity_pest"
                                                name="severity">
                                                <option value="none">Không có</option>
                                                <option value="low">Ít</option>
                                                <option value="medium" selected>Trung bình</option>
                                                <option value="high">Nhiều</option>
                                                <option value="outbreak">Bùng phát</option>
                                            </select>
                                        </div>

                                        <!-- Số lượng quan sát được -->
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark small mb-1.5">
                                                Số lượng sâu quan sát được <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex flex-wrap gap-2" id="quantity-pill-group">
                                                <input type="hidden" name="quantity_range" id="quantity_range"
                                                    value="6_20">
                                                <button type="button" class="btn-range-pill" data-val="unknown"
                                                    onclick="selectQuantity('unknown', this)">Không xác định</button>
                                                <button type="button" class="btn-range-pill" data-val="1_5"
                                                    onclick="selectQuantity('1_5', this)">1 – 5 con</button>
                                                <button type="button" class="btn-range-pill active" data-val="6_20"
                                                    onclick="selectQuantity('6_20', this)">6 – 20 con</button>
                                                <button type="button" class="btn-range-pill" data-val="21_50"
                                                    onclick="selectQuantity('21_50', this)">21 – 50 con</button>
                                                <button type="button" class="btn-range-pill" data-val="gt_50"
                                                    onclick="selectQuantity('gt_50', this)">&gt; 50 con</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- KHỐI TRƯỜNG DÀNH RIÊNG CHO BỆNH HẠI (ẨN MẶC ĐỊNH) -->
                                <div id="fields-disease-group" class="p-3 bg-light rounded-4 border mb-3"
                                    style="display: none;">
                                    <div class="row g-3">
                                        <!-- Bộ phận bị bệnh -->
                                        <div class="col-md-6">
                                            <label for="affected_part"
                                                class="form-label fw-semibold text-dark small mb-1">
                                                Bộ phận cây trồng bị bệnh <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select form-select-sm rounded-3" id="affected_part"
                                                name="affected_part" disabled>
                                                <option value="leaf" selected>Lá</option>
                                                <option value="flower">Hoa</option>
                                                <option value="fruit">Quả</option>
                                                <option value="branch">Cành / Thân</option>
                                                <option value="other">Khác</option>
                                            </select>
                                        </div>

                                        <!-- Mức độ phát sinh -->
                                        <div class="col-md-6">
                                            <label for="severity_disease"
                                                class="form-label fw-semibold text-dark small mb-1">
                                                Mức độ gây hại <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select form-select-sm rounded-3" id="severity_disease"
                                                name="severity" disabled>
                                                <option value="none">Không có</option>
                                                <option value="low">Ít</option>
                                                <option value="medium" selected>Trung bình</option>
                                                <option value="high">Nhiều</option>
                                                <option value="outbreak">Bùng phát</option>
                                            </select>
                                        </div>

                                        <!-- Tỷ lệ số cây nhiễm -->
                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-dark small mb-1.5">
                                                Tỷ lệ số cây bị nhiễm bệnh <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex flex-wrap gap-2" id="infection-pill-group">
                                                <input type="hidden" name="infection_rate_range"
                                                    id="infection_rate_range" value="10_25" disabled>
                                                <button type="button" class="btn-range-pill" data-val="lt_5"
                                                    onclick="selectInfection('lt_5', this)">&lt; 5%</button>
                                                <button type="button" class="btn-range-pill" data-val="5_10"
                                                    onclick="selectInfection('5_10', this)">5 – 10%</button>
                                                <button type="button" class="btn-range-pill active" data-val="10_25"
                                                    onclick="selectInfection('10_25', this)">10 – 25%</button>
                                                <button type="button" class="btn-range-pill" data-val="25_50"
                                                    onclick="selectInfection('25_50', this)">25 – 50%</button>
                                                <button type="button" class="btn-range-pill" data-val="gt_50"
                                                    onclick="selectInfection('gt_50', this)">&gt; 50%</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4" style="border-color: #f1f5f9;">

                            <!-- SECTION 4: HÌNH ẢNH & GHI CHÚ -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-primary text-white rounded-circle p-1"
                                        style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">4</span>
                                    <h6 class="fw-bold text-dark mb-0">Hình Ảnh & Ghi Chú Thực Địa</h6>
                                </div>

                                <div class="row g-3">
                                    <!-- Ảnh chụp thực địa -->
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold text-dark small mb-1">Ảnh chụp thực
                                            địa</label>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex gap-2">
                                                <!-- Chụp từ Camera trên mobile -->
                                                <label
                                                    class="btn btn-outline-secondary btn-sm rounded-3 d-flex align-items-center gap-1.5 cursor-pointer">
                                                    <i class="bi bi-camera-fill text-primary"></i> Chụp ảnh
                                                    <input type="file" name="image" id="survey-camera-input"
                                                        accept="image/*" capture="environment" class="d-none"
                                                        onchange="previewSurveyImage(this)">
                                                </label>
                                                <!-- Chọn từ file máy tính/thư viện -->
                                                <label
                                                    class="btn btn-outline-secondary btn-sm rounded-3 d-flex align-items-center gap-1.5 cursor-pointer">
                                                    <i class="bi bi-image text-info"></i> Chọn từ máy
                                                    <input type="file" name="image" id="survey-file-input"
                                                        accept="image/*" class="d-none"
                                                        onchange="previewSurveyImage(this)">
                                                </label>
                                            </div>

                                            <!-- Thumbnail preview container -->
                                            <div id="survey-image-preview" style="display: none;">
                                                <div class="image-preview-wrapper mt-2">
                                                    <img id="survey-preview-img" src="" alt="Ảnh khảo sát">
                                                    <button type="button" class="image-preview-remove"
                                                        onclick="removeSurveyImage()" title="Xóa ảnh">&times;</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ghi chú bổ sung -->
                                    <div class="col-md-7">
                                        <label for="notes" class="form-label fw-semibold text-dark small mb-1">Ghi chú
                                            quan sát</label>
                                        <textarea class="form-control form-control-sm rounded-3" id="notes" name="notes" rows="3"
                                            placeholder="Nhập ghi chú quan sát cụ thể tại thời điểm kiểm tra" maxlength="1000"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- NÚT LƯU FORM -->
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                <button type="reset" class="btn btn-light btn-sm px-3 rounded-3"
                                    onclick="resetSurveyForm()">Khôi phục</button>
                                <button type="submit"
                                    class="btn btn-primary btn-sm px-4 py-2 rounded-3 fw-bold shadow-sm"
                                    id="btn-submit-survey">
                                    <i class="bi bi-check2-circle me-1"></i> Lưu Khảo Sát Thực Địa
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                <!-- ================= TAB 2: LỊCH SỬ KHẢO SÁT ================= -->
                <div class="tab-pane fade" id="tab-survey-history" role="tabpanel">
                    <!-- BỘ LỌC LỊCH SỬ -->
                    <form method="GET" action="{{ route('degree-days.surveys.index') }}"
                        class="row g-2 mb-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Trạm quan trắc:</label>
                            <select name="station_id" class="form-select form-select-sm rounded-3">
                                <option value="">-- Tất cả trạm --</option>
                                @foreach ($allowedStations as $st)
                                    <option value="{{ $st->id }}"
                                        {{ request('station_id') == $st->id ? 'selected' : '' }}>
                                        [{{ $st->code }}] {{ $st->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Đối tượng:</label>
                            <select name="object_type" class="form-select form-select-sm rounded-3">
                                <option value="">-- Tất cả --</option>
                                <option value="pest" {{ request('object_type') === 'pest' ? 'selected' : '' }}>Sâu đục
                                    cuống quả</option>
                                <option value="disease" {{ request('object_type') === 'disease' ? 'selected' : '' }}>Bệnh
                                    hại</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Mức độ:</label>
                            <select name="severity" class="form-select form-select-sm rounded-3">
                                <option value="">-- Tất cả --</option>
                                <option value="none" {{ request('severity') === 'none' ? 'selected' : '' }}>Không có
                                </option>
                                <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Ít</option>
                                <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Trung bình
                                </option>
                                <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>Nhiều
                                </option>
                                <option value="outbreak" {{ request('severity') === 'outbreak' ? 'selected' : '' }}>Bùng
                                    phát</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Ngày khảo sát:</label>
                            <input type="date" name="date" class="form-control form-control-sm rounded-3"
                                value="{{ request('date') }}">
                        </div>

                        <div class="col-md-2 d-flex gap-1.5">
                            <button type="submit" class="btn btn-primary btn-sm rounded-3 w-100"><i
                                    class="bi bi-funnel"></i> Lọc</button>
                            <a href="{{ route('degree-days.surveys.index') }}" class="btn btn-light btn-sm rounded-3"
                                title="Xóa lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
                        </div>
                    </form>

                    <!-- BẢNG LỊCH SỬ KHẢO SÁT -->
                    <div class="table-responsive">
                        <table class="custom-table w-100 mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>Thời Gian</th>
                                    <th>Trạm / Vườn</th>
                                    <th>Đối Tượng</th>
                                    <th>Giai Đoạn / Bộ Phận</th>
                                    <th>Số Lượng / Tỷ Lệ</th>
                                    <th>Mức Độ</th>
                                    <th>Snapshot Vi Khí Hậu IoT</th>
                                    <th style="width: 100px; text-align: center;">Chi Tiết</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($surveys as $idx => $s)
                                    <tr>
                                        <td class="text-secondary fw-semibold">{{ $surveys->firstItem() + $idx }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $s->surveyed_at->format('d/m/Y') }}</div>
                                            <div class="text-muted small">{{ $s->surveyed_at->format('H:i') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                {{ $s->station->name ?? ($s->station_name ?? '--') }}</div>
                                            <div class="text-muted small">
                                                [{{ $s->station->code ?? ($s->station_code ?? '--') }}]
                                                {{ $s->station->garden->name ?? ($s->garden_name ?? '') }}</div>
                                        </td>
                                        <td>
                                            @if ($s->object_type === 'pest')
                                                <span
                                                    class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                                    <i class="bi bi-bug-fill me-1"></i> Sâu đục cuống
                                                </span>
                                            @else
                                                <span
                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                    <i class="bi bi-virus me-1"></i> Bệnh hại
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($s->object_type === 'pest')
                                                <span class="fw-medium text-dark">{{ $s->development_stage_label }}</span>
                                            @else
                                                <span class="fw-medium text-dark">{{ $s->affected_part_label }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($s->object_type === 'pest')
                                                <span
                                                    class="text-secondary font-monospace">{{ $s->quantity_range_label }}</span>
                                            @else
                                                <span
                                                    class="text-secondary font-monospace">{{ $s->infection_rate_label }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $s->severity_badge_class }}">
                                                {{ $s->severity_label }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($s->iot_temperature !== null)
                                                <div class="small">
                                                    <span class="text-danger fw-bold"><i
                                                            class="bi bi-thermometer-half"></i>
                                                        {{ $s->iot_temperature }}°C</span> |
                                                    <span class="text-info fw-medium"><i class="bi bi-droplet-half"></i>
                                                        {{ $s->iot_humidity }}%</span>
                                                </div>
                                                <div class="text-muted" style="font-size: 11px;">
                                                    Gió: {{ $s->iot_wind_speed ?? 0 }} m/s | Mưa:
                                                    {{ $s->iot_rainfall ?? 0 }} mm
                                                </div>
                                            @else
                                                <span class="text-muted small">-- Chưa có snapshot --</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm px-2.5 py-1 rounded-3"
                                                onclick="openSurveyDetailModal({{ $s->id }})">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                                            Chưa có bản ghi khảo sát nào phù hợp với điều kiện lọc.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- PHÂN TRANG -->
                    <div class="mt-3">
                        {{ $surveys->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CHI TIẾT BẢN GHI KHẢO SÁT & SNAPSHOT IOT -->
    <div class="app-modal" id="modal-survey-detail">
        <div class="modal-dialog" style="max-width: 720px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text-fill text-primary me-2"></i> Chi Tiết Bản Ghi
                    Khảo Sát Thực Địa</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <div class="modal-body py-3">
                <!-- Thông tin người khảo sát & thời gian -->
                <div
                    class="p-3 bg-light rounded-4 border mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="text-muted small">Trạm quan trắc & Vùng trồng:</div>
                        <div class="fw-bold text-dark fs-6" id="m-station-name">--</div>
                        <div class="text-secondary small"><i class="bi bi-person me-1 text-primary"></i> Người khảo sát:
                            <span id="m-surveyor" class="fw-semibold text-dark">--</span></div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Thời điểm khảo sát:</div>
                        <div class="fw-bold text-dark" id="m-surveyed-at">--</div>
                    </div>
                </div>

                <!-- Thông tin quan sát thực địa -->
                <div class="card border mb-3 rounded-3 p-3">
                    <h6 class="fw-bold text-dark mb-2.5"><i class="bi bi-binoculars-fill text-warning me-1.5"></i> Kết Quả
                        Quan Sát Thực Địa:</h6>
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <span class="text-muted">Đối tượng:</span> <strong id="m-object-type"
                                class="text-dark">--</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted">Mức độ phát sinh:</span> <span id="m-severity"
                                class="badge">--</span>
                        </div>
                        <div class="col-sm-6" id="m-pest-row">
                            <span class="text-muted">Giai đoạn & Số lượng:</span> <strong id="m-pest-info"
                                class="text-dark">--</strong>
                        </div>
                        <div class="col-sm-6" id="m-disease-row">
                            <span class="text-muted">Bộ phận & Tỷ lệ:</span> <strong id="m-disease-info"
                                class="text-dark">--</strong>
                        </div>
                    </div>

                    <!-- Ghi chú nếu có -->
                    <div class="mt-2 pt-2 border-top" id="m-notes-box">
                        <div class="text-muted small">Ghi chú bổ sung:</div>
                        <div class="text-dark small fst-italic" id="m-notes-content">--</div>
                    </div>

                    <!-- Ảnh nếu có -->
                    <div class="mt-2 pt-2 border-top" id="m-image-box" style="display: none;">
                        <div class="text-muted small mb-1.5">Ảnh chụp thực địa:</div>
                        <a id="m-image-link" href="#" target="_blank">
                            <img id="m-image-img" src="" alt="Ảnh thực địa" class="rounded-3 border"
                                style="max-height: 180px; max-width: 100%; object-fit: contain;">
                        </a>
                    </div>
                </div>

                <!-- Snapshot IoT gắn liền -->
                <div class="card border rounded-3 p-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-broadcast-pin text-primary me-1.5"></i>
                            Snapshot Vi Khí Hậu IoT:</h6>
                        <span class="badge bg-white text-muted border font-monospace" id="m-iot-time"
                            style="font-size: 11px;">--</span>
                    </div>

                    <div class="row g-2 text-center">
                        <div class="col-4 col-sm-2">
                            <div class="bg-white p-2 rounded-2 border">
                                <small class="text-muted d-block" style="font-size: 10.5px;">Nhiệt độ</small>
                                <span class="fw-bold text-danger" id="m-iot-temp">--</span>
                            </div>
                        </div>
                        <div class="col-4 col-sm-2">
                            <div class="bg-white p-2 rounded-2 border">
                                <small class="text-muted d-block" style="font-size: 10.5px;">Độ ẩm</small>
                                <span class="fw-bold text-info" id="m-iot-hum">--</span>
                            </div>
                        </div>
                        <div class="col-4 col-sm-2">
                            <div class="bg-white p-2 rounded-2 border">
                                <small class="text-muted d-block" style="font-size: 10.5px;">Lượng mưa</small>
                                <span class="fw-bold text-primary" id="m-iot-rain">--</span>
                            </div>
                        </div>
                        <div class="col-4 col-sm-2">
                            <div class="bg-white p-2 rounded-2 border">
                                <small class="text-muted d-block" style="font-size: 10.5px;">Tốc độ gió</small>
                                <span class="fw-bold text-secondary" id="m-iot-wind">--</span>
                            </div>
                        </div>
                        <div class="col-4 col-sm-2">
                            <div class="bg-white p-2 rounded-2 border">
                                <small class="text-muted d-block" style="font-size: 10.5px;">Độ ẩm đất</small>
                                <span class="fw-bold text-success" id="m-iot-soil">--</span>
                            </div>
                        </div>
                        <div class="col-4 col-sm-2">
                            <div class="bg-white p-2 rounded-2 border">
                                <small class="text-muted d-block" style="font-size: 10.5px;">Ánh sáng</small>
                                <span class="fw-bold text-warning" id="m-iot-light">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm btn-modal-close">Đóng Cửa Sổ</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 0. Khởi tạo Biểu đồ Thống kê Trực quan (Chỉ dành cho Admin / Manager)
        @if (!empty($chartData))
            document.addEventListener('DOMContentLoaded', function() {
                const chartData = @json($chartData);

                // Biểu đồ 1: Diễn biến 14 ngày (Line/Bar)
                const trendCtx = document.getElementById('surveyTrendChart');
                if (trendCtx) {
                    new Chart(trendCtx, {
                        type: 'bar',
                        data: {
                            labels: chartData.trend.labels,
                            datasets: [{
                                    label: 'Sâu đục cuống',
                                    data: chartData.trend.pest,
                                    backgroundColor: '#f59e0b',
                                    borderRadius: 6,
                                    barPercentage: 0.6
                                },
                                {
                                    label: 'Bệnh hại',
                                    data: chartData.trend.disease,
                                    backgroundColor: '#ef4444',
                                    borderRadius: 6,
                                    barPercentage: 0.6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    padding: 8,
                                    borderRadius: 6
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            size: 11
                                        }
                                    },
                                    grid: {
                                        color: '#f1f5f9'
                                    }
                                }
                            }
                        }
                    });
                }

                // Biểu đồ 2: Phân bố giai đoạn phát triển sâu (Doughnut)
                const stageCtx = document.getElementById('surveyStageChart');
                if (stageCtx) {
                    new Chart(stageCtx, {
                        type: 'doughnut',
                        data: {
                            labels: chartData.stages.labels,
                            datasets: [{
                                data: chartData.stages.data,
                                backgroundColor: [
                                    '#38bdf8', // Trứng
                                    '#f59e0b', // Sâu non
                                    '#8b5cf6', // Nhộng
                                    '#ef4444', // Trưởng thành
                                    '#94a3b8' // Không phát hiện
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        boxWidth: 12,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    padding: 8,
                                    borderRadius: 6
                                }
                            },
                            cutout: '65%'
                        }
                    });
                }
            });
        @endif

        // 1. Chuyển đổi đối tượng khảo sát (Sâu đục cuống quả / Bệnh)
        function selectObjectType(type) {
            const cardPest = document.getElementById('card-type-pest');
            const cardDisease = document.getElementById('card-type-disease');
            const fieldsPest = document.getElementById('fields-pest-group');
            const fieldsDisease = document.getElementById('fields-disease-group');

            const devStage = document.getElementById('development_stage');
            const severityPest = document.getElementById('severity_pest');
            const qtyRange = document.getElementById('quantity_range');

            const affectedPart = document.getElementById('affected_part');
            const severityDisease = document.getElementById('severity_disease');
            const infectionRate = document.getElementById('infection_rate_range');

            if (type === 'pest') {
                document.getElementById('type_pest').checked = true;
                cardPest.classList.add('selected');
                cardDisease.classList.remove('selected');
                fieldsPest.style.display = 'block';
                fieldsDisease.style.display = 'none';

                devStage.disabled = false;
                severityPest.disabled = false;
                qtyRange.disabled = false;

                affectedPart.disabled = true;
                severityDisease.disabled = true;
                infectionRate.disabled = true;
            } else {
                document.getElementById('type_disease').checked = true;
                cardDisease.classList.add('selected');
                cardPest.classList.remove('selected');
                fieldsDisease.style.display = 'block';
                fieldsPest.style.display = 'none';

                devStage.disabled = true;
                severityPest.disabled = true;
                qtyRange.disabled = true;

                affectedPart.disabled = false;
                severityDisease.disabled = false;
                infectionRate.disabled = false;
            }
        }

        // 2. Chọn khoảng số lượng sâu
        function selectQuantity(val, btn) {
            document.getElementById('quantity_range').value = val;
            document.querySelectorAll('#quantity-pill-group .btn-range-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        // 3. Chọn khoảng tỷ lệ bệnh
        function selectInfection(val, btn) {
            document.getElementById('infection_rate_range').value = val;
            document.querySelectorAll('#infection-pill-group .btn-range-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        // 4. Xem trước ảnh thực địa
        function previewSurveyImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('survey-preview-img').src = e.target.result;
                    document.getElementById('survey-image-preview').style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // 5. Xóa ảnh thực địa
        function removeSurveyImage() {
            document.getElementById('survey-camera-input').value = '';
            document.getElementById('survey-file-input').value = '';
            document.getElementById('survey-preview-img').src = '';
            document.getElementById('survey-image-preview').style.display = 'none';
        }

        // 6. Reset form
        function resetSurveyForm() {
            removeSurveyImage();
            selectObjectType('pest');
            setTimeout(() => {
                fetchStationSnapshot();
            }, 100);
        }

        // 7. Tự động fetch snapshot IoT khi đổi trạm hoặc thời gian khảo sát
        function fetchStationSnapshot() {
            const stationSelect = document.getElementById('monitoring_station_id');
            const surveyedAtInput = document.getElementById('surveyed_at');

            if (!stationSelect || !stationSelect.value) return;

            const stationId = stationSelect.value;
            const surveyedAt = surveyedAtInput ? surveyedAtInput.value : '';

            const timeBadge = document.getElementById('iot-time-text');
            if (timeBadge) timeBadge.textContent = 'Đang cập nhật...';

            fetch(
                    `{{ route('degree-days.surveys.snapshot') }}?station_id=${stationId}&surveyed_at=${encodeURIComponent(surveyedAt)}`)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const d = res.data;
                        const vals = d.values || {};

                        document.getElementById('iot-time-text').textContent = d.recorded_at_display ||
                            'Chưa có bản ghi';
                        document.getElementById('iot-temp-val').textContent = vals.temp !== undefined ? (vals.temp +
                            ' °C') : '--';
                        document.getElementById('iot-hum-val').textContent = vals.humidity !== undefined ? (vals
                            .humidity + ' %') : '--';
                        document.getElementById('iot-rain-val').textContent = vals.rain !== undefined ? (vals.rain +
                            ' mm') : '0.0 mm';
                        document.getElementById('iot-wind-val').textContent = vals.wind !== undefined ? (vals.wind +
                            ' m/s') : '--';
                        document.getElementById('iot-soil-val').textContent = vals.soil_moist !== undefined ? (vals
                            .soil_moist + ' %') : '--';
                        document.getElementById('iot-light-val').textContent = vals.light !== undefined ? Number(vals
                            .light).toLocaleString() : '--';
                    }
                })
                .catch(err => {
                    console.error('Lỗi khi nạp snapshot IoT:', err);
                    if (timeBadge) timeBadge.textContent = 'Không có kết nối trạm';
                });
        }

        // Lắng nghe sự kiện thay đổi trạm hoặc thời gian khảo sát
        document.addEventListener('DOMContentLoaded', function() {
            const stationSelect = document.getElementById('monitoring_station_id');
            const surveyedAtInput = document.getElementById('surveyed_at');

            if (stationSelect) {
                stationSelect.addEventListener('change', fetchStationSnapshot);
            }
            if (surveyedAtInput) {
                surveyedAtInput.addEventListener('change', fetchStationSnapshot);
            }

            // Mở tab lịch sử nếu URL có query param lọc
            @if (request()->hasAny(['station_id', 'object_type', 'severity', 'date', 'page']))
                const tabHistoryBtn = document.getElementById('tab-survey-history-btn');
                if (tabHistoryBtn) {
                    new bootstrap.Tab(tabHistoryBtn).show();
                }
            @endif
        });

        // 8. Xem chi tiết bản ghi khảo sát (Modal)
        function openSurveyDetailModal(surveyId) {
            fetch(`{{ url('/degree-days/surveys') }}/${surveyId}`)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.survey) {
                        const s = res.survey;
                        document.getElementById('m-station-name').textContent =
                            `[${s.station_code}] ${s.station_name} - ${s.garden_name}`;
                        document.getElementById('m-surveyor').textContent = s.surveyor_name;
                        document.getElementById('m-surveyed-at').textContent = s.surveyed_at;

                        document.getElementById('m-object-type').textContent = s.object_type_label;

                        const sevBadge = document.getElementById('m-severity');
                        sevBadge.textContent = s.severity_label;
                        sevBadge.className = 'badge ' + s.severity_badge_class;

                        const pestRow = document.getElementById('m-pest-row');
                        const diseaseRow = document.getElementById('m-disease-row');

                        if (s.object_type === 'pest') {
                            pestRow.style.display = 'block';
                            diseaseRow.style.display = 'none';
                            document.getElementById('m-pest-info').textContent =
                                `${s.development_stage_label} - ${s.quantity_range_label}`;
                        } else {
                            pestRow.style.display = 'none';
                            diseaseRow.style.display = 'block';
                            document.getElementById('m-disease-info').textContent =
                                `${s.affected_part_label} - Tỷ lệ: ${s.infection_rate_label}`;
                        }

                        // Ghi chú
                        const notesBox = document.getElementById('m-notes-box');
                        if (s.notes) {
                            notesBox.style.display = 'block';
                            document.getElementById('m-notes-content').textContent = s.notes;
                        } else {
                            notesBox.style.display = 'none';
                        }

                        // Ảnh
                        const imgBox = document.getElementById('m-image-box');
                        if (s.image_url) {
                            imgBox.style.display = 'block';
                            document.getElementById('m-image-img').src = s.image_url;
                            document.getElementById('m-image-link').href = s.image_url;
                        } else {
                            imgBox.style.display = 'none';
                        }

                        // IoT Snapshot
                        const iot = s.iot || {};
                        document.getElementById('m-iot-time').textContent = iot.recorded_at ? ('Gói tin lúc ' + iot
                            .recorded_at) : 'Chưa có dữ liệu trạm';
                        document.getElementById('m-iot-temp').textContent = iot.temperature;
                        document.getElementById('m-iot-hum').textContent = iot.humidity;
                        document.getElementById('m-iot-rain').textContent = iot.rainfall;
                        document.getElementById('m-iot-wind').textContent = iot.wind;
                        document.getElementById('m-iot-soil').textContent = iot.soil_moist;
                        document.getElementById('m-iot-light').textContent = iot.light;

                        openModal('modal-survey-detail');
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (typeof showToast === 'function') {
                        showToast('Không thể nạp chi tiết bản ghi khảo sát.', 'danger');
                    }
                });
        }
    </script>
@endpush
