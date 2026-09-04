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

        .play-btn-circle {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease;
        }

        #camera-standby-cover:hover .play-btn-circle {
            transform: scale(1.1);
            box-shadow: 0 0 35px rgba(59, 130, 246, 0.6) !important;
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
            <div class="camera-viewport position-relative mb-3">
                <!-- Video Player phát trực tiếp (Hls.js) -->
                <video id="camera-live-video" class="camera-feed-img w-100 h-100" playsinline controls autoplay muted
                    style="display: none; background: #000;"></video>

                <!-- Màn hình sẵn sàng phát trực tiếp (Cover Player) -->
                <div id="camera-standby-cover"
                    class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center text-white p-4"
                    style="background: radial-gradient(circle at center, rgba(30, 41, 59, 0.85) 0%, rgba(15, 23, 42, 0.96) 100%); z-index: 5; cursor: pointer;"
                    onclick="startStream()">
                    <div class="play-btn-circle mb-3 shadow-lg d-flex align-items-center justify-content-center"
                        style="width: 84px; height: 84px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 50%; border: 3px solid rgba(255, 255, 255, 0.25);">
                        <i class="bi bi-play-fill text-white" style="font-size: 46px; margin-left: 5px;"></i>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill small fw-medium">
                        <i class="bi bi-camera-video me-1"></i> Bấm để phát video
                    </span>
                </div>

                <!-- Hình ảnh luồng Camera / Snapshot fallback -->
                <img id="camera-feed"
                    src="{{ $station['camera_url'] ?? 'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=1000&q=80' }}"
                    alt="Camera Live Feed" class="camera-feed-img" style="display: none;">

                <!-- AI Bounding Box Cảnh Báo Sâu Bệnh (Mô phỏng) -->
                <div id="ai-detection-overlay" class="ai-detect-box" style="display: none;">
                    <span class="badge bg-danger text-white style-badge font-monospace" style="font-size: 10px;">AI DETECT:
                        SƯƠNG MAI (94.5%)</span>
                    <span class="text-white font-monospace text-end"
                        style="font-size: 9px; text-shadow: 0 1px 2px #000;">X:420 Y:350</span>
                </div>

                <!-- Overlay Top Left -->
                <div class="cam-overlay-top-left d-flex align-items-center gap-2">
                    <span id="stream-status-badge"
                        class="badge bg-secondary text-white px-2.5 py-1.5 d-flex align-items-center gap-1.5 shadow-sm">
                        <span class="live-dot" id="stream-status-dot" style="background-color: #94a3b8;"></span>
                        <span id="stream-status-text">SẴN SÀNG</span>
                    </span>
                    <span class="badge bg-dark text-white border border-secondary px-2.5 py-1.5 font-monospace">
                        {{ $station['code'] ?? 'TRẠM-01' }} | <span id="active-cam-label">Camera 01 (Toàn cảnh)</span>
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

            <!-- Thanh Điều Khiển Camera On-Demand -->
            <div class="card border-0 shadow-sm mb-3 bg-light">
                <div class="card-body py-2.5 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-dark border px-2.5 py-1.5 fw-bold">
                            <i class="bi bi-camera-video-fill text-primary me-1"></i> Chọn Camera:
                        </span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-primary active" id="btn-cam-1"
                                onclick="switchCamera('cam_1')">
                                <i class="bi bi-eye me-1"></i> Cam 01 (Toàn cảnh)
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btn-cam-2"
                                onclick="switchCamera('cam_2')">
                                <i class="bi bi-zoom-in me-1"></i> Cam 02 (Cận cảnh)
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2" id="stream-action-group">
                        <button type="button"
                            class="btn btn-sm btn-primary fw-medium shadow-sm d-flex align-items-center gap-1.5"
                            id="btn-start-stream" onclick="startStream()">
                            <i class="bi bi-play-fill fs-6"></i> Xem trực tiếp
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger fw-medium d-none" id="btn-stop-stream"
                            onclick="stopStream()">
                            <i class="bi bi-stop-fill fs-6"></i> Dừng phát
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary fw-medium d-none" id="btn-renew-stream"
                            onclick="renewStream()" title="Gia hạn thêm thời gian xem">
                            <i class="bi bi-arrow-clockwise me-1"></i> +3 Phút
                        </button>
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

                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Left', -5, 0)"
                            title="Xoay Trái"><i class="bi bi-arrow-left"></i></button>
                        <button type="button" class="ptz-btn-dir ptz-btn-center" onclick="takeSnapshot()"
                            title="Chụp ảnh tức thì"><i class="bi bi-camera-fill"></i></button>
                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Right', 5, 0)"
                            title="Xoay Phải"><i class="bi bi-arrow-right"></i></button>

                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Down-Left', -5, -5)"
                            title="Xuống - Trái"><i class="bi bi-arrow-down-left"></i></button>
                        <button type="button" class="ptz-btn-dir" onclick="moveCamera('Down', 0, -5)"
                            title="Xoay Xuống"><i class="bi bi-arrow-down"></i></button>
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
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script>
        const stationCode = "{{ $station['code'] ?? 'ST-PHUCHOA-01' }}";
        let activeCamId = 'cam_1';
        let isStreamActive = false;
        let hlsInstance = null;
        let countdownTimer = null;
        let remainingSeconds = 0;

        let currentPan = 45;
        let currentTilt = -15;
        let currentZoom = 2.5;
        let isRecording = false;

        document.addEventListener('DOMContentLoaded', () => {
            startClock();
            checkInitialStreamStatus();
        });

        // 0. Ghi log tương tác MQTT chi tiết
        function logMqttAction(actionName, reqInfo, resInfo) {
            console.groupCollapsed(`%c[MQTT CAMERA] ${actionName} - ${new Date().toLocaleTimeString('vi-VN')}`, 'background: #0f172a; color: #38bdf8; font-weight: bold; padding: 3px 8px; border-radius: 4px;');
            console.log('%c[GỬI LỆNH ĐẾN MQTT]', 'color: #3b82f6; font-weight: bold;', reqInfo);
            if (resInfo) {
                console.log('%c[MQTT / TRẠM TRẢ LẠI KẾT QUẢ]', 'color: #10b981; font-weight: bold;', resInfo);
            }
            console.groupEnd();
        }

        // 1. Chuyển đổi giữa Camera 01 và Camera 02
        function switchCamera(camId) {
            if (activeCamId === camId) return;
            activeCamId = camId;

            document.getElementById('btn-cam-1').className = camId === 'cam_1' ? 'btn btn-primary active' :
                'btn btn-outline-secondary';
            document.getElementById('btn-cam-2').className = camId === 'cam_2' ? 'btn btn-primary active' :
                'btn btn-outline-secondary';
            document.getElementById('active-cam-label').textContent = camId === 'cam_1' ? 'Camera 01 (Toàn cảnh)' :
                'Camera 02 (Cận cảnh)';

            if (isStreamActive) {
                stopStream(false);
                setTimeout(() => startStream(), 500);
            } else {
                checkInitialStreamStatus();
            }
        }

        // 2. Kích hoạt xem trực tiếp
        async function startStream(duration = 180) {
            const startBtn = document.getElementById('btn-start-stream');
            if (startBtn) startBtn.disabled = true;
            showToast('Đang kết nối luồng camera trực tiếp...', 'info');

            const reqPayload = {
                camera_id: activeCamId,
                duration_seconds: duration,
                quality: 'sub'
            };

            console.log(`%c[MQTT LỆNH ĐIỀU KHIỂN] Bắt đầu xem stream trạm ${stationCode}:`, 'color: #2563eb; font-weight: bold;', {
                topic: `khcn/stations/${stationCode}/camera/command`,
                action: 'START_STREAM',
                params: reqPayload
            });

            try {
                const res = await fetch(`/api/iot/stations/${stationCode}/camera/stream`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(reqPayload)
                });
                const result = await res.json();

                // Ghi log chi tiết: Lệnh gửi đi & Kết quả MQTT trả lại
                logMqttAction('START_STREAM', {
                    topic: result.command?.topic || `khcn/stations/${stationCode}/camera/command`,
                    action: 'START_STREAM',
                    command_id: result.command?.command_id,
                    payload_sent: result.command?.payload || reqPayload
                }, {
                    mqtt_published: result.command?.success ?? result.success,
                    mqtt_response_ack: result.ack || 'Đang xử lý ngầm trên Broker/Worker',
                    stream_info: result.stream
                });

                if (result.success && result.stream) {
                    initHlsPlayer(result.stream.hls_url);
                    startCountdown(duration);
                    showToast('Đã gửi lệnh, đang kết nối luồng video...', 'info');
                } else {
                    showToast('Không thể kết nối camera: ' + (result.message || 'Lỗi server'), 'error');
                }
            } catch (err) {
                console.error('[MQTT CAMERA ERROR] Lỗi gửi lệnh startStream:', err);
                showToast('Lỗi kết nối máy chủ camera', 'error');
            } finally {
                if (startBtn) startBtn.disabled = false;
            }
        }

        // 3. Khởi tạo trình phát video Hls.js
        function initHlsPlayer(hlsUrl) {
            const video = document.getElementById('camera-live-video');
            const standbyCover = document.getElementById('camera-standby-cover');
            const startBtn = document.getElementById('btn-start-stream');
            const stopBtn = document.getElementById('btn-stop-stream');
            const renewBtn = document.getElementById('btn-renew-stream');

            standbyCover.style.display = 'none';
            video.style.display = 'block';
            startBtn.classList.add('d-none');
            stopBtn.classList.remove('d-none');
            renewBtn.classList.remove('d-none');
            isStreamActive = true;

            if (hlsInstance) {
                hlsInstance.destroy();
            }

            if (Hls.isSupported()) {
                hlsInstance = new Hls({
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 30,
                    manifestLoadingMaxRetry: 5,
                    manifestLoadingRetryDelay: 1000
                });
                hlsInstance.loadSource(hlsUrl);
                hlsInstance.attachMedia(video);

                hlsInstance.on(Hls.Events.MANIFEST_PARSED, () => {
                    showToast('Đã kết nối camera trực tiếp!', 'success');
                    video.play().catch(e => console.log('Autoplay muted:', e));
                });

                let fatalRetryCount = 0;
                hlsInstance.on(Hls.Events.ERROR, (event, data) => {
                    if (data.fatal) {
                        switch (data.type) {
                            case Hls.ErrorTypes.NETWORK_ERROR:
                                fatalRetryCount++;
                                if (fatalRetryCount <= 4) {
                                    console.warn(`[HLS] Đang chờ trạm khởi tạo luồng video (lần ${fatalRetryCount}/4)...`);
                                    setTimeout(() => {
                                        if (hlsInstance) hlsInstance.startLoad();
                                    }, 1500);
                                } else {
                                    console.error('[HLS] Trạm chưa đẩy luồng video lên Media Server:', data);
                                    showToast('Chưa nhận được tín hiệu hình ảnh từ trạm camera.', 'error');
                                    stopStream(false);
                                }
                                break;
                            case Hls.ErrorTypes.MEDIA_ERROR:
                                console.warn('[HLS] Đang khắc phục lỗi giải mã media...');
                                hlsInstance.recoverMediaError();
                                break;
                            default:
                                showToast('Lỗi phát luồng video từ trạm.', 'error');
                                stopStream(false);
                                break;
                        }
                    }
                });
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                // Hỗ trợ Safari iOS/macOS
                video.src = hlsUrl;
                video.addEventListener('loadedmetadata', () => {
                    showToast('Đã kết nối camera trực tiếp!', 'success');
                    video.play().catch(e => console.log(e));
                });
            }
        }

        // 4. Dừng luồng phát video
        async function stopStream(callApi = true) {
            clearInterval(countdownTimer);
            if (hlsInstance) {
                hlsInstance.destroy();
                hlsInstance = null;
            }

            const video = document.getElementById('camera-live-video');
            const standbyCover = document.getElementById('camera-standby-cover');
            const startBtn = document.getElementById('btn-start-stream');
            const stopBtn = document.getElementById('btn-stop-stream');
            const renewBtn = document.getElementById('btn-renew-stream');
            const statusBadge = document.getElementById('stream-status-badge');
            const statusText = document.getElementById('stream-status-text');
            const statusDot = document.getElementById('stream-status-dot');

            video.pause();
            video.src = '';
            video.style.display = 'none';
            standbyCover.style.display = 'flex';

            startBtn.classList.remove('d-none');
            stopBtn.classList.add('d-none');
            renewBtn.classList.add('d-none');

            statusBadge.className =
                'badge bg-secondary text-white px-2.5 py-1.5 d-flex align-items-center gap-1.5 shadow-sm';
            statusDot.style.backgroundColor = '#94a3b8';
            statusText.textContent = 'SẴN SÀNG';
            isStreamActive = false;

            if (callApi) {
                showToast('Đã dừng phát video.', 'info');
                const reqPayload = { camera_id: activeCamId };
                console.log(`%c[MQTT LỆNH ĐIỀU KHIỂN] Dừng stream trạm ${stationCode}:`, 'color: #dc2626; font-weight: bold;', {
                    topic: `khcn/stations/${stationCode}/camera/command`,
                    action: 'STOP_STREAM',
                    params: reqPayload
                });

                try {
                    const res = await fetch(`/api/iot/stations/${stationCode}/camera/stop`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(reqPayload)
                    });
                    const result = await res.json();

                    logMqttAction('STOP_STREAM', {
                        topic: result.command?.topic || `khcn/stations/${stationCode}/camera/command`,
                        action: 'STOP_STREAM',
                        command_id: result.command?.command_id,
                        payload_sent: result.command?.payload || reqPayload
                    }, {
                        mqtt_published: result.command?.success ?? result.success,
                        mqtt_response_ack: result.ack || result
                    });
                } catch (e) {
                    console.error('[MQTT CAMERA ERROR] Lỗi dừng stream:', e);
                }
            }
        }

        function renewStream() {
            startStream(180);
        }

        // 5. Đồng hồ đếm ngược phiên xem
        function startCountdown(seconds) {
            clearInterval(countdownTimer);
            remainingSeconds = seconds;

            const statusBadge = document.getElementById('stream-status-badge');
            const statusText = document.getElementById('stream-status-text');
            const statusDot = document.getElementById('stream-status-dot');

            statusBadge.className = 'badge bg-danger text-white px-2.5 py-1.5 d-flex align-items-center gap-1.5 shadow-sm';
            statusDot.style.backgroundColor = '#ef4444';

            updateCountdownDisplay();

            countdownTimer = setInterval(() => {
                remainingSeconds--;
                if (remainingSeconds <= 0) {
                    clearInterval(countdownTimer);
                    stopStream(false);
                    showToast('Phiên phát trực tiếp đã kết thúc.', 'info');
                } else {
                    updateCountdownDisplay();
                }
            }, 1000);
        }

        function updateCountdownDisplay() {
            const m = Math.floor(remainingSeconds / 60).toString().padStart(2, '0');
            const s = (remainingSeconds % 60).toString().padStart(2, '0');
            document.getElementById('stream-status-text').textContent = `LIVE (${m}:${s})`;
        }

        // 6. Kiểm tra nếu luồng đang mở sẵn từ trước
        async function checkInitialStreamStatus() {
            try {
                const res = await fetch(`/api/iot/stations/${stationCode}/camera/status?camera_id=${activeCamId}`);
                const data = await res.json();
                if (data.active && data.remaining_seconds > 0 && data.stream) {
                    initHlsPlayer(data.stream.hls_url);
                    startCountdown(data.remaining_seconds);
                }
            } catch (e) {
                // Ignore
            }
        }

        // 7. Đồng hồ hệ thống góc phải
        function startClock() {
            const clockEl = document.getElementById('live-clock');
            setInterval(() => {
                const now = new Date();
                clockEl.textContent = now.toLocaleTimeString('vi-VN');
            }, 1000);
        }

        // 8. Di chuyển Camera qua API PTZ
        async function moveCamera(directionName, deltaPan, deltaTilt) {
            currentPan = Math.max(-180, Math.min(180, currentPan + deltaPan));
            currentTilt = Math.max(-45, Math.min(45, currentTilt + deltaTilt));

            document.getElementById('val-pan').textContent = currentPan.toFixed(1) + '°';
            document.getElementById('val-tilt').textContent = currentTilt.toFixed(1) + '°';

            const reqPayload = {
                camera_id: activeCamId,
                direction: directionName,
                speed: 5
            };

            console.log(`%c[MQTT LỆNH ĐIỀU KHIỂN] Điều khiển PTZ [${directionName}]:`, 'color: #059669; font-weight: bold;', {
                topic: `khcn/stations/${stationCode}/camera/command`,
                action: 'PTZ_CONTROL',
                params: reqPayload
            });

            try {
                const res = await fetch(`/api/iot/stations/${stationCode}/camera/ptz`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(reqPayload)
                });
                const result = await res.json();

                logMqttAction(`PTZ_CONTROL (${directionName})`, {
                    topic: result.command?.topic || `khcn/stations/${stationCode}/camera/command`,
                    action: 'PTZ_CONTROL',
                    command_id: result.command?.command_id,
                    payload_sent: result.command?.payload || reqPayload
                }, {
                    mqtt_published: result.command?.success ?? result.success,
                    mqtt_response_ack: result.ack || result
                });
            } catch (e) {
                console.error('[MQTT CAMERA ERROR] Lỗi điều khiển PTZ:', e);
            }
        }

        // 9. Thay đổi Zoom
        function updateZoom(val) {
            currentZoom = Math.max(1.0, Math.min(4.0, Math.round(parseFloat(val) * 10) / 10));
            document.getElementById('val-zoom').textContent = currentZoom.toFixed(1) + 'x';
            document.getElementById('zoom-val-badge').textContent = currentZoom.toFixed(1) + 'x';
        }

        function changeZoom(delta) {
            updateZoom(currentZoom + delta);
        }

        function applyPreset(name, pan, tilt, zoom) {
            currentPan = pan;
            currentTilt = tilt;
            document.getElementById('val-pan').textContent = currentPan.toFixed(1) + '°';
            document.getElementById('val-tilt').textContent = currentTilt.toFixed(1) + '°';
            updateZoom(zoom);
        }

        // 10. Chụp ảnh từ camera
        async function takeSnapshot() {
            showToast('Đang chụp ảnh...', 'info');
            const reqPayload = { camera_id: activeCamId };

            console.log(`%c[MQTT LỆNH ĐIỀU KHIỂN] Chụp ảnh snapshot camera:`, 'color: #7c3aed; font-weight: bold;', {
                topic: `khcn/stations/${stationCode}/camera/command`,
                action: 'CAPTURE_SNAPSHOT',
                params: reqPayload
            });

            try {
                const res = await fetch(`/api/iot/stations/${stationCode}/camera/snapshot`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(reqPayload)
                });
                const result = await res.json();

                logMqttAction('CAPTURE_SNAPSHOT', {
                    topic: result.command?.topic || `khcn/stations/${stationCode}/camera/command`,
                    action: 'CAPTURE_SNAPSHOT',
                    command_id: result.command?.command_id,
                    payload_sent: result.command?.payload || reqPayload
                }, {
                    mqtt_published: result.command?.success ?? result.success,
                    mqtt_response_ack: result.ack || result
                });

                if (result.success) {
                    showToast('Đã chụp ảnh thành công!', 'success');
                } else {
                    showToast('Không thể chụp ảnh lúc này', 'error');
                }
            } catch (err) {
                console.error('[MQTT CAMERA ERROR] Lỗi chụp ảnh:', err);
                showToast('Không thể chụp ảnh lúc này', 'error');
            }
        }

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
                    showToast('Đã lưu video clip ghi hình!', 'success');
                }, 10000);
            }
        }

        function runAiCropScan() {
            const overlay = document.getElementById('ai-detection-overlay');
            overlay.style.display = 'flex';
            showToast('Đã kích hoạt quét AI sâu bệnh trên luồng camera!', 'info');
        }

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
    </script>
@endpush
