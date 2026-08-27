@extends('layouts.app')

@section('title', 'Bản Đồ Vùng Trồng - Hệ Thống IoT Bắc Ninh')
@section('page_title', 'Bản Đồ Vùng Trồng & Trạm Quan Trắc')

@push('styles')
<!-- Leaflet CSS CDN for interactive mapping -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map {
        height: 600px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
    }
    .zone-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .zone-item:hover {
        background-color: #f1f3f5;
    }
    .zone-item.active {
        border-left: 4px solid var(--primary-color) !important;
        background-color: #e8f5e9;
    }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Map Canvas (Left 8 Columns) -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-geo-alt-fill text-success me-2"></i>Bản Đồ Số Vùng Trồng</span>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-success active" id="btn-draw"><i class="bi bi-vector-pen me-1"></i> Vẽ Vùng Mới</button>
                    <button class="btn btn-sm btn-outline-success" id="btn-add-station"><i class="bi bi-plus-circle me-1"></i> Thêm Trạm</button>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Map div -->
                <div id="map"></div>
            </div>
        </div>
    </div>

    <!-- Sidebar Listing & Zone Details (Right 4 Columns) -->
    <div class="col-lg-4 mb-4">
        <!-- Zone search and list -->
        <div class="card mb-4">
            <div class="card-header">
                <span><i class="bi bi-search me-2 text-success"></i>Tìm Kiếm Vùng Trồng</span>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Mã vùng (mã vùng trồng)...">
                    <button class="btn btn-primary" type="button"><i class="bi bi-search"></i></button>
                </div>

                <div class="list-group list-group-flush border-top border-bottom overflow-auto" style="max-height: 250px;">
                    <!-- Zone 1 -->
                    <div class="list-group-item zone-item active border-start border-4 border-success py-3" onclick="selectZone(1)">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-1">VT-01: Vùng Lúa Cao Sản</h6>
                            <span class="badge bg-success">Bình Thường</span>
                        </div>
                        <small class="text-muted d-block">Mã: <strong>MVT-BN-01</strong></small>
                        <small class="text-muted">Diện tích: 4.5 ha | Cây trồng: Lúa</small>
                    </div>

                    <!-- Zone 2 -->
                    <div class="list-group-item zone-item py-3 border-start border-4 border-danger" onclick="selectZone(2)">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-1">VT-02: Vùng Dưa Chuột</h6>
                            <span class="badge bg-danger">Sương Mai</span>
                        </div>
                        <small class="text-muted d-block">Mã: <strong>MVT-BN-02</strong></small>
                        <small class="text-muted">Diện tích: 3.0 ha | Cây trồng: Dưa Chuột</small>
                    </div>

                    <!-- Zone 3 -->
                    <div class="list-group-item zone-item py-3 border-start border-4 border-warning" onclick="selectZone(3)">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-1">VT-03: Vùng Ngô Sinh Khối</h6>
                            <span class="badge bg-warning">Nguy Cơ</span>
                        </div>
                        <small class="text-muted d-block">Mã: <strong>MVT-BN-03</strong></small>
                        <small class="text-muted">Diện tích: 5.0 ha | Cây trồng: Ngô</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Zone Info Panel -->
        <div class="card" id="zone-details-card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-info-circle-fill me-2"></i>Chi Tiết Vùng Trồng: VT-01
            </div>
            <div class="card-body">
                <table class="table table-sm border-0 mb-3">
                    <tr>
                        <td class="text-muted py-2" style="width: 40%;">Chủ sở hữu:</td>
                        <td class="fw-bold py-2">Nguyễn Văn A</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-2">Địa chỉ:</td>
                        <td class="fw-bold py-2">Yên Phong, Bắc Ninh</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-2">Tọa độ tâm:</td>
                        <td class="fw-bold py-2">21.1963, 105.9734</td>
                    </tr>
                    <tr>
                        <td class="text-muted py-2">Cảnh báo:</td>
                        <td><span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">An Toàn</span></td>
                    </tr>
                </table>

                <h6 class="fw-bold mb-2 border-bottom pb-1"><i class="bi bi-cpu text-secondary me-2"></i>Trạm Quan Trắc Trực Thuộc</h6>
                <div class="d-grid gap-2">
                    <a href="{{ url('/stations/1') }}" class="btn btn-outline-success btn-sm text-start py-2">
                        <i class="bi bi-cpu-fill me-2"></i>Trạm 1 - Phía Đông
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet Map JS CDN -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // Initialize map centering on Bac Ninh Province coordinates
    var map = L.map('map').setView([21.1863, 105.9834], 13);

    // Load OpenStreetMap tiles
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Mock markers for Monitoring Stations
    var station1 = L.marker([21.1850, 105.9800]).addTo(map)
        .bindPopup('<b>Trạm Quan Trắc Số 1</b><br>Khu Vực Phía Đông.<br><a href="{{ url("/stations/1") }}">Xem chi tiết trạm</a>');
    
    var station2 = L.marker([21.1960, 105.9900]).addTo(map)
        .bindPopup('<b>Trạm Quan Trắc Số 2</b><br>Khu Vực Phía Bắc.<br><span class="badge bg-danger">Cảnh báo sâu bệnh</span><br><a href="{{ url("/stations/2") }}">Xem chi tiết trạm</a>');

    // Add mock polygon for VT-01 (Vùng Lúa)
    var latlngsVT01 = [
        [21.1800, 105.9750],
        [21.1880, 105.9750],
        [21.1880, 105.9850],
        [21.1800, 105.9850]
    ];
    var polygonVT01 = L.polygon(latlngsVT01, {color: 'green', fillOpacity: 0.2}).addTo(map)
        .bindPopup('<b>VT-01: Vùng Lúa Cao Sản</b>');

    // Add mock polygon for VT-02 (Vùng Dưa Chuột)
    var latlngsVT02 = [
        [21.1900, 105.9850],
        [21.1980, 105.9850],
        [21.1980, 105.9950],
        [21.1900, 105.9950]
    ];
    var polygonVT02 = L.polygon(latlngsVT02, {color: 'red', fillOpacity: 0.2}).addTo(map)
        .bindPopup('<b>VT-02: Vùng Dưa Chuột</b><br><span class="text-danger fw-bold">Phát hiện Bệnh Sương Mai</span>');

    function selectZone(id) {
        // Toggle active list items
        document.querySelectorAll('.zone-item').forEach(item => item.classList.remove('active'));
        
        // Mock detailed data swap
        var detailsCard = document.getElementById('zone-details-card');
        
        if (id === 1) {
            map.setView([21.1840, 105.9800], 14);
            polygonVT01.openPopup();
            detailsCard.innerHTML = `
                <div class="card-header bg-success text-white">
                    <i class="bi bi-info-circle-fill me-2"></i>Chi Tiết Vùng Trồng: VT-01
                </div>
                <div class="card-body">
                    <table class="table table-sm border-0 mb-3">
                        <tr><td class="text-muted py-2" style="width: 40%;">Chủ sở hữu:</td><td class="fw-bold py-2">Nguyễn Văn A</td></tr>
                        <tr><td class="text-muted py-2">Địa chỉ:</td><td class="fw-bold py-2">Yên Phong, Bắc Ninh</td></tr>
                        <tr><td class="text-muted py-2">Tọa độ tâm:</td><td class="fw-bold py-2">21.1840, 105.9800</td></tr>
                        <tr><td class="text-muted py-2">Cảnh báo:</td><td><span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">An Toàn</span></td></tr>
                    </table>
                    <h6 class="fw-bold mb-2 border-bottom pb-1"><i class="bi bi-cpu text-secondary me-2"></i>Trạm Quan Trắc Trực Thuộc</h6>
                    <div class="d-grid"><a href="{{ url('/stations/1') }}" class="btn btn-outline-success btn-sm text-start py-2"><i class="bi bi-cpu-fill me-2"></i>Trạm 1 - Phía Đông</a></div>
                </div>
            `;
        } else if (id === 2) {
            map.setView([21.1940, 105.9900], 14);
            polygonVT02.openPopup();
            detailsCard.innerHTML = `
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Chi Tiết Vùng Trồng: VT-02
                </div>
                <div class="card-body">
                    <table class="table table-sm border-0 mb-3">
                        <tr><td class="text-muted py-2" style="width: 40%;">Chủ sở hữu:</td><td class="fw-bold py-2">Trần Văn B</td></tr>
                        <tr><td class="text-muted py-2">Địa chỉ:</td><td class="fw-bold py-2">Gia Bình, Bắc Ninh</td></tr>
                        <tr><td class="text-muted py-2">Tọa độ tâm:</td><td class="fw-bold py-2">21.1940, 105.9900</td></tr>
                        <tr><td class="text-muted py-2">Cảnh báo:</td><td><span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2">Bệnh Sương Mai</span></td></tr>
                    </table>
                    <h6 class="fw-bold mb-2 border-bottom pb-1"><i class="bi bi-cpu text-secondary me-2"></i>Trạm Quan Trắc Trực Thuộc</h6>
                    <div class="d-grid"><a href="{{ url('/stations/2') }}" class="btn btn-outline-danger btn-sm text-start py-2"><i class="bi bi-cpu-fill me-2"></i>Trạm 2 - Phía Bắc</a></div>
                </div>
            `;
        } else if (id === 3) {
            map.setView([21.1863, 105.9834], 13);
            detailsCard.innerHTML = `
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>Chi Tiết Vùng Trồng: VT-03
                </div>
                <div class="card-body">
                    <table class="table table-sm border-0 mb-3">
                        <tr><td class="text-muted py-2" style="width: 40%;">Chủ sở hữu:</td><td class="fw-bold py-2">Phạm Thị C</td></tr>
                        <tr><td class="text-muted py-2">Địa chỉ:</td><td class="fw-bold py-2">Tiên Du, Bắc Ninh</td></tr>
                        <tr><td class="text-muted py-2">Tọa độ tâm:</td><td class="fw-bold py-2">21.1863, 105.9834</td></tr>
                        <tr><td class="text-muted py-2">Cảnh báo:</td><td><span class="badge bg-warning-subtle text-warning border border-warning-subtle py-1 px-2">Nguy Cơ Nhiễm Sâu</span></td></tr>
                    </table>
                    <h6 class="fw-bold mb-2 border-bottom pb-1"><i class="bi bi-cpu text-secondary me-2"></i>Trạm Quan Trắc Trực Thuộc</h6>
                    <span class="text-muted fs-7">Vùng này chưa liên kết trạm quan trắc.</span>
                </div>
            `;
        }
    }
</script>
@endpush
