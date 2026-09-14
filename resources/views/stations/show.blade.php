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

        .play-btn-circle:hover {
            transform: scale(1.1);
            box-shadow: 0 0 35px rgba(59, 130, 246, 0.6) !important;
        }

        /* Lưới 4 Camera (2x2 Grid View) */
        .camera-grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 12px;
        }

        @media (max-width: 768px) {
            .camera-grid-container {
                grid-template-columns: 1fr;
            }
        }

        .cam-cell {
            position: relative;
            background: #0b0f19;
            border-radius: 14px;
            border: 2px solid #1e293b;
            overflow: hidden;
            aspect-ratio: 16 / 9;
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .cam-cell:hover {
            border-color: #3b82f6;
        }

        .cam-cell.active-focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.35), 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .cam-cell-header {
            position: absolute;
            top: 8px;
            left: 8px;
            right: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            pointer-events: none;
        }

        .cam-cell-header * {
            pointer-events: auto;
        }

        .cam-cell-footer {
            position: absolute;
            bottom: 8px;
            left: 8px;
            right: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            pointer-events: none;
        }

        .cam-cell-footer * {
            pointer-events: auto;
        }

        .cam-cell-cover {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.82) 0%, rgba(10, 15, 26, 0.96) 100%);
            color: #ffffff;
            z-index: 5;
            transition: opacity 0.2s ease;
        }

        .cam-cell-cover:hover .play-btn-circle {
            transform: scale(1.1);
            box-shadow: 0 0 25px rgba(59, 130, 246, 0.6);
        }

        /* Chế độ xem phóng to 1 camera (Single View) */
        .camera-grid-container.single-mode .cam-cell {
            display: none;
        }

        .camera-grid-container.single-mode .cam-cell.active-focus {
            display: block;
            grid-column: span 2;
            aspect-ratio: 16 / 9;
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
        <!-- 1. CỘT BÊN TRÁI (8 COLS): LƯỚI 4 CAMERA LIVE STREAM -->
        <div class="col-lg-8">
            <!-- Header công cụ xem camera (Chuyển chế độ Lưới 4 cam / Phóng to & Nút Phát tất cả) -->
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-white text-dark border px-2.5 py-1.5 fw-bold shadow-sm">
                        <i class="bi bi-display text-primary me-1"></i> Chế độ hiển thị:
                    </span>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-primary active btn-mode-toggle" id="btn-mode-grid" onclick="setViewMode('grid')">
                            <i class="bi bi-grid-fill me-1"></i> 4 Camera (Lưới 2x2)
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-mode-toggle" id="btn-mode-single" onclick="setViewMode('single')">
                            <i class="bi bi-square me-1"></i> Phóng to 1 Cam
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-success fw-bold shadow-sm d-flex align-items-center gap-1.5" id="btn-start-all-streams" onclick="startAllStreams()">
                        <i class="bi bi-play-circle-fill"></i> Phát cả 4 Cam
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger fw-medium d-none" id="btn-stop-all-streams" onclick="stopAllStreams()">
                        <i class="bi bi-stop-circle-fill"></i> Dừng tất cả
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary fw-medium d-none" id="btn-renew-all-streams" onclick="renewAllStreams()" title="Gia hạn thêm thời gian xem cho các cam đang chạy">
                        <i class="bi bi-arrow-clockwise me-1"></i> +3 Phút
                    </button>
                </div>
            </div>

            <!-- Khung Lưới 4 Camera (Grid 2x2) -->
            <div class="camera-grid-container mb-3" id="camera-grid-box">
                @foreach (['cam_1' => 'Camera 1', 'cam_2' => 'Camera 2', 'cam_3' => 'Camera 3', 'cam_4' => 'Camera 4'] as $cId => $cName)
                    <div class="cam-cell {{ $cId === 'cam_1' ? 'active-focus' : '' }}" id="cam-cell-{{ $cId }}" onclick="focusCamera('{{ $cId }}')">
                        <video id="video-{{ $cId }}" class="w-100 h-100" playsinline controls autoplay muted style="display: none; object-fit: cover; background: #000;"></video>

                        <!-- Màn hình chờ phát video -->
                        <div id="standby-{{ $cId }}" class="cam-cell-cover" onclick="startSingleStream('{{ $cId }}')">
                            <div class="play-btn-circle mb-2 shadow-sm d-flex align-items-center justify-content-center"
                                style="width: 52px; height: 52px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 50%; border: 2px solid rgba(255, 255, 255, 0.25);">
                                <i class="bi bi-play-fill text-white fs-3" style="margin-left: 2px;"></i>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill" style="font-size: 11px;">
                                <i class="bi bi-camera-video me-1"></i> Bấm phát
                            </span>
                        </div>

                        <!-- Header Overlay -->
                        <div class="cam-cell-header">
                            <span class="badge bg-dark bg-opacity-75 text-white border border-secondary border-opacity-50 px-2 py-1 font-monospace" style="font-size: 11px;">
                                <span class="live-dot me-1" id="dot-{{ $cId }}" style="background-color: #94a3b8;"></span>
                                <span id="label-{{ $cId }}">{{ $cName }}</span>
                            </span>
                            <span class="badge bg-success px-2 py-1 focus-badge {{ $cId === 'cam_1' ? '' : 'd-none' }}" id="focus-badge-{{ $cId }}" style="font-size: 10px;">
                                <i class="bi bi-crosshair me-1"></i> Đang chọn PTZ
                            </span>
                        </div>

                        <!-- Footer Overlay -->
                        <div class="cam-cell-footer">
                            <span class="badge bg-black bg-opacity-75 text-white border border-dark px-2 py-1 font-monospace" style="font-size: 10px;" id="status-{{ $cId }}">
                                SẴN SÀNG
                            </span>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-xs btn-dark bg-opacity-75 border border-secondary text-white py-0.5 px-1.5"
                                    title="Phát/Dừng camera này" onclick="event.stopPropagation(); toggleStreamForCam('{{ $cId }}')">
                                    <i class="bi bi-power" id="pwr-{{ $cId }}"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-dark bg-opacity-75 border border-secondary text-white py-0.5 px-1.5"
                                    title="Toàn màn hình camera này" onclick="event.stopPropagation(); toggleFullscreenForCam('{{ $cId }}')">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Thanh Điều Khiển Chọn Camera PTZ & Thông Tin Phiên -->
            <div class="card border-0 shadow-sm mb-3 bg-light">
                <div class="card-body py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-dark border px-2.5 py-1.5 fw-bold">
                            <i class="bi bi-crosshair text-success me-1"></i> Camera điều khiển PTZ:
                        </span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-primary active btn-cam-select" id="btn-cam-1"
                                data-cam-id="cam_1" onclick="focusCamera('cam_1')">
                                <i class="bi bi-eye me-1"></i> Camera 1
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-cam-select" id="btn-cam-2"
                                data-cam-id="cam_2" onclick="focusCamera('cam_2')">
                                <i class="bi bi-zoom-in me-1"></i> Camera 2
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-cam-select" id="btn-cam-3"
                                data-cam-id="cam_3" onclick="focusCamera('cam_3')">
                                <i class="bi bi-camera me-1"></i> Camera 3
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-cam-select" id="btn-cam-4"
                                data-cam-id="cam_4" onclick="focusCamera('cam_4')">
                                <i class="bi bi-camera-video me-1"></i> Camera 4
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <span><i class="bi bi-compass text-warning me-1"></i> Pan: <strong id="val-pan">--°</strong> | Tilt: <strong id="val-tilt">--°</strong></span>
                        <span><i class="bi bi-zoom-in text-info me-1"></i> Zoom: <strong id="val-zoom">--x</strong></span>
                        <span class="ms-1 border-start ps-2"><i class="bi bi-clock me-1 text-info"></i> <strong id="live-clock">--:--:--</strong></span>
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
                        <button type="button" class="ptz-btn-dir" onmousedown="startContinuousPtz('Up-Left', -5, 5)"
                            onmouseup="stopContinuousPtz('Up-Left', -5, 5)"
                            onmouseleave="stopContinuousPtz('Up-Left', -5, 5)"
                            ontouchstart="startContinuousPtz('Up-Left', -5, 5)"
                            ontouchend="stopContinuousPtz('Up-Left', -5, 5)" onclick="moveCamera('Up-Left', -5, 5)"
                            title="Lên - Trái (Nhấp để nhích, giữ để quay liên tục)"><i
                                class="bi bi-arrow-up-left"></i></button>
                        <button type="button" class="ptz-btn-dir" onmousedown="startContinuousPtz('Up', 0, 5)"
                            onmouseup="stopContinuousPtz('Up', 0, 5)" onmouseleave="stopContinuousPtz('Up', 0, 5)"
                            ontouchstart="startContinuousPtz('Up', 0, 5)" ontouchend="stopContinuousPtz('Up', 0, 5)"
                            onclick="moveCamera('Up', 0, 5)" title="Xoay Lên (Nhấp để nhích, giữ để quay liên tục)"><i
                                class="bi bi-arrow-up"></i></button>
                        <button type="button" class="ptz-btn-dir" onmousedown="startContinuousPtz('Up-Right', 5, 5)"
                            onmouseup="stopContinuousPtz('Up-Right', 5, 5)"
                            onmouseleave="stopContinuousPtz('Up-Right', 5, 5)"
                            ontouchstart="startContinuousPtz('Up-Right', 5, 5)"
                            ontouchend="stopContinuousPtz('Up-Right', 5, 5)" onclick="moveCamera('Up-Right', 5, 5)"
                            title="Lên - Phải (Nhấp để nhích, giữ để quay liên tục)"><i
                                class="bi bi-arrow-up-right"></i></button>

                        <button type="button" class="ptz-btn-dir" onmousedown="startContinuousPtz('Left', -5, 0)"
                            onmouseup="stopContinuousPtz('Left', -5, 0)" onmouseleave="stopContinuousPtz('Left', -5, 0)"
                            ontouchstart="startContinuousPtz('Left', -5, 0)" ontouchend="stopContinuousPtz('Left', -5, 0)"
                            onclick="moveCamera('Left', -5, 0)" title="Xoay Trái (Nhấp để nhích, giữ để quay liên tục)"><i
                                class="bi bi-arrow-left"></i></button>
                        <button type="button" class="ptz-btn-dir ptz-btn-center" onclick="takeSnapshot()"
                            title="Chụp ảnh tức thì"><i class="bi bi-camera-fill"></i></button>
                        <button type="button" class="ptz-btn-dir" onmousedown="startContinuousPtz('Right', 5, 0)"
                            onmouseup="stopContinuousPtz('Right', 5, 0)" onmouseleave="stopContinuousPtz('Right', 5, 0)"
                            ontouchstart="startContinuousPtz('Right', 5, 0)" ontouchend="stopContinuousPtz('Right', 5, 0)"
                            onclick="moveCamera('Right', 5, 0)" title="Xoay Phải (Nhấp để nhích, giữ để quay liên tục)"><i
                                class="bi bi-arrow-right"></i></button>

                        <button type="button" class="ptz-btn-dir" onmousedown="startContinuousPtz('Down-Left', -5, -5)"
                            onmouseup="stopContinuousPtz('Down-Left', -5, -5)"
                            onmouseleave="stopContinuousPtz('Down-Left', -5, -5)"
                            ontouchstart="startContinuousPtz('Down-Left', -5, -5)"
                            ontouchend="stopContinuousPtz('Down-Left', -5, -5)" onclick="moveCamera('Down-Left', -5, -5)"
                            title="Xuống - Trái (Nhấp để nhích, giữ để quay liên tục)"><i
                                class="bi bi-arrow-down-left"></i></button>
                        <button type="button" class="ptz-btn-dir" onmousedown="startContinuousPtz('Down', 0, -5)"
                            onmouseup="stopContinuousPtz('Down', 0, -5)" onmouseleave="stopContinuousPtz('Down', 0, -5)"
                            ontouchstart="startContinuousPtz('Down', 0, -5)" ontouchend="stopContinuousPtz('Down', 0, -5)"
                            onclick="moveCamera('Down', 0, -5)"
                            title="Xoay Xuống (Nhấp để nhích, giữ để quay liên tục)"><i
                                class="bi bi-arrow-down"></i></button>
                        <button type="button" class="ptz-btn-dir" onmousedown="startContinuousPtz('Down-Right', 5, -5)"
                            onmouseup="stopContinuousPtz('Down-Right', 5, -5)"
                            onmouseleave="stopContinuousPtz('Down-Right', 5, -5)"
                            ontouchstart="startContinuousPtz('Down-Right', 5, -5)"
                            ontouchend="stopContinuousPtz('Down-Right', 5, -5)" onclick="moveCamera('Down-Right', 5, -5)"
                            title="Xuống - Phải (Nhấp để nhích, giữ để quay liên tục)"><i
                                class="bi bi-arrow-down-right"></i></button>
                    </div>

                    <!-- Điều chỉnh Mức Zoom -->
                    <div class="bg-light rounded-3 p-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label fw-bold mb-0 text-dark" style="font-size: 13px;">
                                <i class="bi bi-zoom-in text-info me-1"></i> Zoom
                            </label>

                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5"
                                    onmousedown="startContinuousPtz('ZOOM_OUT', 0, 0)"
                                    onmouseup="stopContinuousPtz('ZOOM_OUT', 0, 0)"
                                    onmouseleave="stopContinuousPtz('ZOOM_OUT', 0, 0)"
                                    ontouchstart="startContinuousPtz('ZOOM_OUT', 0, 0)"
                                    ontouchend="stopContinuousPtz('ZOOM_OUT', 0, 0)" onclick="changeZoom(-0.5)"
                                    title="Thu nhỏ (Zoom out) - Nhấp để nhích, giữ để zoom liên tục">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <span class="badge bg-primary fs-6 font-monospace px-3 py-1.5"
                                    id="zoom-val-badge">2.5x</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5"
                                    onmousedown="startContinuousPtz('ZOOM_IN', 0, 0)"
                                    onmouseup="stopContinuousPtz('ZOOM_IN', 0, 0)"
                                    onmouseleave="stopContinuousPtz('ZOOM_IN', 0, 0)"
                                    ontouchstart="startContinuousPtz('ZOOM_IN', 0, 0)"
                                    ontouchend="stopContinuousPtz('ZOOM_IN', 0, 0)" onclick="changeZoom(0.5)"
                                    title="Phóng to (Zoom in) - Nhấp để nhích, giữ để zoom liên tục">
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
                    <button type="button" class="btn btn-outline-secondary py-2 fw-medium opacity-50" id="btn-record"
                        onclick="toggleRecording()" disabled title="Vui lòng bật xem trực tiếp camera trước khi ghi hình">
                        <i class="bi bi-record-circle me-1"></i> Ghi hình
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
                            <div class="preset-card-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="fw-bold text-dark small text-truncate" title="{{ $preset->name }}">
                                        {{ $preset->name }}
                                    </div>
                                    <span
                                        class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace"
                                        style="font-size: 10px;">
                                        {{ strtoupper($preset->camera_id ?? 'cam_1') }}
                                    </span>
                                </div>
                                <div class="text-muted small font-monospace" style="font-size: 11px;">
                                    Pan: {{ number_format($preset->pan_angle, 1) }}° | Tilt:
                                    {{ number_format($preset->tilt_angle, 1) }}° |
                                    {{ number_format($preset->zoom_level, 1) }}x
                                </div>
                                @if ($preset->schedule)
                                    <div class="mt-1">
                                        <span
                                            class="badge bg-light text-secondary border font-monospace text-truncate d-inline-block"
                                            style="font-size: 10px; max-width: 100%;"
                                            title="{{ $preset->schedule->name }}">
                                            <i
                                                class="bi bi-clock me-1 text-primary"></i>{{ substr($preset->schedule->start_time, 0, 5) }}
                                            - {{ substr($preset->schedule->end_time, 0, 5) }}
                                        </span>
                                    </div>
                                @endif
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
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-muted border font-monospace"
                            id="snapshot-count">{{ count($recentSnapshots ?? []) }} Hình ảnh</span>
                        <a href="{{ route('iot.media', ['station_id' => $station['id']]) }}"
                            class="btn btn-outline-primary btn-sm py-0.5 px-2" style="font-size: 11px;"
                            title="Quản lý & Xóa ảnh trong kho media">
                            <i class="bi bi-folder2-open me-1"></i>Kho media
                        </a>
                    </div>
                </div>

                <div class="row g-2" id="snapshot-gallery">
                    @forelse($recentSnapshots ?? [] as $media)
                        @php
                            $devCode = $media->device->code ?? '';
                            $camTag = str_contains($devCode, 'cam_1')
                                ? 'Cam 01'
                                : (str_contains($devCode, 'cam_2')
                                    ? 'Cam 02'
                                    : (str_contains($devCode, 'cam_3')
                                        ? 'Cam 03'
                                        : (str_contains($devCode, 'cam_4')
                                            ? 'Cam 04'
                                            : 'Camera')));
                            $filePath = $media->file_path;
                            $imgUrl = str_starts_with($filePath, 'http') ? $filePath : asset('storage/' . $filePath);
                        @endphp
                        <div class="col-4">
                            <div class="border rounded-3 p-1 position-relative bg-light shadow-sm">
                                <span
                                    class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-1 px-1.5 py-0.5 font-monospace"
                                    style="font-size: 9px; z-index: 2;">
                                    {{ $camTag }}
                                </span>
                                <a href="{{ $imgUrl }}" target="_blank" title="Bấm để phóng to ảnh gốc">
                                    <img src="{{ $imgUrl }}" class="snapshot-thumb"
                                        alt="{{ $media->name ?? 'Snapshot' }}"
                                        style="height: 80px; width: 100%; object-fit: cover; border-radius: 6px;"
                                        onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center text-muted\' style=\'height:80px; font-size:10px;\'><i class=\'bi bi-image me-1\'></i> Lỗi ảnh</div>';">
                                </a>
                                <div class="text-muted font-monospace text-center mt-1 text-truncate"
                                    style="font-size: 10px;">
                                    {{ $media->created_at ? $media->created_at->format('H:i - d/m') : '' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-3 empty-snap-msg">
                            <i class="bi bi-camera me-1"></i> Chưa có ảnh chụp nào từ trạm.<br>Bấm nút máy ảnh ở bảng điều
                            khiển để chụp tức thì.
                        </div>
                    @endforelse
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
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Camera áp dụng <span class="text-danger">*</span></label>
                            <select name="camera_id" id="save-preset-cam-id" class="form-select" required>
                                <option value="cam_1">Camera 1</option>
                                <option value="cam_2">Camera 2</option>
                                <option value="cam_3">Camera 3</option>
                                <option value="cam_4">Camera 4</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Khung giờ lịch trình</label>
                            <select name="schedule_id" class="form-select" id="save-preset-schedule-id">
                                <option value="">-- Không gắn lịch trình --</option>
                                @foreach ($schedules ?? [] as $sch)
                                    <option value="{{ $sch->id }}" data-start="{{ $sch->start_time }}"
                                        data-end="{{ $sch->end_time }}" data-camera="{{ $sch->camera_id ?? 'all' }}">
                                        {{ $sch->name }} ({{ substr($sch->start_time, 0, 5) }} -
                                        {{ substr($sch->end_time, 0, 5) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
        const cameraIds = ['cam_1', 'cam_2', 'cam_3', 'cam_4'];
        const cameraLabels = {
            'cam_1': 'Camera 1',
            'cam_2': 'Camera 2',
            'cam_3': 'Camera 3',
            'cam_4': 'Camera 4'
        };

        let activeCamId = 'cam_1';
        let viewMode = 'grid'; // 'grid' | 'single'
        let hlsInstances = {};
        let streamRemaining = { cam_1: 0, cam_2: 0, cam_3: 0, cam_4: 0 };
        let streamTimers = { cam_1: null, cam_2: null, cam_3: null, cam_4: null };

        let currentPan = 0.0;
        let currentTilt = 0.0;
        let currentZoom = 1.0;
        
        // Quản lý Buffer phiên xem 3 phút độc lập cho từng camera
        let sessionBuffers = {
            cam_1: null,
            cam_2: null,
            cam_3: null,
            cam_4: null
        };

        function updateRecordButtonState(enabled) {
            const btn = document.getElementById('btn-record');
            if (!btn) return;

            const isLive = streamRemaining[activeCamId] > 0;
            const buf = sessionBuffers[activeCamId];

            if (isLive && enabled) {
                btn.disabled = false;
                btn.removeAttribute('disabled');

                if (buf && buf.isMarkedToSave) {
                    btn.className = 'btn btn-danger py-2 fw-medium animate-pulse';
                    btn.title = 'Đang ghi hình phiên này. Bấm vào đây để dừng và lưu video ngay!';
                    btn.innerHTML = '<i class="bi bi-stop-circle-fill me-1"></i> Đang ghi hình (Bấm lưu ngay)';
                } else {
                    btn.className = 'btn btn-outline-danger py-2 fw-medium';
                    btn.title = 'Bấm để ghi lại video phiên xem trực tiếp này';
                    btn.innerHTML = '<i class="bi bi-record-circle me-1"></i> Ghi hình';
                }
            } else {
                btn.disabled = true;
                btn.setAttribute('disabled', 'disabled');
                btn.className = 'btn btn-outline-secondary py-2 fw-medium opacity-50';
                btn.title = 'Vui lòng bật xem trực tiếp camera trước khi ghi hình';
                btn.innerHTML = '<i class="bi bi-record-circle me-1"></i> Ghi hình';
            }
        }

        function updatePtzDisplay(pan, tilt, zoom) {
            if (pan !== undefined && pan !== null) {
                currentPan = parseFloat(pan);
                const el = document.getElementById('val-pan');
                if (el) el.textContent = (currentPan >= 0 ? '+' : '') + currentPan.toFixed(1) + '°';
            }
            if (tilt !== undefined && tilt !== null) {
                currentTilt = parseFloat(tilt);
                const el = document.getElementById('val-tilt');
                if (el) el.textContent = (currentTilt >= 0 ? '+' : '') + currentTilt.toFixed(1) + '°';
            }
            if (zoom !== undefined && zoom !== null) {
                currentZoom = Math.max(1.0, parseFloat(zoom));
                const el = document.getElementById('val-zoom');
                if (el) el.textContent = currentZoom.toFixed(1) + 'x';
                const badge = document.getElementById('zoom-val-badge');
                if (badge) badge.textContent = currentZoom.toFixed(1) + 'x';
            }
        }

        async function fetchPtzStatus(camId) {
            try {
                const targetCam = camId || activeCamId;
                const res = await fetch(`/api/iot/stations/${stationCode}/camera/ptz?camera_id=${targetCam}`);
                const data = await res.json();
                if (data.success && data.ptz) {
                    updatePtzDisplay(data.ptz.pan, data.ptz.tilt, data.ptz.zoom);
                }
            } catch (e) {
                console.warn('[PTZ] Không thể lấy tọa độ PTZ từ camera:', e);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            startClock();
            updateRecordButtonState(false);
            checkInitialStreamStatus();
            fetchPtzStatus(activeCamId);
        });

        // 0. Ghi log tương tác MQTT chi tiết
        function logMqttAction(actionName, reqInfo, resInfo) {
            console.groupCollapsed(`%c[MQTT CAMERA] ${actionName} - ${new Date().toLocaleTimeString('vi-VN')}`,
                'background: #0f172a; color: #38bdf8; font-weight: bold; padding: 3px 8px; border-radius: 4px;');
            console.log('%c[GỬI LỆNH ĐẾN MQTT]', 'color: #3b82f6; font-weight: bold;', reqInfo);
            if (resInfo) {
                console.log('%c[MQTT / TRẠM TRẢ LẠI KẾT QUẢ]', 'color: #10b981; font-weight: bold;', resInfo);
            }
            console.groupEnd();
        }

        // 1. Chuyển đổi và Focus Camera để điều khiển PTZ & Chụp ảnh
        function focusCamera(camId) {
            activeCamId = camId;

            // Highlight ô camera trong grid
            cameraIds.forEach(id => {
                const cell = document.getElementById(`cam-cell-${id}`);
                const badge = document.getElementById(`focus-badge-${id}`);
                if (cell) {
                    if (id === camId) {
                        cell.classList.add('active-focus');
                    } else {
                        cell.classList.remove('active-focus');
                    }
                }
                if (badge) {
                    if (id === camId) {
                        badge.classList.remove('d-none');
                    } else {
                        badge.classList.add('d-none');
                    }
                }
            });

            // Đồng bộ trạng thái active của thanh nút chọn camera
            document.querySelectorAll('.btn-cam-select').forEach(btn => {
                const isSelected = btn.getAttribute('data-cam-id') === camId;
                btn.className = isSelected ? 'btn btn-primary active btn-cam-select' :
                    'btn btn-outline-secondary btn-cam-select';
            });

            // Lấy tọa độ PTZ thực tế của camera vừa chọn
            fetchPtzStatus(camId);

            // Cập nhật trạng thái nút Ghi hình theo camera đang focus
            const isLive = streamRemaining[camId] > 0;
            updateRecordButtonState(isLive);
        }

        // Chuyển đổi chế độ hiển thị: Lưới 4 cam (grid) hoặc Phóng to 1 cam (single)
        function setViewMode(mode) {
            viewMode = mode;
            const gridBox = document.getElementById('camera-grid-box');
            const btnGrid = document.getElementById('btn-mode-grid');
            const btnSingle = document.getElementById('btn-mode-single');

            if (mode === 'single') {
                gridBox.classList.add('single-mode');
                btnSingle.className = 'btn btn-primary active btn-mode-toggle';
                btnGrid.className = 'btn btn-outline-secondary btn-mode-toggle';
            } else {
                gridBox.classList.remove('single-mode');
                btnGrid.className = 'btn btn-primary active btn-mode-toggle';
                btnSingle.className = 'btn btn-outline-secondary btn-mode-toggle';
            }
        }

        // 2. Kích hoạt phát video cho 1 Camera cụ thể
        async function startSingleStream(camId, duration = 180) {
            showToast(`Đang kết nối ${cameraLabels[camId] || camId}...`, 'info');

            const statusEl = document.getElementById(`status-${camId}`);
            const dotEl = document.getElementById(`dot-${camId}`);
            if (statusEl) statusEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width: 8px; height: 8px;"></span> KHỞI TẠO...';
            if (dotEl) dotEl.style.backgroundColor = '#f59e0b';

            const reqPayload = {
                camera_id: camId,
                duration_seconds: duration,
                quality: 'sub'
            };

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

                logMqttAction(`START_STREAM (${camId})`, {
                    topic: result.command?.topic || `khcn/stations/${stationCode}/camera/command`,
                    action: 'START_STREAM',
                    command_id: result.command?.command_id,
                    payload_sent: result.command?.payload || reqPayload
                }, {
                    mqtt_published: result.command?.success ?? result.success,
                    mqtt_response_ack: result.ack || result,
                    stream_info: result.stream
                });

                if (result.success && result.stream) {
                    if (result.ptz && camId === activeCamId) {
                        updatePtzDisplay(result.ptz.pan, result.ptz.tilt, result.ptz.zoom);
                    }
                    startCountdownForCam(camId, duration);
                    updateStreamGlobalButtons();
                    if (camId === activeCamId) {
                        updateRecordButtonState(true);
                    }

                    // Nếu camera chưa có HLS player hoặc đang tắt, khởi tạo player mới sau 1s
                    // Nếu camera đang phát trực tiếp (trường hợp gia hạn), giữ nguyên luồng video để ghi hình liên tục không bị giật
                    if (!hlsInstances[camId]) {
                        setTimeout(() => {
                            initHlsPlayerForCam(camId, result.stream.hls_url);
                        }, 1000);
                    }

                    showToast(`Đang kết nối trực tiếp ${cameraLabels[camId]}...`, 'success');
                } else {
                    if (statusEl) statusEl.textContent = 'SẴN SÀNG';
                    if (dotEl) dotEl.style.backgroundColor = '#94a3b8';
                    showToast(`Không thể kết nối ${cameraLabels[camId]}: ` + (result.message || 'Lỗi server'), 'error');
                }
            } catch (err) {
                console.error(`[STREAM ERROR] ${camId}:`, err);
                if (statusEl) statusEl.textContent = 'LỖI';
                if (dotEl) dotEl.style.backgroundColor = '#ef4444';
                showToast(`Lỗi kết nối máy chủ camera ${camId}`, 'error');
            }
        }

        // 3. Khởi tạo HLS Player cho 1 camera
        function initHlsPlayerForCam(camId, hlsUrl) {
            const video = document.getElementById(`video-${camId}`);
            const standby = document.getElementById(`standby-${camId}`);
            if (!video || !standby) return;

            standby.style.display = 'none';
            video.style.display = 'block';

            if (hlsInstances[camId]) {
                hlsInstances[camId].destroy();
                hlsInstances[camId] = null;
            }

            if (Hls.isSupported()) {
                const hls = new Hls({
                    enableWorker: true,
                    lowLatencyMode: true,
                    backBufferLength: 30,
                    manifestLoadingMaxRetry: 20,
                    manifestLoadingRetryDelay: 1200,
                    manifestLoadingMaxRetryTimeout: 30000,
                    levelLoadingMaxRetry: 20,
                    levelLoadingRetryDelay: 1200,
                    fragLoadingMaxRetry: 20,
                    fragLoadingRetryDelay: 1200
                });

                hls.loadSource(hlsUrl);
                hls.attachMedia(video);

                hls.on(Hls.Events.MANIFEST_PARSED, () => {
                    video.play().catch(e => console.log('Autoplay muted:', e));
                    if (camId === activeCamId) updateRecordButtonState(true);
                    const statusEl = document.getElementById(`status-${camId}`);
                    const dotEl = document.getElementById(`dot-${camId}`);
                    if (dotEl) dotEl.style.backgroundColor = '#ef4444';
                });

                video.onplaying = () => {
                    startSessionBuffering(camId);
                };

                let retries = 0;
                hls.on(Hls.Events.ERROR, (event, data) => {
                    if (data.fatal) {
                        if (data.type === Hls.ErrorTypes.NETWORK_ERROR && retries < 25) {
                            retries++;
                            setTimeout(() => {
                                if (hlsInstances[camId] === hls) {
                                    // ĐẶC BIỆT QUAN TRỌNG: Khi Media Server trả về 404 do trạm đang khởi động kết nối RTSP/RTMP,
                                    // trong hls.js BẮT BUỘC phải gọi loadSource() để tải lại file playlist index.m3u8!
                                    // Nếu chỉ gọi startLoad(), hls.js sẽ bị kẹt và không tự phát lại được nếu không reload trang.
                                    if (data.details === Hls.ErrorDetails.MANIFEST_LOAD_ERROR || !hls.url) {
                                        hls.loadSource(hlsUrl);
                                    } else {
                                        hls.startLoad();
                                    }
                                }
                            }, 1200);
                        } else if (data.type === Hls.ErrorTypes.MEDIA_ERROR) {
                            hls.recoverMediaError();
                        } else {
                            console.warn(`[HLS ERROR] Luồng ${camId} gặp sự cố:`, data);
                            stopSingleStream(camId, false);
                        }
                    }
                });

                hlsInstances[camId] = hls;
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = hlsUrl;
                video.play().catch(e => console.log(e));
                video.onplaying = () => {
                    startSessionBuffering(camId);
                };
            }
        }

        // 4. Dừng luồng phát của 1 camera
        async function stopSingleStream(camId, callApi = true) {
            clearInterval(streamTimers[camId]);
            streamRemaining[camId] = 0;

            // Đóng gói và lưu video nếu session này được chọn lưu
            finalizeSessionBuffer(camId);

            if (hlsInstances[camId]) {
                hlsInstances[camId].destroy();
                hlsInstances[camId] = null;
            }

            const video = document.getElementById(`video-${camId}`);
            const standby = document.getElementById(`standby-${camId}`);
            const dot = document.getElementById(`dot-${camId}`);
            const status = document.getElementById(`status-${camId}`);

            if (video) {
                video.pause();
                video.src = '';
                video.style.display = 'none';
            }
            if (standby) standby.style.display = 'flex';
            if (dot) dot.style.backgroundColor = '#94a3b8';
            if (status) status.textContent = 'SẴN SÀNG';

            if (camId === activeCamId) {
                updateRecordButtonState(false);
            }

            updateStreamGlobalButtons();

            if (callApi) {
                try {
                    await fetch(`/api/iot/stations/${stationCode}/camera/stop`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ camera_id: camId })
                    });
                } catch (e) {
                    console.error('[STOP STREAM ERROR]', e);
                }
            }
        }

        function toggleStreamForCam(camId) {
            if (streamRemaining[camId] > 0) {
                stopSingleStream(camId, true);
                showToast(`Đã dừng phát ${cameraLabels[camId]}`, 'info');
            } else {
                startSingleStream(camId);
            }
        }

        // Bật / Dừng toàn bộ 4 Camera đồng thời
        async function startAllStreams(duration = 180) {
            showToast('Đang kích hoạt phát trực tiếp cả 4 Camera...', 'info');
            const startAllBtn = document.getElementById('btn-start-all-streams');
            if (startAllBtn) startAllBtn.disabled = true;

            for (let i = 0; i < cameraIds.length; i++) {
                const cId = cameraIds[i];
                startSingleStream(cId, duration);
                if (i < cameraIds.length - 1) {
                    await new Promise(r => setTimeout(r, 350));
                }
            }
            if (startAllBtn) startAllBtn.disabled = false;
        }

        async function stopAllStreams() {
            showToast('Đang dừng tất cả các luồng camera...', 'info');
            for (const cId of cameraIds) {
                if (streamRemaining[cId] > 0) {
                    stopSingleStream(cId, true);
                }
            }
        }

        function renewAllStreams() {
            cameraIds.forEach(cId => {
                if (streamRemaining[cId] > 0) {
                    startSingleStream(cId, 180);
                }
            });
            showToast('Đã gia hạn thêm 3 phút cho các luồng đang phát.', 'success');
        }

        function updateStreamGlobalButtons() {
            const anyActive = cameraIds.some(cId => streamRemaining[cId] > 0);
            const stopAllBtn = document.getElementById('btn-stop-all-streams');
            const renewAllBtn = document.getElementById('btn-renew-all-streams');

            if (stopAllBtn && renewAllBtn) {
                if (anyActive) {
                    stopAllBtn.classList.remove('d-none');
                    renewAllBtn.classList.remove('d-none');
                } else {
                    stopAllBtn.classList.add('d-none');
                    renewAllBtn.classList.add('d-none');
                }
            }
        }

        // 5. Đếm ngược phiên phát của từng camera
        function startCountdownForCam(camId, seconds) {
            clearInterval(streamTimers[camId]);
            streamRemaining[camId] = Math.max(0, Math.floor(Number(seconds) || 0));

            const dot = document.getElementById(`dot-${camId}`);
            const status = document.getElementById(`status-${camId}`);
            if (dot) dot.style.backgroundColor = '#ef4444';

            const updateDisplay = () => {
                const totalSec = Math.max(0, Math.floor(streamRemaining[camId]));
                const m = Math.floor(totalSec / 60).toString().padStart(2, '0');
                const s = (totalSec % 60).toString().padStart(2, '0');
                if (status) status.textContent = `LIVE (${m}:${s})`;
            };

            updateDisplay();

            streamTimers[camId] = setInterval(() => {
                streamRemaining[camId]--;
                if (streamRemaining[camId] <= 0) {
                    clearInterval(streamTimers[camId]);
                    stopSingleStream(camId, false);
                    showToast(`Phiên xem ${cameraLabels[camId]} đã kết thúc.`, 'info');
                } else {
                    updateDisplay();
                }
            }, 1000);
        }

        function toggleFullscreenForCam(camId) {
            const cell = document.getElementById(`cam-cell-${camId}`);
            if (!cell) return;
            if (!document.fullscreenElement) {
                cell.requestFullscreen().catch(err => {});
            } else {
                document.exitFullscreen();
            }
        }

        // 6. Kiểm tra nếu luồng đang mở sẵn từ trước
        async function checkInitialStreamStatus() {
            cameraIds.forEach(async (cId) => {
                try {
                    const res = await fetch(`/api/iot/stations/${stationCode}/camera/status?camera_id=${cId}`);
                    const data = await res.json();
                    if (data.ptz && cId === activeCamId) {
                        updatePtzDisplay(data.ptz.pan, data.ptz.tilt, data.ptz.zoom);
                    }
                    if (data.active && data.remaining_seconds > 0 && data.stream) {
                        initHlsPlayerForCam(cId, data.stream.hls_url);
                        startCountdownForCam(cId, data.remaining_seconds);
                        updateStreamGlobalButtons();
                        if (cId === activeCamId) {
                            updateRecordButtonState(true);
                        }
                    }
                } catch (e) {
                    // Ignore
                }
            });
        }

        // 7. Đồng hồ hệ thống góc phải
        function startClock() {
            const clockEl = document.getElementById('live-clock');
            setInterval(() => {
                const now = new Date();
                clockEl.textContent = now.toLocaleTimeString('vi-VN');
            }, 1000);
        }

        // 8. Di chuyển Camera qua API PTZ (Hỗ trợ cả nhấp bước & nhấn giữ liên tục)
        let ptzHoldTimer = null;
        let isPtzHolding = false;

        function startContinuousPtz(directionName, deltaPan, deltaTilt) {
            isPtzHolding = false;
            if (ptzHoldTimer) clearTimeout(ptzHoldTimer);
            ptzHoldTimer = setTimeout(() => {
                isPtzHolding = true;
                sendPtzRequest(directionName, true);
            }, 250);
        }

        function stopContinuousPtz(directionName, deltaPan, deltaTilt) {
            if (ptzHoldTimer) {
                clearTimeout(ptzHoldTimer);
                ptzHoldTimer = null;
            }
            if (isPtzHolding) {
                isPtzHolding = false;
                sendPtzRequest('STOP', false);
            }
        }

        async function sendPtzRequest(directionName, continuous = false, stepDuration = 0.5) {
            const reqPayload = {
                camera_id: activeCamId,
                direction: directionName,
                speed: 5,
                continuous: continuous,
                step_duration: stepDuration
            };

            console.log(`%c[MQTT LỆNH ĐIỀU KHIỂN] Điều khiển PTZ [${directionName}] (continuous: ${continuous}):`,
                'color: #059669; font-weight: bold;', {
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

                if (result.ptz) {
                    updatePtzDisplay(result.ptz.pan, result.ptz.tilt, result.ptz.zoom);
                } else if (result.ack?.data?.ptz) {
                    updatePtzDisplay(result.ack.data.ptz.pan, result.ack.data.ptz.tilt, result.ack.data.ptz.zoom);
                }
            } catch (e) {
                console.error('[MQTT CAMERA ERROR] Lỗi điều khiển PTZ:', e);
            }
        }

        async function moveCamera(directionName, deltaPan, deltaTilt) {
            // Nếu vừa hoàn thành một phiên nhấn giữ liên tục thì bỏ qua sự kiện click
            if (isPtzHolding) return;
            await sendPtzRequest(directionName, false, 0.4);
        }

        // 9. Thay đổi Zoom (gửi lệnh ZOOM_IN / ZOOM_OUT sang camera thật)
        function updateZoom(val) {
            currentZoom = Math.max(1.0, Math.min(10.0, Math.round(parseFloat(val) * 10) / 10));
            document.getElementById('val-zoom').textContent = currentZoom.toFixed(1) + 'x';
            document.getElementById('zoom-val-badge').textContent = currentZoom.toFixed(1) + 'x';
        }

        async function changeZoom(delta) {
            if (isPtzHolding) return;
            const dir = delta > 0 ? 'ZOOM_IN' : 'ZOOM_OUT';
            await sendPtzRequest(dir, false, 0.5);
        }

        function applyPreset(name, pan, tilt, zoom) {
            updatePtzDisplay(pan, tilt, zoom);
            showToast(`Đang chuyển camera tới góc chụp [${name}]...`, 'info');
        }

        // 10. Tự động tải ảnh về PC / Mobile của người dùng qua Browser
        async function triggerBrowserDownload(imageUrl, defaultName) {
            try {
                const response = await fetch(imageUrl);
                const blob = await response.blob();
                const blobUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = defaultName || `Snapshot_${activeCamId}_${Date.now()}.jpg`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => window.URL.revokeObjectURL(blobUrl), 1000);
            } catch (e) {
                const a = document.createElement('a');
                a.href = imageUrl;
                a.download = defaultName || `Snapshot_${activeCamId}.jpg`;
                a.target = '_blank';
                document.body.appendChild(a);
                a.click();
                a.remove();
            }
        }

        // 11. Chụp ảnh từ camera tức thời, tự tải về máy & hiển thị ngay lên Gallery
        async function takeSnapshot() {
            showToast('Đang chụp ảnh từ camera...', 'info');
            const reqPayload = {
                camera_id: activeCamId,
                quality: 'main'
            };

            const snapBtn = document.querySelector('.ptz-btn-center');
            if (snapBtn) snapBtn.classList.add('animate-pulse');

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
                    if (result.ptz) {
                        updatePtzDisplay(result.ptz.pan, result.ptz.tilt, result.ptz.zoom);
                    }
                    if (result.image_url) {
                        const filename = result.filename || `Snapshot_${stationCode}_${activeCamId}_${Date.now()}.jpg`;
                        triggerBrowserDownload(result.image_url, filename);
                        showToast('Đã chụp ảnh & tải về máy thành công!', 'success');

                        const gallery = document.getElementById('snapshot-gallery');
                        if (gallery) {
                            const emptyMsg = gallery.querySelector('.empty-snap-msg');
                            if (emptyMsg) emptyMsg.remove();

                            const now = new Date();
                            const nowStr = now.toLocaleTimeString('vi-VN', {
                                hour: '2-digit',
                                minute: '2-digit'
                            }) + ' - ' + now.toLocaleDateString('vi-VN', {
                                day: '2-digit',
                                month: '2-digit'
                            });
                            const camLabel = cameraLabels[activeCamId] || activeCamId;
                            const col = document.createElement('div');
                            col.className = 'col-4 animate-fade-in';
                            col.innerHTML = `
                                <div class="border rounded-3 p-1 position-relative bg-light shadow-sm">
                                    <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-1 px-1.5 py-0.5 font-monospace" style="font-size: 9px; z-index: 2;">
                                        ${camLabel.split(' ')[0]} ${camLabel.split(' ')[1] || ''}
                                    </span>
                                    <a href="${result.image_url}" target="_blank" title="Bấm để phóng to ảnh gốc">
                                        <img src="${result.image_url}" class="snapshot-thumb" alt="Ảnh mới chụp"
                                            style="height: 80px; width: 100%; object-fit: cover; border-radius: 6px;">
                                    </a>
                                    <div class="text-muted font-monospace text-center mt-1 text-truncate" style="font-size: 10px;">${nowStr} (Mới)</div>
                                </div>
                            `;
                            gallery.prepend(col);

                            const countBadge = document.getElementById('snapshot-count');
                            if (countBadge) {
                                countBadge.textContent = `${gallery.querySelectorAll('.col-4').length} Hình ảnh`;
                            }
                        }
                    } else {
                        showToast('Đã chụp ảnh thành công!', 'success');
                    }
                } else {
                    showToast(result.message || 'Không thể chụp ảnh lúc này', 'error');
                }
            } catch (err) {
                console.error('[MQTT CAMERA ERROR] Lỗi chụp ảnh:', err);
                showToast('Không thể chụp ảnh lúc này', 'error');
            } finally {
                if (snapBtn) snapBtn.classList.remove('animate-pulse');
            }
        }

        // =========================================================================
        // GHI ĐỆM VÀ LƯU TRỌN VẸN PHIÊN XEM LIVE 3 PHÚT (SESSION BUFFERING)
        // =========================================================================
        function startSessionBuffering(camId) {
            const video = document.getElementById(`video-${camId}`);
            if (!video) return;

            // Nếu camera này đang có bộ đệm hoạt động tốt thì không khởi tạo lại
            if (sessionBuffers[camId] && sessionBuffers[camId].recorder && sessionBuffers[camId].recorder.state === 'recording') {
                return;
            }

            let stream = null;
            try {
                if (typeof video.captureStream === 'function') {
                    stream = video.captureStream();
                } else if (typeof video.mozCaptureStream === 'function') {
                    stream = video.mozCaptureStream();
                }
            } catch (err) {
                console.warn(`[SESSION BUFFER] captureStream lỗi cho ${camId}:`, err);
                return;
            }

            if (!stream) return;

            let mimeType = '';
            let fileExt = 'webm';
            const candidateTypes = [
                'video/mp4;codecs=avc1',
                'video/mp4',
                'video/webm;codecs=vp9,opus',
                'video/webm;codecs=vp8,opus',
                'video/webm'
            ];

            for (const type of candidateTypes) {
                if (typeof MediaRecorder !== 'undefined' && typeof MediaRecorder.isTypeSupported === 'function' &&
                    MediaRecorder.isTypeSupported(type)) {
                    mimeType = type;
                    if (type.includes('mp4')) fileExt = 'mp4';
                    break;
                }
            }

            try {
                const chunks = [];
                const recorder = mimeType ? new MediaRecorder(stream, { mimeType }) : new MediaRecorder(stream);

                recorder.ondataavailable = function(e) {
                    if (e.data && e.data.size > 0) {
                        chunks.push(e.data);
                    }
                };

                sessionBuffers[camId] = {
                    recorder: recorder,
                    chunks: chunks,
                    mimeType: mimeType,
                    fileExt: fileExt,
                    isMarkedToSave: false,
                    startTime: new Date()
                };

                recorder.start(1000); // Lưu từng mẩu 1 giây để gom liên tục từ đầu phiên
                console.log(`[SESSION BUFFER] Bắt đầu ghi đệm phiên xem live từ 00:00 cho ${camId}`);

                if (camId === activeCamId) {
                    updateRecordButtonState(true);
                }
            } catch (e) {
                console.warn(`[SESSION BUFFER] Không thể khởi tạo MediaRecorder cho ${camId}:`, e);
            }
        }

        function finalizeSessionBuffer(camId, manualTrigger = false) {
            const buf = sessionBuffers[camId];
            if (!buf || !buf.recorder) return;

            const shouldSave = buf.isMarkedToSave;
            const recorder = buf.recorder;
            const chunks = buf.chunks;
            const mimeType = buf.mimeType || 'video/webm';
            const fileExt = buf.fileExt || 'webm';

            recorder.ondataavailable = null;
            recorder.onstop = null;

            try {
                if (recorder.state !== 'inactive') {
                    recorder.stop();
                }
            } catch (e) {}

            sessionBuffers[camId] = null;

            if (shouldSave && chunks && chunks.length > 0) {
                const blob = new Blob(chunks, { type: mimeType });
                const blobUrl = window.URL.createObjectURL(blob);

                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                const timeStr = `${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
                const filename = `Session_${stationCode}_${camId}_${timeStr}.${fileExt}`;

                // 1. Tải file về máy tính người dùng
                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => window.URL.revokeObjectURL(blobUrl), 4000);

                showToast(`Đã lưu video ghi hình (${filename})!`, 'success');

                // 2. Upload đồng bộ lên máy chủ
                uploadRecordedVideo(blob, filename, camId);
            }

            if (camId === activeCamId) {
                updateRecordButtonState(streamRemaining[camId] > 0);
            }
        }

        function toggleRecording() {
            const isLive = streamRemaining[activeCamId] > 0;
            const video = document.getElementById(`video-${activeCamId}`);

            if (!isLive || !video || video.paused || video.ended) {
                showToast(`Vui lòng bật xem trực tiếp ${cameraLabels[activeCamId] || activeCamId} trước!`, 'warning');
                updateRecordButtonState(false);
                return;
            }

            let buf = sessionBuffers[activeCamId];
            if (!buf || !buf.recorder) {
                startSessionBuffering(activeCamId);
                buf = sessionBuffers[activeCamId];
            }

            if (!buf) {
                showToast('Trình duyệt chưa khởi tạo xong bộ đệm video!', 'error');
                return;
            }

            if (!buf.isMarkedToSave) {
                // Người dùng bấm Ghi hình -> Đánh dấu ghi lại phiên này
                buf.isMarkedToSave = true;
                updateRecordButtonState(true);
                showToast(`Đang ghi hình ${cameraLabels[activeCamId]}! Dữ liệu video từ đầu phiên đến hết giờ xem sẽ được tự động lưu (hoặc bấm nút lần nữa để lưu ngay).`, 'success');
            } else {
                // Nếu bấm lại khi đang ghi hình -> Xuất và lưu ngay
                showToast(`Đang xuất và lưu video của ${cameraLabels[activeCamId]}...`, 'info');
                finalizeSessionBuffer(activeCamId, true);
                if (streamRemaining[activeCamId] > 0) {
                    setTimeout(() => startSessionBuffering(activeCamId), 300);
                }
            }
        }

        async function uploadRecordedVideo(blob, filename, camId = null) {
            try {
                const formData = new FormData();
                formData.append('video', blob, filename);
                formData.append('station_code', stationCode);
                formData.append('camera_id', camId || activeCamId);
                formData.append('captured_at', new Date().toISOString());

                const res = await fetch('/api/iot/camera/upload-video', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    console.log('[RECORD] Video đã được đồng bộ lên máy chủ:', data);
                }
            } catch (e) {
                console.warn('[RECORD] Không thể đồng bộ video lên máy chủ (đã lưu cục bộ):', e);
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

            const camSelect = document.getElementById('save-preset-cam-id');
            if (camSelect) camSelect.value = activeCamId || 'cam_1';

            // Tự động tìm và chọn lịch trình đang diễn ra (phù hợp thời gian hiện tại và camera)
            const scheduleSelect = document.getElementById('save-preset-schedule-id');
            if (scheduleSelect) {
                const now = new Date();
                const currentMinutes = now.getHours() * 60 + now.getMinutes();
                let matchedValue = '';

                for (let i = 0; i < scheduleSelect.options.length; i++) {
                    const opt = scheduleSelect.options[i];
                    const startStr = opt.dataset.start;
                    const endStr = opt.dataset.end;
                    const cam = opt.dataset.camera;
                    if (startStr && endStr) {
                        const [sh, sm] = startStr.split(':').map(Number);
                        const [eh, em] = endStr.split(':').map(Number);
                        const startMin = sh * 60 + sm;
                        const endMin = eh * 60 + em;

                        let isInRange = false;
                        if (startMin <= endMin) {
                            isInRange = currentMinutes >= startMin && currentMinutes <= endMin;
                        } else {
                            isInRange = currentMinutes >= startMin || currentMinutes <= endMin;
                        }

                        if (isInRange) {
                            if (cam === 'all' || cam === activeCamId) {
                                matchedValue = opt.value;
                                break;
                            } else if (!matchedValue) {
                                matchedValue = opt.value;
                            }
                        }
                    }
                }

                if (matchedValue) {
                    scheduleSelect.value = matchedValue;
                } else if (scheduleSelect.options.length === 2) {
                    scheduleSelect.selectedIndex = 1;
                }
            }

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
