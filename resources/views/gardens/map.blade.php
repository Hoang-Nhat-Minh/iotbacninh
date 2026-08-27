@extends('layouts.app')

@section('title', 'Bản Đồ Vị Trí Vườn & Trạm Quan Trắc')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #garden-map {
            height: 480px;
            width: 100%;
            border-radius: 16px;
            z-index: 1;
        }

        .map-gardens-card {
            border-radius: 16px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .gardens-scroll-body {
            max-height: 380px;
            overflow-y: auto;
        }

        .zone-badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #f87171;
        }

        .zone-badge-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .gis-picker-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
        }

        /* Mini Map Picker trong Modal */
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
    <x-page-header title="Bản Đồ Vùng Trồng & Trạm Quan Trắc">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Vườn & Bản đồ</span>
            <span>/</span>
            <span class="text-primary fw-bold">Bản đồ vùng trồng</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <button type="button" class="btn btn-secondary" onclick="openModal('modal-add-station')">
                <i class="bi bi-broadcast"></i> Thêm Trạm IoT
            </button>
            <button type="button" class="btn btn-primary" onclick="openAddGardenModal()">
                <i class="bi bi-plus-circle-fill"></i> Thêm Vùng Trồng
            </button>
        </x-slot:actions>
    </x-page-header>



    <!-- 1. HÀNG TRÊN: BẢN ĐỒ VÙNG TRỒNG (FULL WIDTH) -->
    <div class="card p-2 mb-4 shadow-sm border-0 rounded-4">
        <div id="garden-map"></div>
    </div>

    <!-- 2. HÀNG DƯỚI: DANH SÁCH VÙNG TRỒNG (CÓ HEIGHT CỐ ĐỊNH & OVERFLOW SCROLL) -->
    <div class="map-gardens-card mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                <i class="bi bi-grid-fill text-primary"></i> Danh Sách Vùng Trồng ({{ count($gardens) }} Vùng)
            </h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                <i class="bi bi-geo-fill me-1"></i> Tọa độ GIS
            </span>
        </div>
        <div class="gardens-scroll-body">
            <div class="table-responsive">
                <table class="custom-table w-100 mb-0">
                    <thead class="sticky-top bg-light" style="z-index: 2;">
                        <tr>
                            <th style="width: 70px;">Mã</th>
                            <th>Tên Vùng Trồng</th>
                            <th>Cây Trồng</th>
                            <th>Chủ Vườn</th>
                            <th>Vị Trí</th>
                            <th>Trạm IoT</th>
                            <th>Trạng Thái</th>
                            <th style="width: 140px; text-align: center;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gardens as $g)
                            @php
                                $isDanger = $g->code === 'VT-01';
                            @endphp
                            <tr>
                                <td><strong class="text-primary">{{ $g->code }}</strong></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $g->name }}</div>
                                    <small class="text-muted"><i class="bi bi-pin-map me-1"></i>Lat: {{ $g->latitude ?? 21.0542 }}, Lng: {{ $g->longitude ?? 106.0712 }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $g->crop_type ?? 'Cây ăn quả' }}</span>
                                </td>
                                <td class="text-secondary fw-medium">{{ $g->user->name ?? 'Chưa gán' }}</td>
                                <td class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $g->location ?? 'Bắc Ninh' }}</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        <i class="bi bi-broadcast me-1"></i>{{ count($g->stations) }} Trạm
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-status {{ $isDanger ? 'zone-badge-danger' : 'zone-badge-success' }}">
                                        <i class="bi {{ $isDanger ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' }}"></i>
                                        {{ $isDanger ? 'Sâu đục cuống' : 'Bình thường' }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-primary btn-sm py-1 px-2" title="Định vị trên bản đồ"
                                            onclick="focusZone({{ $g->latitude ?? 21.0542 }}, {{ $g->longitude ?? 106.0712 }}, '{{ addslashes($g->name) }}', '{{ $isDanger ? 'danger' : 'success' }}')">
                                            <i class="bi bi-geo-alt"></i> Xem
                                        </button>
                                        <button class="btn btn-secondary btn-sm py-1 px-2" title="Sửa thông tin"
                                            onclick="openEditGardenModal({{ $g->id }}, '{{ $g->code }}', '{{ addslashes($g->name) }}', '{{ $g->crop_type }}', '{{ $g->location }}', {{ $g->latitude ?? 21.0542 }}, {{ $g->longitude ?? 106.0712 }}, '{{ $g->status }}')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-secondary btn-sm py-1 px-2 text-danger" title="Xóa vùng trồng"
                                            onclick="openDeleteGardenModal({{ $g->id }}, '{{ $g->code }}', '{{ addslashes($g->name) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Chưa có vùng trồng nào được khởi tạo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Thêm Vùng Trồng Mới -->
    <div class="app-modal" id="modal-add-garden">
        <div class="modal-dialog" style="max-width: 580px;">
            <form action="{{ route('gardens.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle text-primary"></i> Thêm Vùng Trồng Mới</h5>
                    <button type="button" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã vùng trồng <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="VT-02" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tên vườn trồng <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Vùng Cà Chua Gia Bình" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chủ vườn (Người dùng) <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->phone }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Loại cây trồng</label>
                            <input type="text" name="crop_type" class="form-control" placeholder="Bưởi Diễn / Cà chua">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ / Huyện xã</label>
                            <input type="text" name="location" id="add-garden-location" class="form-control" placeholder="Thuận Thành, Bắc Ninh">
                        </div>

                        <!-- BẢN ĐỒ CHỌN TỌA ĐỘ TÂM TRONG MODAL THÊM -->
                        <div class="col-12">
                            <div class="gis-picker-box">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 text-dark" style="font-size: 13px;">
                                        <i class="bi bi-hand-index-thumb text-primary me-1"></i> Kéo Bản Đồ Chọn Tọa Độ Tâm
                                    </label>
                                    <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2" style="font-size: 12px;" onclick="getCurrentGpsPosition('add')">
                                        <i class="bi bi-crosshair me-1"></i> Định vị GPS hiện tại
                                    </button>
                                </div>

                                <!-- Ô TÌM KIẾM ĐỊA ĐIỂM -->
                                <div class="input-group input-group-sm mb-2">
                                    <input type="text" id="search-location-input-add" class="form-control" placeholder="Nhập địa danh cần tìm (ví dụ: Thuận Thành, Bắc Ninh)..." onkeypress="if(event.key==='Enter'){event.preventDefault();searchLocationGeocode('add');}">
                                    <button type="button" class="btn btn-primary" onclick="searchLocationGeocode('add')">
                                        <i class="bi bi-search me-1"></i> Tìm Vị Trí
                                    </button>
                                </div>

                                <div class="mini-map-container">
                                    <div id="mini-map-add"></div>
                                    <div class="mini-map-pin"><i class="bi bi-geo-alt-fill"></i></div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1" style="font-size: 11px;">Vĩ độ (Latitude)</label>
                                        <input type="text" name="latitude" id="add-garden-lat" class="form-control form-control-sm font-monospace fw-bold text-primary" placeholder="21.0542">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1" style="font-size: 11px;">Kinh độ (Longitude)</label>
                                        <input type="text" name="longitude" id="add-garden-lng" class="form-control form-control-sm font-monospace fw-bold text-primary" placeholder="106.0712">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="status" value="active">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu Vùng Trồng</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Chỉnh Sửa Vùng Trồng -->
    <div class="app-modal" id="modal-edit-garden">
        <div class="modal-dialog" style="max-width: 580px;">
            <form id="form-edit-garden" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square text-primary"></i> Chỉnh Sửa Thông Tin Vùng Trồng</h5>
                    <button type="button" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã vùng</label>
                            <input type="text" id="edit-garden-code" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tên vườn</label>
                            <input type="text" id="edit-garden-name" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Loại cây trồng</label>
                            <input type="text" id="edit-garden-crop" name="crop_type" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" id="edit-garden-location" name="location" class="form-control">
                        </div>

                        <!-- BẢN ĐỒ CHỌN TỌA ĐỘ TÂM TRONG MODAL SỬA -->
                        <div class="col-12">
                            <div class="gis-picker-box">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0 text-dark" style="font-size: 13px;">
                                        <i class="bi bi-hand-index-thumb text-primary me-1"></i> Kéo Bản Đồ Chọn Tọa Độ Tâm
                                    </label>
                                    <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2" style="font-size: 12px;" onclick="getCurrentGpsPosition('edit')">
                                        <i class="bi bi-crosshair me-1"></i> Định vị GPS hiện tại
                                    </button>
                                </div>

                                <!-- Ô TÌM KIẾM ĐỊA ĐIỂM -->
                                <div class="input-group input-group-sm mb-2">
                                    <input type="text" id="search-location-input-edit" class="form-control" placeholder="Nhập địa danh cần tìm (ví dụ: Gia Bình, Bắc Ninh)..." onkeypress="if(event.key==='Enter'){event.preventDefault();searchLocationGeocode('edit');}">
                                    <button type="button" class="btn btn-primary" onclick="searchLocationGeocode('edit')">
                                        <i class="bi bi-search me-1"></i> Tìm Vị Trí
                                    </button>
                                </div>

                                <div class="mini-map-container">
                                    <div id="mini-map-edit"></div>
                                    <div class="mini-map-pin"><i class="bi bi-geo-alt-fill"></i></div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1" style="font-size: 11px;">Vĩ độ (Latitude)</label>
                                        <input type="text" id="edit-garden-lat" name="latitude" class="form-control form-control-sm font-monospace fw-bold text-primary">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1" style="font-size: 11px;">Kinh độ (Longitude)</label>
                                        <input type="text" id="edit-garden-lng" name="longitude" class="form-control form-control-sm font-monospace fw-bold text-primary">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="status" value="active">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Xóa Vùng Trồng -->
    <div class="app-modal" id="modal-delete-garden">
        <div class="modal-dialog" style="max-width: 440px;">
            <form id="form-delete-garden" action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Vùng Trồng</h5>
                    <button type="button" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="text-danger mb-3"><i class="bi bi-exclamation-triangle" style="font-size: 48px;"></i></div>
                    <p>Bạn có chắc muốn xóa <strong id="delete-garden-name" class="text-danger"></strong>?</p>
                    <p class="text-muted small">Lưu ý: Nếu vùng trồng đang liên kết nhật ký canh tác hoặc trạm quan trắc, hệ thống sẽ yêu cầu hủy liên kết trước khi xóa.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger">Xác Nhận Xóa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Thêm Trạm Quan Trắc Mới -->
    <div class="app-modal" id="modal-add-station">
        <div class="modal-dialog">
            <form action="{{ url('/iot/stations/store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-broadcast text-primary"></i> Thêm Trạm Quan Trắc Mới</h5>
                    <button type="button" class="modal-close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã trạm <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="TT-01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tên trạm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Trạm Quan Trắc Thuận Thành" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gắn với vùng trồng <span class="text-danger">*</span></label>
                            <select name="garden_id" class="form-select" required>
                                @foreach ($gardens as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chu kỳ gửi dữ liệu (giây)</label>
                            <input type="number" name="data_interval" class="form-control" value="60">
                        </div>
                        <input type="hidden" name="status" value="active">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu Trạm</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let miniMapAdd, miniMapEdit;
        const rawGardens = @json($gardens);

        document.addEventListener('DOMContentLoaded', function() {
            map = L.map('garden-map').setView([21.0542, 106.1012], 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap Bắc Ninh IoT'
            }).addTo(map);

            rawGardens.forEach(z => {
                const lat = z.latitude || 21.0542;
                const lng = z.longitude || 106.0712;
                const isDanger = z.code === 'VT-01';
                const color = isDanger ? '#ef4444' : '#22c55e';

                const marker = L.circleMarker([lat, lng], {
                    radius: 9,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(map);

                const popupContent = `
                    <div style="min-width: 200px;">
                        <h6 style="margin: 0 0 4px; font-weight: bold; color: #1e293b;">${z.name} (${z.code})</h6>
                        <div style="margin-bottom: 8px;">
                            <span style="font-size: 11px; padding: 2px 6px; border-radius: 12px; background: ${isDanger ? '#fee2e2' : '#dcfce7'}; color: ${isDanger ? '#b91c1c' : '#15803d'}; font-weight: 600;">
                                ${isDanger ? 'Cảnh báo sâu đục cuống' : 'Bình thường'}
                            </span>
                        </div>
                        <div style="font-size: 12px; color: #475569; margin-bottom: 8px;">
                            <strong>Địa điểm:</strong> ${z.location || 'Bắc Ninh'}
                        </div>
                        <div style="display: flex; gap: 4px;">
                            <a href="${window.location.origin}/iot/stations" class="btn btn-sm btn-primary" style="font-size: 11px; padding: 2px 8px;">Xem Trạm IoT</a>
                        </div>
                    </div>
                `;
                marker.bindPopup(popupContent);
            });

            initMiniMaps();
            bindManualLatControl();
        });

        function initMiniMaps() {
            // Mini Map cho Modal Thêm
            if (document.getElementById('mini-map-add') && !miniMapAdd) {
                miniMapAdd = L.map('mini-map-add', { zoomControl: false }).setView([21.0542, 106.0712], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMapAdd);

                const updateAddCenter = () => {
                    const center = miniMapAdd.getCenter();
                    document.getElementById('add-garden-lat').value = center.lat.toFixed(6);
                    document.getElementById('add-garden-lng').value = center.lng.toFixed(6);
                };
                miniMapAdd.on('move', updateAddCenter);
                updateAddCenter();
            }

            // Mini Map cho Modal Sửa
            if (document.getElementById('mini-map-edit') && !miniMapEdit) {
                miniMapEdit = L.map('mini-map-edit', { zoomControl: false }).setView([21.0542, 106.0712], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMapEdit);

                const updateEditCenter = () => {
                    const center = miniMapEdit.getCenter();
                    document.getElementById('edit-garden-lat').value = center.lat.toFixed(6);
                    document.getElementById('edit-garden-lng').value = center.lng.toFixed(6);
                };
                miniMapEdit.on('move', updateEditCenter);
                updateEditCenter();
            }
        }

        // Lắng nghe sự kiện gõ nhập tay Vĩ độ & Kinh độ để tự xoay bản đồ mini theo
        function bindManualLatControl() {
            ['add-garden-lat', 'add-garden-lng'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function() {
                        const lat = parseFloat(document.getElementById('add-garden-lat').value);
                        const lng = parseFloat(document.getElementById('add-garden-lng').value);
                        if (!isNaN(lat) && !isNaN(lng) && miniMapAdd) {
                            miniMapAdd.setView([lat, lng], miniMapAdd.getZoom());
                        }
                    });
                }
            });

            ['edit-garden-lat', 'edit-garden-lng'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function() {
                        const lat = parseFloat(document.getElementById('edit-garden-lat').value);
                        const lng = parseFloat(document.getElementById('edit-garden-lng').value);
                        if (!isNaN(lat) && !isNaN(lng) && miniMapEdit) {
                            miniMapEdit.setView([lat, lng], miniMapEdit.getZoom());
                        }
                    });
                }
            });
        }

        // Dịch vụ Tìm kiếm Địa điểm Geocoding
        function searchLocationGeocode(type) {
            const inputId = type === 'add' ? 'search-location-input-add' : 'search-location-input-edit';
            const query = document.getElementById(inputId).value.trim();
            if (!query) {
                showToast('Vui lòng nhập tên địa điểm cần tìm!', 'warning');
                return;
            }

            showToast('Đang tìm kiếm vị trí: ' + query + '...', 'info');

            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Bắc Ninh, Việt Nam')}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        const placeName = result.display_name.split(',')[0];

                        if (type === 'add' && miniMapAdd) {
                            miniMapAdd.setView([lat, lng], 14);
                            if (document.getElementById('add-garden-location')) {
                                document.getElementById('add-garden-location').value = placeName;
                            }
                        } else if (type === 'edit' && miniMapEdit) {
                            miniMapEdit.setView([lat, lng], 14);
                            if (document.getElementById('edit-garden-location')) {
                                document.getElementById('edit-garden-location').value = placeName;
                            }
                        }
                        showToast('Đã định vị bản đồ tới: ' + placeName, 'success');
                    } else {
                        showToast('Không tìm thấy địa điểm trên bản đồ!', 'warning');
                    }
                })
                .catch(err => {
                    showToast('Không thể kết nối dịch vụ tìm kiếm địa điểm!', 'danger');
                });
        }

        function openAddGardenModal() {
            openModal('modal-add-garden');
            setTimeout(() => {
                if (miniMapAdd) {
                    miniMapAdd.invalidateSize();
                    miniMapAdd.setView([21.0542, 106.0712], 12);
                }
            }, 250);
        }

        function openEditGardenModal(id, code, name, crop, location, lat, lng) {
            document.getElementById('form-edit-garden').action = window.location.origin + '/gardens/update/' + id;
            document.getElementById('edit-garden-code').value = code;
            document.getElementById('edit-garden-name').value = name;
            document.getElementById('edit-garden-crop').value = crop || '';
            document.getElementById('edit-garden-location').value = location || '';
            
            const targetLat = lat || 21.0542;
            const targetLng = lng || 106.0712;
            document.getElementById('edit-garden-lat').value = targetLat;
            document.getElementById('edit-garden-lng').value = targetLng;

            openModal('modal-edit-garden');

            setTimeout(() => {
                if (miniMapEdit) {
                    miniMapEdit.invalidateSize();
                    miniMapEdit.setView([targetLat, targetLng], 14);
                }
            }, 250);
        }

        function getCurrentGpsPosition(type) {
            if ("geolocation" in navigator) {
                showToast('Đang lấy vị trí GPS từ thiết bị...', 'info');
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        if (type === 'add' && miniMapAdd) {
                            miniMapAdd.setView([lat, lng], 15);
                        } else if (type === 'edit' && miniMapEdit) {
                            miniMapEdit.setView([lat, lng], 15);
                        }
                        showToast('Đã di chuyển tới tọa độ GPS của bạn!', 'success');
                    },
                    function(error) {
                        showToast('Không thể lấy vị trí GPS của thiết bị!', 'warning');
                    }
                );
            } else {
                showToast('Trình duyệt không hỗ trợ Geolocation!', 'warning');
            }
        }

        function focusZone(lat, lng, name, status) {
            if (map) {
                const mapEl = document.getElementById('garden-map');
                if (mapEl) {
                    mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                map.flyTo([lat, lng], 14, {
                    duration: 1.2
                });
                showToast('Đang định vị tới: ' + name, status === 'danger' ? 'danger' : 'success');
            }
        }

        function openDeleteGardenModal(id, code, name) {
            document.getElementById('form-delete-garden').action = window.location.origin + '/gardens/delete/' + id;
            document.getElementById('delete-garden-name').textContent = name + ' (' + code + ')';
            openModal('modal-delete-garden');
        }
    </script>
@endpush
