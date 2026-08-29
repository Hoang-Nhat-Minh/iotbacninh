@extends('layouts.app')

@section('title', 'Giám Sát Camera PTZ - ' . ($station['name'] ?? 'Trạm Quan Trắc'))

@push('styles')
    <style>
        .camera-viewport {
            position: relative;
            width: 100%;
            height: 480px;
            background-color: #0f172a;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 2px solid #1e293b;
        }

        .camera-feed-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        /* Overlay elements */
        .cam-overlay-top-left {
            position: absolute;
            top: 16px;
            left: 16px;
            z-index: 10;
        }

        .cam-overlay-top-right {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 10;
        }

        .cam-overlay-bottom {
            position: absolute;
            bottom: 16px;
            left: 16px;
            right: 16px;
            z-index: 10;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 10px 16px;
        }

        .live-dot {
            width: 10px;
            height: 10px;
            background-color: #ef4444;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            animation: pulseLive 1.5s infinite;
        }

        @keyframes pulseLive {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.8);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* PTZ Control Wheel & Panel */
        .ptz-wheel-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .ptz-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            max-width: 220px;
            margin: 0 auto 16px auto;
        }

        .ptz-btn-dir {
            height: 54px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .ptz-btn-dir:hover {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        .ptz-btn-dir:active {
            transform: scale(0.95);
        }

        .ptz-btn-center {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: none;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .ptz-btn-center:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        .preset-card-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .preset-card-item:hover {
            border-color: #10b981;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.12);
            transform: translateY(-2px);
        }

        /* AI Detection Box Overlay */
        .ai-detect-box {
            position: absolute;
            top: 35%;
            left: 42%;
            width: 140px;
            height: 120px;
            border: 2px dashed #ef4444;
            background: rgba(239, 68, 68, 0.15);
            border-radius: 8px;
            z-index: 5;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 4px;
            animation: fadeInDetect 0.5s ease;
        }

        @keyframes fadeInDetect {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .snapshot-thumb {
            width: 100%;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
@endpush

@section('content')
    <x-page-header title="Giám Sát Camera PTZ Trực Tiếp">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <a href="{{ url('/iot/stations') }}">Trạm quan trắc</a>
            <span>/</span>
            <span class="text-primary fw-bold">{{ $station['name'] ?? 'Xem Camera' }}</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <a href="{{ url('/iot/stations') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách trạm
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4 mb-4">
        <!-- 1. CỘT BÊN TRÁI (8 COLS): KHUNG CAMERA LIVE STREAM -->
        <div class="col-lg-8">
            <div class="camera-viewport">
                <!-- Hình ảnh luồng Camera (Live Feed) -->
                <img id="camera-feed"
                    src="{{ $station['camera_url'] ?? 'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=1000&q=80' }}"
                    alt="Camera Live Feed" class="camera-feed-img">

                <!-- AI Bounding Box Cảnh Báo Sâu Bệnh (Mô phỏng) -->
                <div id="ai-detection-overlay" class="ai-detect-box">
                    <span class="badge bg-danger text-white style-badge font-monospace" style="font-size: 10px;">AI DETECT:
                        SƯƠNG MAI (94.5%)</span>
                    <span class="text-white font-monospace text-end"
                        style="font-size: 9px; text-shadow: 0 1px 2px #000;">X:420 Y:350</span>
                </div>

                <!-- Overlay Top Left -->
                <div class="cam-overlay-top-left d-flex align-items-center gap-2">
                    <span class="badge bg-danger text-white px-2.5 py-1.5 d-flex align-items-center gap-1.5 shadow-sm">
                        <span class="live-dot"></span> LIVE 24/7
                    </span>
                    <span class="badge bg-dark text-white border border-secondary px-2.5 py-1.5 font-monospace">
                        {{ $station['code'] ?? 'TRẠM-01' }} | {{ $station['name'] ?? 'Trạm Quan Trắc' }}
                    </span>
                </div>

                <!-- Overlay Top Right -->
                <div class="cam-overlay-top-right d-flex align-items-center gap-2">
                    <span class="badge bg-dark text-white border border-secondary px-2.5 py-1.5 font-monospace">
                        <i class="bi bi-clock me-1 text-info"></i> <span id="live-clock">--:--:--</span>
                    </span>
                    <button type="button" class="btn btn-sm btn-dark border border-secondary px-2.5 text-white"
                        title="Toàn màn hình" onclick="toggleFullscreen()">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>

                <!-- Overlay Bottom Status Bar -->
                <div
                    class="cam-overlay-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 text-white small">
                    <div class="d-flex align-items-center gap-3">
                        <span><i class="bi bi-wifi text-success me-1"></i> Tín hiệu: <strong>Strong (1080p -
                                30fps)</strong></span>
                        <span><i class="bi bi-compass text-warning me-1"></i> Pan: <strong id="val-pan">45.0°</strong> |
                            Tilt: <strong id="val-tilt">-15.0°</strong></span>
                        <span><i class="bi bi-zoom-in text-info me-1"></i> Zoom: <strong id="val-zoom">2.5x</strong></span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                            <i class="bi bi-shield-check me-1"></i> Hồng ngoại ban đêm: Auto
                        </span>
                    </div>
                </div>
            </div>

            <!-- Dòng Thông Tin Tọa Độ bên dưới Camera -->
            <div class="d-flex justify-content-between align-items-center mt-2 px-2 text-muted small">
                <span><i class="bi bi-geo-alt-fill text-danger me-1"></i> Tọa độ trạm:
                    <strong>{{ $station['coords'] ?? '21.0542, 106.0712' }}</strong></span>
                <span><i class="bi bi-tree-fill text-success me-1"></i> Vùng trồng:
                    <strong>{{ $station['zone_name'] ?? 'Bắc Ninh' }}</strong></span>
            </div>
        </div>

        <!-- 2. CỘT BÊN PHẢI (4 COLS): BẢNG ĐIỀU KHIỂN -->
        <div class="col-lg-4">
            <div class="ptz-wheel-box h-100 d-flex flex-column justify-content-between">
                <div>
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size: 15px;">
                        <i class="bi bi-sliders text-primary"></i> Bảng điều khiển
                    </h6>

                    <!-- Grid D-Pad Control Wheel (Nút giữa là nút chụp ảnh) -->
                    <div class="ptz-grid">
                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Up-Left', -5, 5)"
                            title="Lên - Trái"><i class="bi bi-arrow-up-left"></i></button>
                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Up', 0, 5)" title="Xoay Lên"><i
                                class="bi bi-arrow-up"></i></button>
                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Up-Right', 5, 5)"
                            title="Lên - Phải"><i class="bi bi-arrow-up-right"></i></button>

                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Left', -5, 0)" title="Xoay Trái"><i
                                class="bi bi-arrow-left"></i></button>
                        <button type="button" class="ptz-btn-dir ptz-btn-center" onclick="takeSnapshot()"
                            title="Chụp ảnh tức thì"><i class="bi bi-camera-fill"></i></button>
                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Right', 5, 0)" title="Xoay Phải"><i
                                class="bi bi-arrow-right"></i></button>

                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Down-Left', -5, -5)"
                            title="Xuống - Trái"><i class="bi bi-arrow-down-left"></i></button>
                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Down', 0, -5)" title="Xoay Xuống"><i
                                class="bi bi-arrow-down"></i></button>
                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Down-Right', 5, -5)"
                            title="Xuống - Phải"><i class="bi bi-arrow-down-right"></i></button>
                    </div>

                    <!-- Điều chỉnh Mức Zoom -->
                    <div class="bg-light rounded-3 p-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label fw-bold mb-0 text-dark" style="font-size: 13px;">
                                <i class="bi bi-zoom-in text-info me-1"></i> Zoom
                            </label>

                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5"
                                    onclick="changeZoom(-0.5)" title="Thu nhỏ (Zoom out)">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <span class="badge bg-primary fs-6 font-monospace px-3 py-1.5"
                                    id="zoom-val-badge">2.5x</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5"
                                    onclick="changeZoom(0.5)" title="Phóng to (Zoom in)">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Các Nút Thao Tác Đưa Vào Bảng Điều Khiển -->
                <div class="d-flex flex-column gap-2 pt-1">
                    <button type="button" class="btn btn-outline-primary py-2 fw-medium"
                        onclick="openSaveCurrentPresetModal()">
                        <i class="bi bi-bookmark-plus-fill me-1"></i> Lưu góc chụp tự động
                    </button>
                    <button type="button" class="btn btn-outline-danger py-2 fw-medium" id="btn-record"
                        onclick="toggleRecording()">
                        <i class="bi bi-record-circle me-1"></i> Ghi hình 10s
                    </button>
                    <button type="button" class="btn btn-outline-success py-2 fw-medium" onclick="runAiCropScan()">
                        <i class="bi bi-cpu-fill me-1"></i> Quét AI sâu bệnh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. HÀNG DƯỚI: VỊ TRÍ GÓC XOAY ĐẶT SẴN (LẤY TỪ DATABASE) & THƯ VIỆN ẢNH CHỤP -->
    <div class="row g-4">
        <!-- Góc Camera Tọa Độ Đặt Sẵn (Load dữ liệu thật) -->
        <div class="col-lg-6">
            <div class="card border-0 bg-white rounded-4 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <i class="bi bi-bookmark-star-fill text-warning"></i> Góc Camera Tọa Độ Đặt Sẵn
                    </h5>
                    <span class="badge bg-light text-muted border font-monospace">{{ count($presets ?? []) }} Tọa độ</span>
                </div>

                <div class="row g-2">
                    @forelse($presets as $idx => $preset)
                        <div class="col-6">
                            <div class="preset-card-item"
                                onclick="applyPreset('{{ addslashes($preset->name) }}', {{ $preset->pan_angle }}, {{ $preset->tilt_angle }}, {{ $preset->zoom_level }})">
                                <div class="fw-bold text-dark small text-truncate" title="{{ $preset->name }}">
                                    {{ $preset->name }}</div>
                                <div class="text-muted small font-monospace" style="font-size: 11px;">
                                    Pan: {{ number_format($preset->pan_angle, 1) }}° | Tilt:
                                    {{ number_format($preset->tilt_angle, 1) }}° |
                                    {{ number_format($preset->zoom_level, 1) }}x
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-3">Chưa có tọa độ góc chụp nào được lưu.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Nhật Ký Ảnh Chụp Tức Thời -->
        <div class="col-lg-6">
            <div class="card border-0 bg-white rounded-4 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <i class="bi bi-images text-primary"></i> Nhật Ký Ảnh Chụp Tức Thời
                    </h5>
                    <span class="badge bg-light text-muted border font-monospace" id="snapshot-count">3 Hình ảnh</span>
                </div>

                <div class="row g-2" id="snapshot-gallery">
                    <div class="col-4">
                        <div class="border rounded-3 p-1 position-relative bg-light">
                            <img src="https://images.unsplash.com/photo-1592417817098-8f3d6ef23a85?auto=format&fit=crop&w=400&q=80"
                                class="snapshot-thumb" alt="Snap 1">
                            <div class="text-muted font-monospace text-center mt-1" style="font-size: 10px;">16:45 - 19/08
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="border rounded-3 p-1 position-relative bg-light">
                            <img src="https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=400&q=80"
                                class="snapshot-thumb" alt="Snap 2">
                            <div class="text-muted font-monospace text-center mt-1" style="font-size: 10px;">14:30 - 19/08
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="border rounded-3 p-1 position-relative bg-light">
                            <img src="https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&w=400&q=80"
                                class="snapshot-thumb" alt="Snap 3">
                            <div class="text-muted font-monospace text-center mt-1" style="font-size: 10px;">10:15 - 19/08
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Lưu Tọa Độ Góc Chụp Hiện Tại -->
    <div class="app-modal" id="modal-save-preset">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bookmark-plus-fill text-primary"></i> Lưu Góc Chụp Tự Động</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form action="{{ url('/iot/locations/store') }}" method="POST">
                @csrf
                <input type="hidden" name="monitoring_station_id" value="{{ $station['id'] }}">
                <input type="hidden" name="status" value="active">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên góc chụp (Preset) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                            placeholder="Ví dụ: Góc luống dưa chuột tây #2" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-4">
                            <label class="form-label">Góc Pan (°)</label>
                            <input type="number" step="0.1" name="pan_angle" id="save-preset-pan"
                                class="form-control" readonly>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Góc Tilt (°)</label>
                            <input type="number" step="0.1" name="tilt_angle" id="save-preset-tilt"
                                class="form-control" readonly>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Zoom Level</label>
                            <input type="number" step="0.1" name="zoom_level" id="save-preset-zoom"
                                class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu Tọa Độ</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentPan = 45;
        let currentTilt = -15;
        let currentZoom = 2.5; // Giới hạn từ 1.0 đến 4.0
        let isRecording = false;

        document.addEventListener('DOMContentLoaded', () => {
            startClock();
        });

        // 1. Đồng hồ thời gian thực Live Feed
        function startClock() {
            const clockEl = document.getElementById('live-clock');
            setInterval(() => {
                const now = new Date();
                clockEl.textContent = now.toLocaleTimeString('vi-VN');
            }, 1000);
        }

        // 2. Di chuyển Camera theo các góc Pan & Tilt
        function moveCamera(directionName, deltaPan, deltaTilt) {
            currentPan = Math.max(-180, Math.min(180, currentPan + deltaPan));
            currentTilt = Math.max(-45, Math.min(45, currentTilt + deltaTilt));

            document.getElementById('val-pan').textContent = currentPan.toFixed(1) + '°';
            document.getElementById('val-tilt').textContent = currentTilt.toFixed(1) + '°';
        }

        // 3. Thay đổi Zoom (Giới hạn từ 1.0 đến 4.0)
        function updateZoom(val) {
            currentZoom = Math.max(1.0, Math.min(4.0, Math.round(parseFloat(val) * 10) / 10));
            document.getElementById('val-zoom').textContent = currentZoom.toFixed(1) + 'x';
            document.getElementById('zoom-val-badge').textContent = currentZoom.toFixed(1) + 'x';
        }

        function changeZoom(delta) {
            updateZoom(currentZoom + delta);
        }

        // 4. Áp dụng Tọa độ Preset đặt sẵn
        function applyPreset(name, pan, tilt, zoom) {
            currentPan = pan;
            currentTilt = tilt;
            document.getElementById('val-pan').textContent = currentPan.toFixed(1) + '°';
            document.getElementById('val-tilt').textContent = currentTilt.toFixed(1) + '°';
            updateZoom(zoom);
        }

        // 5. Chụp Ảnh Tức Thời & Thêm vào Thư Viện
        function takeSnapshot() {
            const gallery = document.getElementById('snapshot-gallery');
            const now = new Date();
            const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

            const itemHtml = `
                <div class="col-4">
                    <div class="border rounded-3 p-1 position-relative bg-light">
                        <img src="https://images.unsplash.com/photo-1592417817098-8f3d6ef23a85?auto=format&fit=crop&w=400&q=80" class="snapshot-thumb" alt="Snap New">
                        <div class="text-muted font-monospace text-center mt-1" style="font-size: 10px;">${timeStr} - Vừa xong</div>
                    </div>
                </div>
            `;

            gallery.insertAdjacentHTML('afterbegin', itemHtml);
            showToast('📸 Đã lưu ảnh chụp thành công!', 'success');
        }

        // 6. Ghi Hình 10 Giây
        function toggleRecording() {
            const btn = document.getElementById('btn-record');
            if (!isRecording) {
                isRecording = true;
                btn.className = 'btn btn-danger py-2 fw-medium animate-pulse';
                btn.innerHTML = '<i class="bi bi-stop-circle-fill me-1"></i> Đang ghi hình (10s)...';

                setTimeout(() => {
                    isRecording = false;
                    btn.className = 'btn btn-outline-danger py-2 fw-medium';
                    btn.innerHTML = '<i class="bi bi-record-circle me-1"></i> Ghi hình 10s';
                    showToast('✅ Đã lưu video clip ghi hình!', 'success');
                }, 10000);
            }
        }

        // 7. Chạy Quét AI Sâu Bệnh
        function runAiCropScan() {
            const overlay = document.getElementById('ai-detection-overlay');
            overlay.style.display = 'flex';
            showToast('🤖 Đã bật quét AI sâu bệnh!', 'info');
        }

        // 8. Mở Modal Xác Định Tọa Độ Camera Hiện Tại
        function openSaveCurrentPresetModal() {
            document.getElementById('save-preset-pan').value = currentPan.toFixed(1);
            document.getElementById('save-preset-tilt').value = currentTilt.toFixed(1);
            document.getElementById('save-preset-zoom').value = currentZoom.toFixed(1);
            openModal('modal-save-preset');
        }

        function toggleFullscreen() {
            const elem = document.querySelector('.camera-viewport');
            if (!document.fullscreenElement) {
                elem.requestFullscreen().catch(err => {});
            } else {
                document.exitFullscreen();
            }
        }

        // Tự động làm mới dữ liệu trạm mỗi 300 giây (5 phút)
        setInterval(function() {
            // Không làm mới nếu đang mở modal lưu preset hoặc đang ghi hình
            const isModalOpen = document.getElementById('modal-save-preset')?.classList.contains('show');
            if (!isModalOpen && !isRecording) {
                console.log('[IoT Station Auto-Refresh] Đang làm mới dữ liệu quan trắc (chu kỳ 300s)...');
                window.location.reload();
            }
        }, 300000);
    </script>
@endpush
