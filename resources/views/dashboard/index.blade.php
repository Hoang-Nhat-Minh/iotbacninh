@extends('layouts.app')

@section('title', 'Bảng Điều Khiển Nông Nghiệp Thông Minh - Bắc Ninh')

@push('styles')
    <style>
        /* Dashboard Overview Header */
        .dashboard-overview-header {
            margin-bottom: 1.5rem;
        }

        .dashboard-hero-title h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
            letter-spacing: -0.5px;
        }

        .dashboard-hero-title p {
            color: #64748b;
            margin-bottom: 0;
            font-size: 14px;
        }

        /* Master Overview Spotlight Card */
        .master-overview-card {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(187, 247, 208, 0.7);
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(22, 101, 52, 0.05);
            overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .master-overview-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -6px rgba(22, 101, 52, 0.1);
            border-color: rgba(34, 197, 94, 0.4);
        }

        /* Plant Spotlight Box */
        .plant-spotlight-box {
            position: relative;
            height: 310px;
            background: radial-gradient(circle at center, rgba(34, 197, 94, 0.15) 0%, rgba(240, 253, 244, 0.6) 45%, rgba(255, 255, 255, 0.95) 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            cursor: pointer;
            user-select: none;
        }

        /* Waterdrop Ripple Animation */
        .water-ripple {
            position: absolute;
            width: 100px;
            height: 100px;
            border: 2px solid rgba(56, 189, 248, 0.6);
            border-radius: 50%;
            animation: rippleWave 3s infinite cubic-bezier(0.25, 1, 0.5, 1);
            pointer-events: none;
        }

        .water-ripple.ripple-2 {
            animation-delay: 1.5s;
        }

        @keyframes rippleWave {
            0% {
                transform: scale(0.6);
                opacity: 0.9;
            }

            100% {
                transform: scale(2.8);
                opacity: 0;
            }
        }

        /* Floating Droplets Effect */
        .floating-drop {
            position: absolute;
            color: #38bdf8;
            font-size: 15px;
            animation: dropFloat 3.2s infinite ease-in-out;
            pointer-events: none;
            filter: drop-shadow(0 2px 4px rgba(56, 189, 248, 0.4));
            z-index: 2;
        }

        .drop-1 {
            top: 22%;
            left: 22%;
            animation-delay: 0s;
        }

        .drop-2 {
            top: 26%;
            right: 22%;
            animation-delay: 1.6s;
            font-size: 13px;
        }

        .drop-3 {
            bottom: 22%;
            left: 26%;
            animation-delay: 0.8s;
            font-size: 12px;
        }

        @keyframes dropFloat {

            0%,
            100% {
                transform: translateY(0) scale(0.9);
                opacity: 0.5;
            }

            50% {
                transform: translateY(-10px) scale(1.2);
                opacity: 1;
            }
        }

        /* Floating Dust Particles */
        .dust-particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(245, 158, 11, 0.8);
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(245, 158, 11, 0.9);
            pointer-events: none;
            animation: dustDrift 4.5s infinite linear;
            z-index: 2;
        }

        .dust-1 {
            top: 32%;
            left: 16%;
            animation-duration: 4.8s;
        }

        .dust-2 {
            top: 62%;
            right: 18%;
            animation-duration: 4.2s;
            animation-delay: 1.2s;
        }

        .dust-3 {
            top: 18%;
            right: 35%;
            animation-duration: 5.5s;
            animation-delay: 2.2s;
            width: 4px;
            height: 4px;
        }

        .dust-4 {
            bottom: 18%;
            left: 38%;
            animation-duration: 4s;
            animation-delay: 0.6s;
            width: 5px;
            height: 5px;
        }

        @keyframes dustDrift {
            0% {
                transform: translateY(0) translateX(0) scale(0.8);
                opacity: 0.2;
            }

            50% {
                transform: translateY(-18px) translateX(12px) scale(1.25);
                opacity: 0.95;
            }

            100% {
                transform: translateY(-36px) translateX(-6px) scale(0.5);
                opacity: 0;
            }
        }

        /* Plant Hero Image */
        .plant-hero-main {
            max-width: 270px;
            object-fit: contain;
            filter: drop-shadow(0 14px 20px rgba(22, 101, 52, 0.22));
            transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
            z-index: 3;
            user-select: none;
        }

        .plant-spotlight-box:hover .plant-hero-main {
            transform: scale(1.12) translateY(-6px);
            filter: drop-shadow(0 20px 28px rgba(22, 101, 52, 0.35));
        }

        /* Telemetry Badges - Revealed on hover */
        .plant-spotlight-box .floating-sensor-tag {
            position: absolute;
            z-index: 5;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 24px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            opacity: 0;
            visibility: hidden;
            transform: scale(0.65);
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
            white-space: nowrap;
        }

        .plant-spotlight-box:hover .floating-sensor-tag {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .plant-spotlight-box:hover .tag-top-left {
            top: 20px;
            left: 20px;
            transform: scale(1);
        }

        .plant-spotlight-box:hover .tag-top-right {
            top: 20px;
            right: 20px;
            transform: scale(1);
        }

        .plant-spotlight-box:hover .tag-bottom-left {
            bottom: 20px;
            left: 20px;
            transform: scale(1);
        }

        .plant-spotlight-box:hover .tag-bottom-right {
            bottom: 20px;
            right: 20px;
            transform: scale(1);
        }

        /* Hover Invitation Hint */
        .plant-hover-hint {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.75);
            color: #ffffff;
            font-size: 11px;
            font-weight: 500;
            padding: 4px 14px;
            border-radius: 14px;
            backdrop-filter: blur(6px);
            transition: opacity 0.25s ease;
            z-index: 4;
            pointer-events: none;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .plant-spotlight-box:hover .plant-hover-hint {
            opacity: 0;
        }

        /* Chart container in master card (Clean Eco-Green Modern Theme) */
        .chart-box-light {
            background: linear-gradient(180deg, #f8fcf9 0%, #f0fdf4 100%);
            border: 1px solid rgba(187, 247, 208, 0.7);
            border-radius: 16px;
            padding: 18px 20px;
            color: #0f172a;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.8);
        }

        .micro-kpi-pill {
            background: #ffffff;
            border: 1px solid rgba(187, 247, 208, 0.7);
            border-radius: 10px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            box-shadow: 0 1px 3px rgba(22, 101, 52, 0.03);
            transition: all 0.2s ease;
        }

        .micro-kpi-pill:hover {
            border-color: var(--primary-light);
            box-shadow: 0 2px 8px rgba(22, 163, 74, 0.15);
            transform: translateY(-1px);
        }

        .dash-card-alert {
            position: relative;
            z-index: 1;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(249, 115, 22, 0.06);
            overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .dash-card-alert:hover {
            transform: translateY(-4px);
        }

        .dash-card-alert .alert-header-box {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .alert-item-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .alert-item-box:hover {
            transform: translateX(4px);
            background: #ffffff;
            border-color: #fdba74;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.08);
        }

        .alert-item-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .task-item-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 11px 14px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .task-item-card:hover {
            border-color: var(--primary-light);
            box-shadow: var(--shadow-sm);
            transform: translateX(3px);
        }

        .status-table th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            font-weight: 700;
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
        }

        .status-table td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
        }

        .health-score-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 12px;
        }
    </style>
@endpush

@section('content')
    <div class="master-overview-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2">
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-flower1 text-success fs-5"></i> Giám Sát Cây Trồng & Vi Khí Hậu
            </h5>
            <div class="d-flex gap-2">
                <a href="{{ url('/gardens/map') }}" class="btn btn-sm btn-primary py-1 px-3">
                    <i class="bi bi-geo-alt"></i> Bản đồ
                </a>
                <a href="{{ url('/iot/stations') }}" class="btn btn-sm btn-outline-secondary py-1 px-3">
                    <i class="bi bi-broadcast"></i> Trạm IoT
                </a>
            </div>
        </div>

        <div class="row g-4 align-items-stretch">
            <!-- 2.1 Cột Trái: Trực Quan Cây Trồng 3D + Hiệu Ứng Nước & Bụi Phấn + Hover Thông Số -->
            <div class="col-xl-5 col-lg-5">
                <div class="plant-spotlight-box">
                    <!-- Hiệu ứng gợn nước (Waterdrop Ripple) -->
                    <div class="water-ripple"></div>
                    <div class="water-ripple ripple-2"></div>

                    <!-- Hiệu ứng giọt sương bay (Floating Droplets) -->
                    <i class="bi bi-droplet-fill floating-drop drop-1"></i>
                    <i class="bi bi-droplet-half floating-drop drop-2"></i>
                    <i class="bi bi-droplet-fill floating-drop drop-3"></i>

                    <!-- Hiệu ứng hạt bụi phấn lấp lánh (Dust Particles) -->
                    <div class="dust-particle dust-1"></div>
                    <div class="dust-particle dust-2"></div>
                    <div class="dust-particle dust-3"></div>
                    <div class="dust-particle dust-4"></div>

                    <!-- 4 Nhãn Thông Số Sinh Thái (Chỉ hiện ra khi Hover vào Cây) -->
                    <div class="floating-sensor-tag tag-top-left">
                        <i class="bi bi-droplet-half text-info fs-6"></i>
                        <span>Độ ẩm: <strong class="text-primary">{{ $avgHumidity }}%</strong></span>
                    </div>

                    <div class="floating-sensor-tag tag-top-right">
                        <i class="bi bi-thermometer-half text-danger fs-6"></i>
                        <span>Nhiệt độ: <strong class="text-danger">{{ $avgTemp }}°C</strong></span>
                    </div>

                    <div class="floating-sensor-tag tag-bottom-left">
                        <i class="bi bi-moisture text-warning fs-6"></i>
                        <span>pH Đất: <strong class="text-dark">{{ $avgPh }}</strong></span>
                    </div>

                    <div class="floating-sensor-tag tag-bottom-right">
                        <i class="bi bi-shield-fill-check text-success fs-6"></i>
                        <span>Sức khỏe: <strong class="text-success">96%</strong></span>
                    </div>

                    <!-- Ảnh Cây Trồng Không Nền (Phóng to nhẹ khi Hover) -->
                    <img src="{{ asset('assets/image/1.png') }}" alt="Cây trồng" class="plant-hero-main">
                </div>
            </div>

            <!-- 2.2 Cột Phải: Biểu Đồ Vi Khí Hậu & Tích Lũy Nhiệt GDD -->
            <div class="col-xl-7 col-lg-7">
                <div class="chart-box-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold text-dark" style="font-size: 13.5px;">
                            <i class="bi bi-graph-up-arrow text-success me-1"></i> Vi Khí Hậu 7 Ngày
                        </div>
                        <div class="d-flex gap-3 small text-muted" style="font-size: 11px;">
                            <span><i class="bi bi-circle-fill text-success me-1"></i> Nhiệt độ (°C)</span>
                            <span><i class="bi bi-circle-fill text-info me-1"></i> Độ ẩm (%)</span>
                        </div>
                    </div>
                    <div style="height: 195px; position: relative;">
                        <canvas id="growthChart"></canvas>
                    </div>
                    <div class="d-flex justify-content-between gap-2 mt-2 pt-2 border-slate-200 flex-wrap">
                        <div class="micro-kpi-pill">
                            <i class="bi bi-sun-fill text-warning fs-6"></i>
                            <div>
                                <span class="text-muted small d-block" style="font-size: 10.5px;">Tích lũy nhiệt</span>
                                <strong class="text-dark">1,420°C</strong>
                            </div>
                        </div>
                        <div class="micro-kpi-pill">
                            <i class="bi bi-moisture text-info fs-6"></i>
                            <div>
                                <span class="text-muted small d-block" style="font-size: 10.5px;">Độ ẩm an toàn</span>
                                <strong class="text-dark">{{ $avgHumidity }}% (Tốt)</strong>
                            </div>
                        </div>
                        <div class="micro-kpi-pill">
                            <i class="bi bi-shield-check text-success fs-6"></i>
                            <div>
                                <span class="text-muted small d-block" style="font-size: 10.5px;">Sâu bệnh</span>
                                <strong class="text-success">Nguy cơ thấp</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Bottom Row: Bảng Giám Sát Vùng Trồng & Cột Cảnh Báo + Nhật Ký Canh Tác -->
    <div class="row g-4 mb-4">
        <!-- 3.1 Bảng Trạng Thái Vùng Trồng & Trạm Quan Trắc -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 mb-0">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                        <i class="bi bi-grid-fill text-success"></i> Danh Sách Vùng Trồng
                    </h5>
                    <a href="{{ url('/gardens/map') }}" class="btn btn-sm btn-outline-primary py-1 px-3">
                        <i class="bi bi-map"></i> Bản đồ
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="status-table w-100 mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Mã</th>
                                <th>Vùng Trồng</th>
                                <th>Cây Trồng</th>
                                <th>Diện Tích</th>
                                <th>Chỉ Số</th>
                                <th>Trạm IoT</th>
                                <th style="width: 90px; text-align: center;">Vị Trí</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gardens as $g)
                                <tr>
                                    <td><strong class="text-primary">{{ $g->code ?? 'VT-' . $g->id }}</strong></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $g->name }}</div>
                                        <div class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $g->location }}
                                        </div>
                                    </td>
                                    <td><span
                                            class="badge bg-light text-dark border">{{ $g->crop_type ?? 'Cây ăn quả' }}</span>
                                    </td>
                                    <td><strong>{{ number_format($g->area_m2) }} m²</strong></td>
                                    <td>
                                        <span class="health-score-badge bg-success-subtle text-success">
                                            <i class="bi bi-shield-fill-check"></i> 96%
                                        </span>
                                    </td>
                                    <td>
                                        @if ($g->stations->count() > 0)
                                            <span class="badge bg-success"><i class="bi bi-wifi"></i> Online</span>
                                        @else
                                            <span class="badge bg-secondary">Chưa gắn</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ url('/gardens/map') }}"
                                            class="btn btn-sm btn-outline-primary py-1 px-2" title="Xem bản đồ">
                                            <i class="bi bi-geo-alt"></i> Xem
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Chưa có dữ liệu vùng trồng.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3.2 Cột Phải: Cảnh Báo Trọng Yếu & Nhật Ký Canh Tác -->
        <div class="col-xl-4 col-lg-5 d-flex flex-column gap-3">
            <!-- Cảnh Báo Trọng Yếu (Thiết kế hài hòa tông màu ấm dịu) -->
            <div class="dash-card-alert">
                <div class="alert-header-box">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                        <i class="bi bi-bell-fill text-danger"></i> Thông Báo
                    </h6>
                    <a href="{{ url('/notifications') }}" class="fw-bold text-secondary text-decoration-none small"
                        style="font-size: 12px;">Tất cả</a>
                </div>
                <div class="p-3">
                    @if ($criticalAlerts->count() > 0)
                        <ul class="alert-items-list mb-0" style="padding-left: 0px">
                            @foreach ($criticalAlerts as $alert)
                                <li class="alert-item-box">
                                    <div
                                        class="alert-item-icon {{ $alert->priority === 'high' ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }}">
                                        <i
                                            class="bi {{ $alert->type === 'pest_alert' ? 'bi-bug-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                                    </div>
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <div class="fw-bold text-truncate text-dark" style="font-size: 13px;">
                                            {{ $alert->title }}</div>
                                        <div class="text-muted small text-truncate" style="font-size: 11px;">
                                            {{ $alert->content }}</div>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted small"></i>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert-item-box">
                            <div class="alert-item-icon bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-shield-fill-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark" style="font-size: 13px;">Vùng trồng an toàn</div>
                                <div class="text-muted small">Không có cảnh báo cấp bách</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Nhật Ký Canh Tác Gần Đây -->
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1 mb-0">
                <div
                    class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">
                        <i class="bi bi-calendar2-check text-primary me-1"></i> Nhật Ký Canh Tác
                    </h6>
                    <a href="{{ url('/care/logs') }}" class="btn btn-sm btn-light py-0 px-2"
                        style="font-size: 11px;">Tất cả</a>
                </div>
                <div class="p-3">
                    <div class="tasks-list">
                        @forelse($recentCareLogs->take(3) as $care)
                            <div class="task-item-card">
                                <div style="min-width: 0;">
                                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 12px;">
                                        {{ $care->content }}</h6>
                                    <div class="text-muted small" style="font-size: 11px;">
                                        <i class="bi bi-geo-alt"></i> {{ $care->garden->name ?? 'Vườn' }} &bull;
                                        {{ \Carbon\Carbon::parse($care->performed_at)->format('d/m') }}
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success ms-2" style="font-size: 10px;">Đã
                                    xong</span>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted small">
                                <i class="bi bi-journal-text fs-4 d-block mb-1"></i>
                                Chưa có nhật ký canh tác
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const growthCanvas = document.getElementById('growthChart');
            if (growthCanvas) {
                const ctx = growthCanvas.getContext('2d');

                const gradientGreen = ctx.createLinearGradient(0, 0, 0, 180);
                gradientGreen.addColorStop(0, 'rgba(22, 163, 74, 0.18)');
                gradientGreen.addColorStop(1, 'rgba(22, 163, 74, 0.01)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
                        datasets: [{
                                label: 'Nhiệt độ (°C)',
                                data: [24, 25.5, 26, 27.5, 26.2, 25.8, 26.5],
                                borderColor: '#16a34a',
                                backgroundColor: gradientGreen,
                                borderWidth: 2.5,
                                tension: 0.4,
                                fill: true,
                                pointRadius: 3,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Độ ẩm (%)',
                                data: [75, 80, 82, 78, 85, 80, 78],
                                borderColor: '#0284c7',
                                borderWidth: 2,
                                borderDash: [4, 4],
                                tension: 0.4,
                                fill: false,
                                pointRadius: 0
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
                                padding: 10,
                                cornerRadius: 8,
                                titleColor: '#f8fafc',
                                bodyColor: '#cbd5e1'
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#64748b',
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    color: '#64748b',
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
