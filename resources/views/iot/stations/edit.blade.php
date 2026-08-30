@extends('layouts.app')

@section('title', 'Chỉnh Sửa Trạm Quan Trắc IoT - ' . $station->name)

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .map-picker-viewport {
            position: relative;
            width: 100%;
            height: 380px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #cbd5e1;
        }

        .map-picker-viewport .leaflet-container {
            height: 100% !important;
            width: 100% !important;
            cursor: grab;
        }

        .map-picker-viewport .leaflet-container:active {
            cursor: grabbing;
        }

        .map-center-pin {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -100%);
            z-index: 1000;
            font-size: 34px;
            color: #ef4444;
            pointer-events: none;
            filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.3));
            animation: bouncePin 1.5s infinite alternate;
        }

        @keyframes bouncePin {
            0% { transform: translate(-50%, -100%); }
            100% { transform: translate(-50%, -115%); }
        }

        .form-section-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
@endpush

@section('content')
    <x-page-header title="Chỉnh Sửa Cấu Hình Trạm Quan Trắc" subtitle="Cập nhật thông tin vùng trồng, chu kỳ telemetry và tọa độ GIS cho trạm {{ $station->name }} ({{ $station->code }}).">
        <x-slot:actions>
            <a href="{{ route('iot.stations') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách trạm
            </a>
        </x-slot:actions>
    </x-page-header>

    <form action="{{ url('/iot/stations/update/' . $station->id) }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- CỘT BÊN TRÁI (5 COLS): THÔNG TIN CẤU HÌNH TRẠM -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <div class="form-section-title">
                        <i class="bi bi-sliders text-primary"></i> Thông Tin Cấu Hình Trạm
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã định danh trạm (Station Code)</label>
                        <input type="text" class="form-control font-monospace bg-light" value="{{ $station->code }}" readonly>
                        <div class="form-text small text-muted">Mã trạm là duy nhất và được gắn cố định trong firmware máy trạm.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên trạm quan trắc <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $station->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gắn với vùng trồng</label>
                        <select name="garden_id" class="form-select @error('garden_id') is-invalid @enderror">
                            <option value="">-- Chưa gắn vùng trồng --</option>
                            @foreach ($gardens as $g)
                                <option value="{{ $g->id }}" {{ old('garden_id', $station->garden_id) == $g->id ? 'selected' : '' }}>
                                    {{ $g->name }} ({{ $g->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('garden_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trạng thái vận hành <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $station->status) == 'active' ? 'selected' : '' }}>Hoạt động (Hoạt động ổn định)</option>
                            <option value="danger" {{ old('status', $station->status) == 'danger' ? 'selected' : '' }}>Cảnh báo dịch bệnh / Nguy hiểm</option>
                            <option value="maintenance" {{ old('status', $station->status) == 'maintenance' ? 'selected' : '' }}>Đang bảo trì kỹ thuật</option>
                            <option value="inactive" {{ old('status', $station->status) == 'inactive' ? 'selected' : '' }}>Tạm ngưng hoạt động</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Chu kỳ gửi dữ liệu Telemetry (Giây)</label>
                        <div class="input-group">
                            <input type="number" name="data_interval" class="form-control font-monospace"
                                placeholder="900" value="{{ old('data_interval', $station->data_interval ?: 900) }}" min="5">
                            <span class="input-group-text bg-light text-muted">giây</span>
                        </div>
                        <div class="form-text small text-success mt-1">
                            <i class="bi bi-broadcast me-1"></i> Khi bấm Lưu, hệ thống sẽ <strong>tự động phát lệnh MQTT <code>SET_INTERVAL</code> xuống máy trạm tức thì</strong>.
                        </div>
                    </div>

                    <div class="mt-auto pt-3 border-top d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                            <i class="bi bi-check-circle-fill me-1"></i> Lưu Thay Đổi & Gửi Lệnh
                        </button>
                        <a href="{{ route('iot.stations') }}" class="btn btn-outline-secondary px-3 py-2">
                            Hủy bỏ
                        </a>
                    </div>
                </div>
            </div>

            <!-- CỘT BÊN PHẢI (7 COLS): BẢN ĐỒ CHỌN TỌA ĐỘ GIS -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-section-title mb-0">
                            <i class="bi bi-geo-alt-fill text-danger"></i> Tọa Độ Địa Lý Trạm (Bản Đồ GIS)
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="getCurrentGpsPosition()">
                            <i class="bi bi-crosshair me-1"></i> Lấy GPS hiện tại
                        </button>
                    </div>

                    <!-- Ô Tìm Kiếm Địa Điểm -->
                    <div class="input-group mb-3">
                        <input type="text" id="search-location-input" class="form-control"
                            placeholder="Nhập tên địa danh cần tìm (Ví dụ: Gia Bình, Thuận Thành, Lục Ngạn, Phúc Hòa)..."
                            onkeypress="if(event.key==='Enter'){event.preventDefault();searchLocationGeocode();}">
                        <button type="button" class="btn btn-primary px-3" onclick="searchLocationGeocode()">
                            <i class="bi bi-search me-1"></i> Tìm Vị Trí
                        </button>
                    </div>

                    <!-- Khung Bản Đồ Leaflet -->
                    <div class="map-picker-viewport mb-3">
                        <div id="map-edit-picker"></div>
                        <div class="map-center-pin"><i class="bi bi-geo-alt-fill"></i></div>
                    </div>

                    <!-- Ô Nhập Vĩ độ & Kinh độ -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Vĩ độ (Latitude)</label>
                            <input type="text" id="edit-station-lat" name="latitude"
                                class="form-control font-monospace fw-bold text-primary"
                                value="{{ old('latitude', $station->latitude ?: '21.054200') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Kinh độ (Longitude)</label>
                            <input type="text" id="edit-station-lng" name="longitude"
                                class="form-control font-monospace fw-bold text-primary"
                                value="{{ old('longitude', $station->longitude ?: '106.071200') }}">
                        </div>
                    </div>
                    <div class="form-text small text-muted mt-2">
                        <i class="bi bi-hand-index-thumb me-1 text-primary"></i> Bạn có thể <strong>kéo bản đồ</strong> để ghim tâm vào vị trí đặt trạm, hoặc <strong>nhập tọa độ tay</strong> trực tiếp vào 2 ô trên.
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let mapPicker;
        let isTypingCoords = false;

        document.addEventListener('DOMContentLoaded', () => {
            initEditMap();
            bindManualInputs();
        });

        function initEditMap() {
            const initialLat = parseFloat(document.getElementById('edit-station-lat').value) || 21.0542;
            const initialLng = parseFloat(document.getElementById('edit-station-lng').value) || 106.0712;

            mapPicker = L.map('map-edit-picker').setView([initialLat, initialLng], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(mapPicker);

            const updateCoordsFromCenter = () => {
                if (isTypingCoords) return;
                const center = mapPicker.getCenter();
                document.getElementById('edit-station-lat').value = center.lat.toFixed(6);
                document.getElementById('edit-station-lng').value = center.lng.toFixed(6);
            };

            mapPicker.on('move', updateCoordsFromCenter);
            updateCoordsFromCenter();
        }

        function bindManualInputs() {
            ['edit-station-lat', 'edit-station-lng'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function() {
                        isTypingCoords = true;
                        const lat = parseFloat(document.getElementById('edit-station-lat').value);
                        const lng = parseFloat(document.getElementById('edit-station-lng').value);
                        if (!isNaN(lat) && !isNaN(lng) && mapPicker) {
                            mapPicker.setView([lat, lng], mapPicker.getZoom());
                        }
                        setTimeout(() => { isTypingCoords = false; }, 300);
                    });
                }
            });
        }

        function searchLocationGeocode() {
            const query = document.getElementById('search-location-input').value.trim();
            if (!query) {
                if (typeof showToast === 'function') showToast('Vui lòng nhập tên địa điểm cần tìm!', 'warning');
                return;
            }

            if (typeof showToast === 'function') showToast('Đang tìm kiếm vị trí: ' + query + '...', 'info');

            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Việt Nam')}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        const placeName = result.display_name.split(',')[0];

                        if (mapPicker) {
                            mapPicker.setView([lat, lng], 14);
                        }
                        if (typeof showToast === 'function') showToast('Đã định vị bản đồ tới: ' + placeName, 'success');
                    } else {
                        if (typeof showToast === 'function') showToast('Không tìm thấy địa điểm trên bản đồ!', 'warning');
                    }
                })
                .catch(err => {
                    if (typeof showToast === 'function') showToast('Không thể kết nối dịch vụ tìm kiếm địa điểm!', 'danger');
                });
        }

        function getCurrentGpsPosition() {
            if ("geolocation" in navigator) {
                if (typeof showToast === 'function') showToast('Đang lấy vị trí GPS từ thiết bị...', 'info');
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        if (mapPicker) {
                            mapPicker.setView([lat, lng], 15);
                        }
                        if (typeof showToast === 'function') showToast('Đã di chuyển tới tọa độ GPS của bạn!', 'success');
                    },
                    function(error) {
                        if (typeof showToast === 'function') showToast('Không thể lấy vị trí GPS từ thiết bị!', 'warning');
                    }
                );
            }
        }
    </script>
@endpush
