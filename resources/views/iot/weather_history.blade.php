@extends('layouts.app')

@section('title', 'Lịch Sử Thời Tiết & Vi Khí Hậu')

@push('styles')
    <style>
        .weather-kpi-card {
            border-radius: 16px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .weather-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .weather-kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .weather-table-row {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .weather-table-row:hover {
            background-color: #f8fafc;
        }

        .weather-table-row.today-row {
            background-color: #f0fdf4 !important;
            font-weight: 500;
        }
    </style>
@endpush

@section('content')
    <x-page-header title="Lịch Sử Thời Tiết & Vi Khí Hậu">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>IoT & Quan trắc</span>
            <span>/</span>
            <span class="text-primary fw-bold">Lịch sử thời tiết</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <form method="GET" action="{{ route('iot.weather.history') }}" class="d-flex align-items-center gap-2">
                <label for="station_id" class="text-nowrap fw-semibold text-muted small mb-0"><i class="bi bi-broadcast"></i> Chọn trạm quan trắc:</label>
                <select name="station_id" id="station_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 220px;">
                    @foreach ($stations as $st)
                        <option value="{{ $st->id }}" {{ $selectedStation && $selectedStation->id == $st->id ? 'selected' : '' }}>
                            {{ $st->name }} ({{ $st->garden->name ?? 'Bắc Ninh' }})
                        </option>
                    @endforeach
                </select>
            </form>
        </x-slot:actions>
    </x-page-header>

    <!-- KHỐI 1: THỐNG KÊ TÓM TẮT 30 NGÀY -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="weather-kpi-card d-flex align-items-center gap-3">
                <div class="weather-kpi-icon bg-warning-subtle text-warning border border-warning-subtle">
                    <i class="bi bi-thermometer-half"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Nhiệt Độ TB 30 Ngày</div>
                    <div class="fs-4 fw-bold text-dark">{{ $summaryStats['avg_temp'] }} °C</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="weather-kpi-card d-flex align-items-center gap-3">
                <div class="weather-kpi-icon bg-info-subtle text-info border border-info-subtle">
                    <i class="bi bi-droplet-half"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Độ Ẩm TB 30 Ngày</div>
                    <div class="fs-4 fw-bold text-dark">{{ $summaryStats['avg_humidity'] }} %</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="weather-kpi-card d-flex align-items-center gap-3">
                <div class="weather-kpi-icon bg-primary-subtle text-primary border border-primary-subtle">
                    <i class="bi bi-cloud-rain-fill"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Tổng Lượng Mưa 30 Ngày</div>
                    <div class="fs-4 fw-bold text-dark">{{ $summaryStats['total_rain'] }} mm</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="weather-kpi-card d-flex align-items-center gap-3">
                <div class="weather-kpi-icon bg-success-subtle text-success border border-success-subtle">
                    <i class="bi bi-cloud-sun-fill"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Số Ngày Có Mưa</div>
                    <div class="fs-4 fw-bold text-dark">{{ $summaryStats['rainy_days'] }} / 30 ngày</div>
                </div>
            </div>
        </div>
    </div>

    <!-- KHỐI 2: BẢNG LỊCH SỬ THỜI TIẾT 30 NGÀY -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 text-dark fw-bold" style="font-size: 1.05rem;">
                <i class="bi bi-calendar-week text-primary me-2"></i> Lịch Sử Vi Khí Hậu 30 Ngày Gần Nhất - {{ $selectedStation->name ?? 'Trạm Quan Trắc' }}
            </h5>
            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Bấm vào dòng bất kỳ để xem chi tiết thời tiết ngày đó</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="custom-table w-100 mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Ngày & Thứ</th>
                            <th>Trạng Thái Thời Tiết</th>
                            <th>Nhiệt Độ TB (°C)</th>
                            <th>Độ Ẩm TB (%)</th>
                            <th>Lượng Mưa (mm)</th>
                            <th>Độ Ẩm Đất (%)</th>
                            <th>Tốc Độ Gió</th>
                            <th style="width: 140px; text-align: center;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dailyWeather as $idx => $w)
                            <tr class="weather-table-row {{ $w['is_today'] ? 'today-row' : '' }}"
                                onclick="openWeatherDayModal('{{ $selectedStation->id ?? 1 }}', '{{ $w['date_str'] }}')">
                                <td class="text-secondary fw-semibold">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        {{ $w['date_display'] }}
                                        @if ($w['is_today'])
                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">Hôm nay</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small">{{ $w['day_of_week'] }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi {{ $w['icon'] }} fs-5"></i>
                                        <span class="fw-medium text-dark" style="font-size: 13.5px;">{{ $w['condition'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger">{{ $w['temp_avg'] }} °C</span>
                                    <span class="text-muted small ms-1">({{ $w['temp_min'] }}° - {{ $w['temp_max'] }}°)</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-info">{{ $w['humidity_avg'] }}%</span>
                                </td>
                                <td>
                                    @if ($w['rain'] > 0)
                                        <span class="fw-bold text-primary"><i class="bi bi-umbrella-fill me-1"></i>{{ $w['rain'] }} mm</span>
                                    @else
                                        <span class="text-muted">0.0 mm</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-medium text-secondary">{{ $w['soil_moist'] }}%</span>
                                </td>
                                <td class="text-muted small">
                                    {{ $w['wind'] }} m/s
                                </td>
                                <td style="text-align: center;" onclick="event.stopPropagation();">
                                    <button type="button" class="btn btn-outline-primary btn-sm px-2.5 py-1"
                                        onclick="openWeatherDayModal('{{ $selectedStation->id ?? 1 }}', '{{ $w['date_str'] }}')">
                                        <i class="bi bi-eye-fill me-1"></i> Chi tiết ngày
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL CHI TIẾT THỜI TIẾT 1 NGÀY -->
    <div class="app-modal" id="modal-detail-weather-day">
        <div class="modal-dialog" style="max-width: 760px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cloud-sun-fill text-primary me-2"></i> Chi Tiết Thời Tiết Ngày <span id="w-modal-date" class="text-primary fw-bold"></span></h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <div class="modal-body py-4">
                <div class="p-3 bg-light rounded border mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Trạm quan trắc quan trắc:</div>
                        <div class="fw-bold text-dark fs-6" id="w-modal-station"></div>
                        <div class="text-secondary small"><i class="bi bi-geo-alt me-1"></i> Vùng trồng: <span id="w-modal-zone"></span></div>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="text-center px-3 py-1.5 bg-white rounded border">
                            <div class="text-muted small">Nhiệt độ TB</div>
                            <div class="fw-bold text-danger fs-6" id="w-modal-temp"></div>
                        </div>
                        <div class="text-center px-3 py-1.5 bg-white rounded border">
                            <div class="text-muted small">Độ ẩm TB</div>
                            <div class="fw-bold text-info fs-6" id="w-modal-humidity"></div>
                        </div>
                        <div class="text-center px-3 py-1.5 bg-white rounded border">
                            <div class="text-muted small">Tổng mưa</div>
                            <div class="fw-bold text-primary fs-6" id="w-modal-rain"></div>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-clock-history text-secondary me-1"></i> Diễn Biến Vi Khí Hậu Theo Các Mốc Giờ Trong Ngày:</h6>
                <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                    <table class="custom-table w-100">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Nhiệt độ (°C)</th>
                                <th>Độ ẩm (%)</th>
                                <th>Lượng mưa (mm)</th>
                                <th>Độ ẩm đất (%)</th>
                                <th>Gió (m/s)</th>
                            </tr>
                        </thead>
                        <tbody id="w-modal-hourly-body">
                            <!-- Populated via AJAX JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-modal-close">Đóng Cửa Sổ</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openWeatherDayModal(stationId, dateStr) {
            fetch(`{{ url('/iot/weather-history/detail') }}/${stationId}/${dateStr}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('w-modal-date').textContent = data.date_display;
                        document.getElementById('w-modal-station').textContent = data.station_name;
                        document.getElementById('w-modal-zone').textContent = data.zone_name;
                        document.getElementById('w-modal-temp').textContent = data.temp_avg + ' °C';
                        document.getElementById('w-modal-humidity').textContent = data.humidity_avg + ' %';
                        document.getElementById('w-modal-rain').textContent = data.total_rain + ' mm';

                        const body = document.getElementById('w-modal-hourly-body');
                        body.innerHTML = '';

                        data.hourly.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td class="fw-semibold text-dark"><i class="bi bi-clock me-1 text-muted"></i> ${row.time}</td>
                                <td><span class="fw-bold text-danger">${row.temp} °C</span></td>
                                <td><span class="fw-medium text-info">${row.humidity}%</span></td>
                                <td>${row.rain > 0 ? `<span class="fw-bold text-primary">${row.rain} mm</span>` : `<span class="text-muted">0.0 mm</span>`}</td>
                                <td><span class="text-secondary">${row.soil_moist}%</span></td>
                                <td class="text-muted small">${row.wind} m/s</td>
                            `;
                            body.appendChild(tr);
                        });

                        openModal('modal-detail-weather-day');
                    }
                })
                .catch(err => {
                    if (typeof showToast === 'function') {
                        showToast('Không thể nạp thông tin thời tiết chi tiết.', 'danger');
                    }
                });
        }
    </script>
@endpush
