@extends('layouts.app')

@section('title', 'Lịch Sử Thời Tiết & Vi Khí Hậu Vùng Trồng')

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
    <x-page-header title="Lịch Sử Thời Tiết & Vi Khí Hậu Vùng Trồng">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>IoT & Quan trắc</span>
            <span>/</span>
            <span class="text-primary fw-bold">Lịch sử thời tiết</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <form method="GET" action="{{ route('iot.weather.history') }}" class="d-flex align-items-center flex-wrap gap-2">
                <!-- Chọn Vùng Trồng -->
                <div class="d-flex align-items-center gap-1.5">
                    <label for="garden_id" class="text-nowrap fw-semibold text-muted small mb-0"><i class="bi bi-geo-alt text-danger"></i> Vùng trồng:</label>
                    <select name="garden_id" id="garden_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 180px;">
                        @foreach ($gardens as $gd)
                            <option value="{{ $gd->id }}" {{ $selectedGarden && $selectedGarden->id == $gd->id ? 'selected' : '' }}>
                                {{ $gd->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Chọn Trạm Quan Trắc -->
                <div class="d-flex align-items-center gap-1.5">
                    <label for="station_id" class="text-nowrap fw-semibold text-muted small mb-0"><i class="bi bi-broadcast text-primary"></i> Trạm quan trắc:</label>
                    <select name="station_id" id="station_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 200px;">
                        @foreach ($stations as $st)
                            <option value="{{ $st->id }}" {{ $selectedStation && $selectedStation->id == $st->id ? 'selected' : '' }}>
                                [{{ $st->code }}] {{ $st->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </x-slot:actions>
    </x-page-header>

    <!-- BANNER THÔNG TIN VÙNG TRỒNG & TRẠM -->
    <div class="card border-0 bg-white shadow-xs rounded-4 p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 22px;">
                    <i class="bi bi-cloud-sun-fill"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold text-dark mb-0">{{ $summaryStats['garden_name'] }}</h5>
                        <span class="badge bg-primary-subtle text-primary border font-monospace">{{ $summaryStats['station_code'] }}</span>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i> Dữ liệu vi khí hậu được tổng hợp từ cảm biến trạm quan trắc thực tế <strong>{{ $summaryStats['station_name'] }}</strong>
                    </small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-check-circle-fill me-1"></i> Nguồn: Cảm biến vi khí hậu hiện trường
                </span>
            </div>
        </div>
    </div>

    <!-- KHỐI 1: THỐNG KÊ TÓM TẮT 30 NGÀY -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="weather-kpi-card d-flex align-items-center gap-3">
                <div class="weather-kpi-icon bg-warning-subtle text-warning border border-warning-subtle">
                    <i class="bi bi-thermometer-half"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Nhiệt Độ TB Vùng Trồng</div>
                    <div class="fs-4 fw-bold text-dark">{{ $summaryStats['avg_temp'] !== null ? $summaryStats['avg_temp'] . ' °C' : '--' }}</div>
                    <small class="text-muted" style="font-size: 11px;">{{ $summaryStats['total_real_days'] > 0 ? ('Tính trên ' . $summaryStats['total_real_days'] . ' ngày có dữ liệu') : 'Chưa có dữ liệu thực tế' }}</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="weather-kpi-card d-flex align-items-center gap-3">
                <div class="weather-kpi-icon bg-info-subtle text-info border border-info-subtle">
                    <i class="bi bi-droplet-half"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Độ Ẩm Khí Quyển TB</div>
                    <div class="fs-4 fw-bold text-dark">{{ $summaryStats['avg_humidity'] !== null ? $summaryStats['avg_humidity'] . ' %' : '--' }}</div>
                    <small class="text-muted" style="font-size: 11px;">{{ $summaryStats['total_records'] > 0 ? ($summaryStats['total_records'] . ' bản ghi cảm biến') : 'Chưa có dữ liệu đo' }}</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="weather-kpi-card d-flex align-items-center gap-3">
                <div class="weather-kpi-icon bg-primary-subtle text-primary border border-primary-subtle">
                    <i class="bi bi-cloud-rain-fill"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Tổng Lượng Mưa Đo Đạc</div>
                    <div class="fs-4 fw-bold text-dark">{{ $summaryStats['total_rain'] > 0 ? $summaryStats['total_rain'] . ' mm' : '0.0 mm' }}</div>
                    <small class="text-primary fw-medium" style="font-size: 11px;">{{ $summaryStats['rainy_days'] }} ngày ghi nhận mưa</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="weather-kpi-card d-flex align-items-center gap-3">
                <div class="weather-kpi-icon bg-success-subtle text-success border border-success-subtle">
                    <i class="bi bi-moisture"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Độ Ẩm Đất Trung Bình</div>
                    <div class="fs-4 fw-bold text-dark">{{ $summaryStats['avg_soil_moist'] !== null ? $summaryStats['avg_soil_moist'] . ' %' : '--' }}</div>
                    <small class="text-success fw-medium" style="font-size: 11px;">Đo từ cảm biến tầng rễ</small>
                </div>
            </div>
        </div>
    </div>

    <!-- KHỐI BIỂU ĐỒ DIỄN BIẾN 30 NGÀY -->
    <div class="card border-0 bg-white shadow-xs rounded-4 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 14px;">
                    <i class="bi bi-graph-up-arrow text-primary"></i> Biểu Đồ Diễn Biến Vi Khí Hậu & Môi Trường Đất 30 Ngày Gần Nhất
                </h6>
                <small class="text-muted">Dữ liệu quan trắc thực tế được ghi nhận từ cảm biến trạm hiện trường</small>
            </div>
            <div class="d-flex gap-3 small text-muted">
                <span><i class="bi bi-circle-fill text-danger me-1"></i> Nhiệt độ (°C)</span>
                <span><i class="bi bi-circle-fill text-info me-1"></i> Độ ẩm khí (%)</span>
                <span><i class="bi bi-circle-fill text-success me-1"></i> Độ ẩm đất (%)</span>
            </div>
        </div>
        <div style="height: 240px; position: relative;">
            <canvas id="weatherTrendChart"></canvas>
        </div>
    </div>

    <!-- KHỐI 2: BẢNG LỊCH SỬ THỜI TIẾT 30 NGÀY -->
    <div class="card mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 text-dark fw-bold" style="font-size: 1.05rem;">
                <i class="bi bi-calendar-week text-primary me-2"></i> Bảng Thống Kê Vi Khí Hậu Từng Ngày - {{ $summaryStats['garden_name'] }}
            </h5>
            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Bấm vào dòng bất kỳ để xem chi tiết các mốc giờ đo đạc</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="custom-table w-100 mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Ngày Quan Trắc</th>
                            <th>Đánh Giá Khí Hậu</th>
                            <th>Nhiệt Độ TB (°C)</th>
                            <th>Độ Ẩm TB (%)</th>
                            <th>Lượng Mưa</th>
                            <th>Độ Ẩm Đất / pH</th>
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
                                        @if (!empty($w['is_real_data']))
                                            <span class="badge bg-primary-subtle text-primary border font-monospace ms-1" style="font-size: 10px;" title="Dữ liệu cảm biến trạm">
                                                <i class="bi bi-broadcast me-0.5"></i>{{ $w['records_count'] }} mẫu
                                            </span>
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
                                    @if ($w['temp_avg'] !== null)
                                        <span class="fw-bold text-danger">{{ $w['temp_avg'] }} °C</span>
                                        <span class="text-muted small ms-1">({{ $w['temp_min'] }}° - {{ $w['temp_max'] }}°)</span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($w['humidity_avg'] !== null)
                                        <span class="fw-semibold text-info">{{ $w['humidity_avg'] }}%</span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($w['rain'] !== null && $w['rain'] > 0)
                                        <span class="fw-bold text-primary"><i class="bi bi-umbrella-fill me-1"></i>{{ $w['rain'] }} mm</span>
                                    @elseif ($w['rain'] !== null)
                                        <span class="text-muted">0.0 mm</span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($w['soil_moist'] !== null)
                                        <span class="fw-medium text-success">{{ $w['soil_moist'] }}%</span>
                                        @if ($w['soil_ph'] !== null)
                                            <small class="text-muted">({{ $w['soil_ph'] }} pH)</small>
                                        @endif
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $w['wind'] !== null ? ($w['wind'] . ' m/s') : '--' }}
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
        <div class="modal-dialog" style="max-width: 780px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cloud-sun-fill text-primary me-2"></i> Chi Tiết Vi Khí Hậu Ngày <span id="w-modal-date" class="text-primary fw-bold"></span></h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <div class="modal-body py-3">
                <div class="p-3 bg-light rounded-4 border mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="text-muted small">Trạm quan trắc cảm biến:</div>
                        <div class="fw-bold text-dark fs-6" id="w-modal-station"></div>
                        <div class="text-secondary small"><i class="bi bi-geo-alt me-1 text-danger"></i> Vùng trồng: <span id="w-modal-zone"></span></div>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="text-center px-3 py-1.5 bg-white rounded-3 border">
                            <div class="text-muted small">Nhiệt độ TB</div>
                            <div class="fw-bold text-danger fs-6" id="w-modal-temp"></div>
                        </div>
                        <div class="text-center px-3 py-1.5 bg-white rounded-3 border">
                            <div class="text-muted small">Độ ẩm TB</div>
                            <div class="fw-bold text-info fs-6" id="w-modal-humidity"></div>
                        </div>
                        <div class="text-center px-3 py-1.5 bg-white rounded-3 border">
                            <div class="text-muted small">Lượng mưa</div>
                            <div class="fw-bold text-primary fs-6" id="w-modal-rain"></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-secondary me-1"></i> Các Mốc Đo Đạc Cảm Biến Trong Ngày:</h6>
                    <span class="badge bg-light text-muted border" id="w-modal-records-count"></span>
                </div>
                <div class="table-responsive rounded-3 border bg-white" style="max-height: 320px; overflow-y: auto;">
                    <table class="custom-table w-100 mb-0">
                        <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 1;">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Khởi tạo Biểu đồ Xu hướng 30 ngày (Chart.js)
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('weatherTrendChart');
            if (ctx) {
                const weatherData = @json(array_reverse($dailyWeather));
                const labels = weatherData.map(d => d.date_display.substring(0, 5));
                const tempData = weatherData.map(d => d.temp_avg);
                const humData = weatherData.map(d => d.humidity_avg);
                const soilData = weatherData.map(d => d.soil_moist);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Nhiệt độ (°C)',
                                data: tempData,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.05)',
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 3,
                                spanGaps: true
                            },
                            {
                                label: 'Độ ẩm khí (%)',
                                data: humData,
                                borderColor: '#06b6d4',
                                backgroundColor: 'rgba(6, 182, 212, 0.05)',
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 3,
                                spanGaps: true
                            },
                            {
                                label: 'Độ ẩm đất (%)',
                                data: soilData,
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34, 197, 94, 0.05)',
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: 3,
                                spanGaps: true
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
                            legend: { display: false },
                            tooltip: { backgroundColor: '#0f172a', padding: 10, borderRadius: 8 }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                            y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }
        });

        function openWeatherDayModal(stationId, dateStr) {
            fetch(`{{ url('/iot/weather-history/detail') }}/${stationId}/${dateStr}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('w-modal-date').textContent = data.date_display;
                        document.getElementById('w-modal-station').textContent = data.station_name + ' (' + data.station_code + ')';
                        document.getElementById('w-modal-zone').textContent = data.zone_name;
                        document.getElementById('w-modal-temp').textContent = data.temp_avg + ' °C';
                        document.getElementById('w-modal-humidity').textContent = data.humidity_avg + ' %';
                        document.getElementById('w-modal-rain').textContent = data.total_rain + ' mm';
                        document.getElementById('w-modal-records-count').textContent = data.records_count > 0 ? (data.records_count + ' bản ghi cảm biến') : 'Ước lượng theo chu kỳ ngày/đêm';

                        const body = document.getElementById('w-modal-hourly-body');
                        body.innerHTML = '';

                        data.hourly.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td class="fw-semibold text-dark"><i class="bi bi-clock me-1 text-muted"></i> ${row.time}</td>
                                <td><span class="fw-bold text-danger">${row.temp} °C</span></td>
                                <td><span class="fw-medium text-info">${row.humidity}%</span></td>
                                <td>${row.rain > 0 ? `<span class="fw-bold text-primary">${row.rain} mm</span>` : `<span class="text-muted">0.0 mm</span>`}</td>
                                <td><span class="text-success fw-medium">${row.soil_moist}%</span></td>
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

