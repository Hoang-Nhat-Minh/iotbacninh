@extends('layouts.labeler')

@section('title', 'Gán Nhãn Dữ Liệu Hình Ảnh')

@push('styles')
    <style>
        .modal {
            z-index: 1060 !important;
        }

        .modal-backdrop {
            z-index: 1050 !important;
        }

        .annotation-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            height: calc(100vh - 90px);
            min-height: 650px;
        }

        .annotation-header-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 8px 16px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .header-title-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-icon-badge {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
            flex-shrink: 0;
        }

        .header-title-text {
            font-weight: 700;
            font-size: 15px;
            color: #0f172a;
            letter-spacing: -0.2px;
            margin: 0;
            white-space: nowrap;
        }

        .task-selector-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .task-selector-box i.select-icon {
            position: absolute;
            left: 12px;
            color: #6366f1;
            font-size: 14px;
            pointer-events: none;
            z-index: 5;
        }

        .task-select-pill {
            padding-left: 32px !important;
            padding-right: 28px !important;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            color: #1e293b;
            font-weight: 600;
            font-size: 12px;
            max-width: 320px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .task-select-pill:hover,
        .task-select-pill:focus {
            background-color: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            color: #4f46e5;
        }

        .segmented-toggle-group {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            padding: 3px;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .segmented-btn {
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .segmented-btn:hover {
            color: #1e293b;
            background: rgba(255, 255, 255, 0.6);
        }

        .segmented-btn.active {
            background: #ffffff;
            color: #4f46e5;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }

        .segmented-count-badge {
            background: #e0e7ff;
            color: #4338ca;
            padding: 1px 7px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .segmented-btn.active .segmented-count-badge {
            background: #4f46e5;
            color: #ffffff;
        }

        .action-btn-hotkeys {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #0284c7;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }

        .action-btn-hotkeys:hover {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }

        .action-btn-dashboard {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .action-btn-dashboard:hover {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
        }

        .workspace-main {
            flex: 1;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            display: flex;
            background: #0f172a;
        }

        .floating-sidebar {
            position: absolute;
            top: 52px;
            bottom: 30px;
            width: 280px;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(8px);
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 100;
        }

        .sidebar-left {
            left: 12px;
            transform: translateX(-310px);
            opacity: 0;
            pointer-events: none;
        }

        .sidebar-left.active {
            transform: translateX(0);
            opacity: 1;
            pointer-events: auto;
        }

        .sidebar-right {
            right: 12px;
            width: 320px;
            transform: translateX(350px);
            opacity: 0;
            pointer-events: none;
        }

        .sidebar-right.active {
            transform: translateX(0);
            opacity: 1;
            pointer-events: auto;
        }

        .sidebar-header {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .gallery-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .gallery-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .gallery-item:hover,
        .gallery-item.active {
            background: rgba(79, 70, 229, 0.08);
            border-color: #4f46e5;
        }

        .gallery-thumb {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            object-fit: cover;
            background: #cbd5e1;
            flex-shrink: 0;
        }

        .canvas-area {
            flex: 1;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .canvas-toolbar {
            background: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 8px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            z-index: 50;
        }

        .toolbar-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
            flex-wrap: nowrap;
            overflow-x: auto;
            -ms-overflow-style: none;  
            scrollbar-width: none;  
        }

        .toolbar-row::-webkit-scrollbar,
        .canvas-toolbar::-webkit-scrollbar,
        #toolbar-label-palette::-webkit-scrollbar,
        .overflow-x-auto::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        .tool-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tool-btn {
            background: #334155;
            border: 1px solid #475569;
            color: #f1f5f9;
            padding: 4px 9px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
        }

        .tool-btn:hover:not(:disabled) {
            background: #475569;
            color: #ffffff;
            border-color: #64748b;
        }

        .tool-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .tool-btn.active {
            background: #4f46e5;
            color: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 12px rgba(79, 70, 229, 0.4);
        }

        .quick-label-pill {
            cursor: pointer;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 2px solid transparent;
            transition: all 0.15s ease;
            user-select: none;
        }

        .quick-label-pill.selected {
            border-color: #ffffff !important;
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.4);
            transform: scale(1.05);
        }

        .canvas-viewport {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
            cursor: crosshair;
        }

        .canvas-viewport.pan-mode {
            cursor: grab;
        }

        .canvas-viewport.pan-mode:active {
            cursor: grabbing;
        }

        #image-transform-wrapper {
            position: relative;
            transform-origin: center center;
            transition: transform 0.05s linear;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            max-height: 100%;
            height: 100%;
        }

        #target-image {
            max-height: 100%;
            height: 100%;
            width: auto;
            object-fit: contain;
            display: block;
            pointer-events: none;
        }

        #annotation-svg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: auto;
        }

        #shape-hover-tooltip {
            position: absolute;
            pointer-events: none;
            z-index: 150;
            display: none;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
            border: 1px solid #475569;
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 11.5px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            transform: translate(-50%, -100%) translateY(-10px);
            transition: opacity 0.15s ease;
            white-space: nowrap;
        }

        .canvas-statusbar {
            padding: 5px 14px;
            background: #0f172a;
            color: #94a3b8;
            font-size: 11px;
            font-family: monospace;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #334155;
            z-index: 50;
        }

        .region-item-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 8px;
        }

        .key-badge {
            background: #475569;
            color: #f8fafc;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 10px;
            font-family: monospace;
            margin-left: 3px;
            border: 1px solid #64748b;
        }
    </style>
@endpush

@section('content')
    <div class="annotation-container">
        
        <div class="annotation-header-bar">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                
                <div class="header-title-box">
                    <div class="header-icon-badge">
                        <i class="bi bi-bounding-box-circles"></i>
                    </div>
                    <h4 class="header-title-text">Gán Nhãn Hình Ảnh</h4>
                </div>

                <div class="vr bg-secondary opacity-25 d-none d-md-block" style="height: 22px;"></div>

                
                <div class="task-selector-box">
                    <i class="bi bi-folder-check select-icon"></i>
                    <select class="form-select form-select-sm task-select-pill"
                        onchange="location.href='?task_id=' + this.value">
                        @foreach ($allTasks as $t)
                            <option value="{{ $t->id }}" {{ $t->id == $selectedTaskId ? 'selected' : '' }}>
                                #TASK-{{ $t->id }}: {{ $t->name }} ({{ $t->project->name ?? 'Dự án' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                
                <div class="segmented-toggle-group">
                    <button type="button" class="segmented-btn" id="btn-toggle-left" onclick="toggleSidebar('left')">
                        <i class="bi bi-images text-indigo-500"></i> Kho Ảnh
                        <span class="segmented-count-badge">{{ count($images) }}</span>
                    </button>
                    <button type="button" class="segmented-btn" id="btn-toggle-right" onclick="toggleSidebar('right')">
                        <i class="bi bi-layers-fill text-indigo-500"></i> Vùng Khoanh
                        <span class="segmented-count-badge" id="btn-regions-count">0</span>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="action-btn-hotkeys" data-bs-toggle="modal" data-bs-target="#hotkeysModal">
                    <i class="bi bi-keyboard-fill"></i> Phím Tắt <span
                        class="badge bg-white text-info border border-info ms-1" style="font-size: 10px;">?</span>
                </button>
                <a href="{{ route('labeler.dashboard') }}" class="action-btn-dashboard">
                    <i class="bi bi-arrow-left-circle-fill"></i> Dashboard
                </a>
            </div>
        </div>

        
        <div class="workspace-main">

            
            <div class="floating-sidebar sidebar-left" id="sidebar-left">
                <div class="sidebar-header">
                    <div class="fw-bold text-dark small">
                        <i class="bi bi-images me-1 text-primary"></i> Kho Ảnh Nhiệm Vụ
                    </div>
                    <button type="button" class="btn-close btn-close-sm" onclick="toggleSidebar('left')"></button>
                </div>

                <div class="p-2 border-bottom">
                    <div class="mb-2">
                        <label class="form-label mb-1 text-muted-labeler" style="font-size: 11px;">Dự Án:</label>
                        <select class="form-select form-select-sm" onchange="location.href='?project_id=' + this.value">
                            @foreach ($projects as $p)
                                <option value="{{ $p->id }}" {{ $p->id == $selectedProjectId ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label mb-1 text-muted-labeler" style="font-size: 11px;">Nhiệm Vụ (Task):</label>
                        <select class="form-select form-select-sm"
                            onchange="location.href='?project_id={{ $selectedProjectId }}&task_id=' + this.value">
                            @foreach ($tasks as $t)
                                <option value="{{ $t->id }}" {{ $t->id == $selectedTaskId ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-between align-items-center small text-muted-labeler mt-2"
                        style="font-size: 11px;">
                        <span>Tiến độ Job:</span>
                        <span class="fw-bold text-primary" id="job-progress-badge">{{ $activeJob->progress ?? 0 }}%</span>
                    </div>
                    <div class="progress mt-1" style="height: 5px;">
                        <div class="progress-bar" id="job-progress-bar" style="width: {{ $activeJob->progress ?? 0 }}%;">
                        </div>
                    </div>
                </div>

                <div class="gallery-list">
                    @forelse($images as $img)
                        <div class="gallery-item {{ $selectedImage && $selectedImage->id == $img->id ? 'active' : '' }}"
                            onclick="location.href='?task_id={{ $selectedTaskId }}&image_id={{ $img->id }}'">
                            <img src="{{ $img->file_path }}" class="gallery-thumb" alt="Thumbnail">
                            <div style="min-width: 0;" class="flex-grow-1">
                                <div class="fw-semibold text-truncate small">{{ $img->file_name }}</div>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    @if ($img->status === 'labeled')
                                        <span class="badge badge-soft-success" style="font-size: 10px;">
                                            <i class="bi bi-check-circle-fill me-1"></i>Đã nhãn
                                        </span>
                                    @else
                                        <span class="badge badge-soft-secondary" style="font-size: 10px;">
                                            <i class="bi bi-dash-circle me-1"></i>Chưa nhãn
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted-labeler small">Không có ảnh nào.</div>
                    @endforelse
                </div>
            </div>

            
            <div class="canvas-area">
                
                <div class="canvas-toolbar">
                    
                    <div class="toolbar-row border-bottom border-secondary border-opacity-25 pb-1 mb-0.5">
                        <div class="tool-group w-100 justify-content-start">
                            <span class="text-secondary me-2 small fw-semibold flex-shrink-0"
                                style="font-size: 11.5px;">Công cụ vẽ:</span>
                            <button type="button" class="tool-btn active flex-shrink-0" id="tool-rect"
                                onclick="setTool('rect')" title="Vẽ Hình Chữ Nhật (Phím R)">
                                <i class="bi bi-bounding-box"></i> Bounding Box <span class="key-badge">R</span>
                            </button>
                            <button type="button" class="tool-btn flex-shrink-0" id="tool-poly"
                                onclick="setTool('poly')" title="Vẽ Đa Giác (Phím P)">
                                <i class="bi bi-pentagon"></i> Đa Giác <span class="key-badge">P</span>
                            </button>
                            <button type="button"
                                class="btn btn-success btn-sm py-1 px-2.5 fw-bold d-none flex-shrink-0 ms-1"
                                id="btn-finish-poly" onclick="finishPolygon()"
                                style="font-size: 11.5px; border-radius: 6px; box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);">
                                <i class="bi bi-check2-circle me-1"></i> Hoàn Thành (Enter)
                            </button>
                            <button type="button" class="tool-btn flex-shrink-0" id="tool-point"
                                onclick="setTool('point')" title="Đánh Dấu Điểm (Phím O)">
                                <i class="bi bi-geo-alt-fill"></i> Chấm Điểm <span class="key-badge">O</span>
                            </button>
                            <button type="button" class="tool-btn flex-shrink-0" id="tool-pan"
                                onclick="setTool('pan')" title="Di Chuyển Ảnh (Phím V)">
                                <i class="bi bi-hand-index-thumb"></i> Di Chuyển <span class="key-badge">V</span>
                            </button>
                        </div>
                    </div>

                    
                    <div class="toolbar-row border-bottom border-secondary border-opacity-25 pb-1 mb-0.5"
                        id="toolbar-label-palette">
                        <div class="d-flex align-items-center gap-3 w-100">
                            <span class="text-secondary me-1 small fw-semibold flex-shrink-0"
                                style="font-size: 11.5px;">Nhãn:</span>
                            <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0 overflow-x-auto py-0.5">
                                @foreach ($labels as $lbl)
                                    <div class="quick-label-pill flex-shrink-0" data-id="{{ $lbl->id }}"
                                        data-color="{{ $lbl->color }}" data-name="{{ $lbl->name }}"
                                        style="background: {{ $lbl->color }}25; color: #ffffff; border-color: {{ $lbl->color }};"
                                        onclick="selectLabel({{ $lbl->id }})">
                                        <span class="rounded-circle d-inline-block"
                                            style="width: 7px; height: 7px; background: {{ $lbl->color }};"></span>
                                        <span>{{ $lbl->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                            
                            <button type="button"
                                class="btn btn-sm btn-primary py-1 px-2.5 ms-auto fw-bold text-nowrap flex-shrink-0"
                                style="font-size: 11.5px; border-radius: 6px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border: none; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);"
                                data-bs-toggle="modal" data-bs-target="#manageLabelsModal"
                                title="Thêm / Sửa / Xóa & Chọn Màu Nhãn (CVAT Style)">
                                <i class="bi bi-gear-fill me-1"></i> Quản Lý Nhãn
                            </button>
                        </div>
                    </div>

                    
                    <div class="toolbar-row gap-3">
                        
                        <div class="tool-group flex-shrink-0 gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="tool-btn" id="btn-undo" onclick="undo()"
                                    title="Hoàn Tác (Phím Ctrl + Z)" disabled>
                                    <i class="bi bi-arrow-counterclockwise"></i> Undo <span
                                        class="key-badge">Ctrl+Z</span>
                                </button>
                                <button type="button" class="tool-btn" id="btn-redo" onclick="redo()"
                                    title="Làm Lại (Phím Ctrl + Y)" disabled>
                                    <i class="bi bi-arrow-clockwise"></i> Redo <span
                                        class="key-badge">Ctrl+Y</span>
                                </button>
                            </div>

                            <div class="vr bg-secondary mx-1 opacity-50" style="height: 18px;"></div>

                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="tool-btn" onclick="prevImage()"
                                    title="Chuyển đến ảnh phía trước (Phím PageUp / [)">
                                    <i class="bi bi-chevron-left"></i> Quay lại <span class="key-badge">[</span>
                                </button>
                                <button type="button" class="tool-btn" onclick="nextImage()"
                                    title="Chuyển đến ảnh kế tiếp (Phím PageDown / ])">
                                    Tiếp <span class="key-badge">]</span> <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>

                        
                        <div class="tool-group ms-auto flex-shrink-0 gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="tool-btn" onclick="zoomOut()" title="Thu Nhỏ (Phím -)">
                                    <i class="bi bi-zoom-out"></i>
                                </button>
                                <button type="button" class="tool-btn px-2" onclick="resetZoom()"
                                    title="Khôi Phục Tỉ Lệ 1:1">
                                    <span id="zoom-level-badge" class="text-warning fw-bold">100%</span>
                                </button>
                                <button type="button" class="tool-btn" onclick="zoomIn()"
                                    title="Phóng To Tối Đa 1000% (Phím +)">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                                <button type="button" class="tool-btn" onclick="rotateImage()"
                                    title="Xoay ảnh 90° (Phím Shift+R)">
                                    <i class="bi bi-arrow-clockwise"></i> Xoay <span class="key-badge">Shift+R</span>
                                </button>
                                <button type="button" class="tool-btn text-info" onclick="fitImage()"
                                    title="Reset vị trí & Căn vừa khung hình (Phím F)">
                                    <i class="bi bi-aspect-ratio me-1"></i> Fit Ảnh <span class="key-badge">F</span>
                                </button>
                            </div>

                            <div class="vr bg-secondary mx-1 opacity-50" style="height: 18px;"></div>

                            <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2.5 fw-bold flex-shrink-0"
                                onclick="clearTempShapes()" title="Xóa tất cả vùng gán nhãn trên ảnh hiện tại"
                                style="font-size: 11.5px; border-radius: 6px;">
                                <i class="bi bi-trash me-1"></i> Xóa Tất Cả Vùng
                            </button>
                        </div>
                    </div>
                </div>

                
                <div class="canvas-viewport" id="canvas-viewport">
                    @if ($selectedImage)
                        <div id="image-transform-wrapper">
                            <img src="{{ $selectedImage->file_path }}" id="target-image" alt="Target Image">
                            <svg id="annotation-svg-overlay" viewBox="0 0 1000 1000" preserveAspectRatio="none">
                                
                            </svg>
                        </div>
                        
                        <div id="shape-hover-tooltip"></div>
                    @else
                        <div class="text-center text-muted-labeler py-5">
                            <i class="bi bi-image fs-1 d-block mb-2"></i>
                            Vui lòng chọn một ảnh từ danh sách bên trái để bắt đầu gán nhãn.
                        </div>
                    @endif
                </div>

                
                <div class="canvas-statusbar">
                    <span id="status-tool-info">
                        <i class="bi bi-info-circle me-1"></i> Bounding Box Mode: Kéo thả chuột trên ảnh để tạo khung gán
                        nhãn
                    </span>
                    <span id="status-coords">X: 0% | Y: 0% | Zoom: 100% | Rotate: 0°</span>
                </div>
            </div>

            
            <div class="floating-sidebar sidebar-right" id="sidebar-right">
                <div class="sidebar-header">
                    <div class="fw-bold text-dark small">
                        <i class="bi bi-list-stars me-1 text-primary"></i> Vùng Đã Khoanh (<span
                            id="regions-count">0</span>)
                    </div>
                    <button type="button" class="btn-close btn-close-sm" onclick="toggleSidebar('right')"></button>
                </div>

                <div class="p-3 flex-grow-1 overflow-y-auto">
                    <div id="regions-list">
                        
                    </div>
                </div>

                <div class="p-3 border-top bg-light">
                    <button type="button" class="btn btn-primary-gradient w-100 py-2.5 fw-bold" id="btn-open-preview"
                        onclick="openPreviewModal()">
                        <i class="bi bi-floppy-fill me-1"></i> Lưu & Xem Trước
                    </button>
                </div>
            </div>

        </div>
    </div>

    
    <div class="modal fade" id="hotkeysModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-keyboard text-primary me-2"></i> Danh Sách Phím Tắt
                        Thao Tác Nhanh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead>
                            <tr class="table-light">
                                <th>Phím Tắt</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="key-badge fs-6">Ctrl + Z</span></td>
                                <td><b>Hoàn tác (Undo)</b> thao tác vẽ / xóa vùng gán nhãn vừa thực hiện</td>
                            </tr>
                            <tr>
                                <td><span class="key-badge fs-6">Ctrl + Y</span> / <span class="key-badge fs-6">Ctrl +
                                        Shift + Z</span></td>
                                <td><b>Làm lại (Redo)</b> thao tác vừa hoàn tác</td>
                            </tr>
                            <tr>
                                <td><span class="key-badge fs-6">R</span></td>
                                <td>Chuyển sang công cụ <b>Bounding Box</b> (Hình chữ nhật)</td>
                            </tr>
                            <tr>
                                <td><span class="key-badge fs-6">P</span></td>
                                <td>Chuyển sang công cụ <b>Đa Giác</b> (Polygon)</td>
                            </tr>
                            <tr>
                                <td><span class="key-badge fs-6">O</span></td>
                                <td>Chuyển sang công cụ <b>Chấm Điểm</b> (Point)</td>
                            </tr>
                            <tr>
                                <td><span class="key-badge fs-6">V</span></td>
                                <td>Chuyển sang công cụ <b>Di Chuyển / Pan Ảnh</b></td>
                            </tr>
                            <tr>
                                <td><span class="key-badge fs-6">F</span></td>
                                <td>Reset vị trí & <b>Fit Ảnh</b> vừa khung hình</td>
                            </tr>
                            <tr>
                                <td><span class="key-badge fs-6">Shift + R</span></td>
                                <td><b>Xoay ảnh 90°</b> theo chiều kim đồng hồ</td>
                            </tr>
                            <tr>
                                <td><span class="key-badge fs-6">Enter</span></td>
                                <td>Hoàn thành vẽ vùng <b>Đa Giác</b></td>
                            </tr>
                            <tr>
                                <td><span class="key-badge fs-6">Esc</span></td>
                                <td>Hủy thao tác vẽ vùng đang chọn</td>
                            </tr>
                            <tr>
                                <td><span class="key-badge fs-6">+</span> / <span class="key-badge fs-6">-</span></td>
                                <td>Phóng to / Thu nhỏ ảnh (Tối đa <b>1000%</b>)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="manageLabelsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-tags-fill text-primary me-2"></i> Quản Lý Bộ Nhãn Mô
                        Tả (CVAT Label Manager)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <form action="{{ route('labeler.annotation.labels.store') }}" method="POST"
                        class="p-3 mb-4 rounded bg-light border">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
                        <div class="fw-bold text-dark mb-2 small"><i class="bi bi-plus-circle-fill text-primary me-1"></i>
                            Thêm Nhãn Mô Tả Mới:</div>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label mb-1 small">Tên Nhãn Mô Tả <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm"
                                    placeholder="Ví dụ: Bệnh phấn trắng" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1 small">Chọn Màu</label>
                                <input type="color" name="color"
                                    class="form-control form-control-color form-control-sm w-100" value="#ef4444"
                                    title="Chọn màu hiển thị nhãn">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small">Ghi Chú</label>
                                <input type="text" name="description" class="form-control form-control-sm"
                                    placeholder="Mô tả tổn thương...">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                                    <i class="bi bi-plus-lg me-1"></i> Thêm Nhãn
                                </button>
                            </div>
                        </div>
                    </form>

                    
                    <div class="fw-bold text-dark mb-2 small"><i class="bi bi-list-task text-primary me-1"></i> Danh Sách
                        Nhãn Trong Dự Án:</div>
                    <div class="table-responsive" style="max-height: 250px;">
                        <table class="table table-sm table-hover align-middle small mb-0">
                            <thead>
                                <tr class="table-light">
                                    <th style="width: 50px;">Màu</th>
                                    <th>Tên Nhãn Mô Tả</th>
                                    <th>Mô Tả Chi Tiết</th>
                                    <th class="text-end" style="width: 140px;">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($labels as $lbl)
                                    <tr>
                                        <td>
                                            <span class="rounded-circle d-inline-block border"
                                                style="width: 22px; height: 22px; background: {{ $lbl->color }}; shadow: 0 1px 3px rgba(0,0,0,0.2);"></span>
                                        </td>
                                        <td class="fw-bold" style="color: {{ $lbl->color }};">{{ $lbl->name }}</td>
                                        <td class="text-muted-labeler">{{ $lbl->description ?: 'Chưa có mô tả' }}</td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editLabelModal{{ $lbl->id }}"
                                                    title="Sửa màu / tên nhãn">
                                                    <i class="bi bi-pencil-square text-warning"></i>
                                                </button>
                                                <form action="{{ route('labeler.annotation.labels.delete', $lbl->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Bạn có chắc muốn xóa nhãn {{ $lbl->name }}?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm"
                                                        title="Xóa nhãn">
                                                        <i class="bi bi-trash text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted-labeler">Chưa có nhãn nào
                                            trong Dự án này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    
    @foreach ($labels as $lbl)
        <div class="modal fade" id="editLabelModal{{ $lbl->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <form action="{{ route('labeler.annotation.labels.update', $lbl->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-1"></i> Sửa
                                Nhãn: {{ $lbl->name }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <label class="form-label mb-1 small">Tên Nhãn <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm"
                                    value="{{ $lbl->name }}" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label mb-1 small">Chọn Màu</label>
                                <input type="color" name="color"
                                    class="form-control form-control-color form-control-sm w-100"
                                    value="{{ $lbl->color }}">
                            </div>

                            <div class="mb-2">
                                <label class="form-label mb-1 small">Mô Tả</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2">{{ $lbl->description }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-warning btn-sm fw-bold">Lưu Nhãn</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-eye-fill text-primary me-2"></i> Bản Xem Trước & Xác
                        Nhận Gán Nhãn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="p-2 rounded bg-light text-center" style="border: 1px solid #e2e8f0;">
                                @if ($selectedImage)
                                    <img src="{{ $selectedImage->file_path }}" class="img-fluid rounded"
                                        style="max-height: 220px; object-fit: contain;">
                                @endif
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="fw-semibold mb-2 small">Danh sách vùng & nhãn sẽ được đồng bộ vào CSDL:</div>
                            <div class="table-responsive" style="max-height: 200px;">
                                <table class="table table-sm align-middle small mb-0">
                                    <thead>
                                        <tr class="text-muted-labeler">
                                            <th>Vùng #</th>
                                            <th>Loại</th>
                                            <th>Nhãn Mô Tả</th>
                                            <th>Ghi Chú</th>
                                        </tr>
                                    </thead>
                                    <tbody id="preview-table-body">
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Chỉnh sửa
                        tiếp</button>
                    <button type="button" class="btn btn-primary-gradient fw-bold px-4"
                        onclick="confirmSaveAnnotations()" id="btn-confirm-save">
                        <i class="bi bi-check-circle-fill me-1"></i> Xác Nhận & Đồng Bộ CSDL
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentTool = 'rect';
        let activeLabelId = null;
        let currentImageId = {{ $selectedImage->id ?? 'null' }};
        let currentJobId = {{ $activeJob->id ?? 'null' }};
        let
            annotations = []; 

        let historyStack = [];
        let redoStack = [];

        let activePolyPoints = [];

        let zoomLevel = 1.0;
        let panX = 0;
        let panY = 0;
        let rotationAngle = 0; 
        let isPanning = false;
        let startPanX = 0;
        let startPanY = 0;

        const savedAnnotations = @json($currentAnnotations);

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.modal').forEach(modalEl => {
                document.body.appendChild(modalEl);
            });

            const firstLabel = document.querySelector('.quick-label-pill');
            if (firstLabel) {
                firstLabel.click();
            }

            if (savedAnnotations && savedAnnotations.length > 0) {
                savedAnnotations.forEach((ann, idx) => {
                    const labelEl = document.querySelector(`.quick-label-pill[data-id="${ann.label_id}"]`);
                    const labelName = labelEl ? labelEl.dataset.name : 'Nhãn chưa xác định';
                    const labelColor = labelEl ? labelEl.dataset.color : '#ef4444';

                    let coords = ann.coordinates || {};
                    if (ann.annotation_type === 'polygon' && coords.points) {
                        coords.points = coords.points.map(p => ({
                            x: p.x <= 100 ? p.x * 10 : p.x,
                            y: p.y <= 100 ? p.y * 10 : p.y
                        }));
                    } else if (ann.annotation_type === 'bbox' && coords.x <= 100) {
                        coords.x = coords.x * 10;
                        coords.y = coords.y * 10;
                        coords.width = (coords.width || 10) * 10;
                        coords.height = (coords.height || 10) * 10;
                    } else if (ann.annotation_type === 'point' && coords.x <= 100) {
                        coords.x = coords.x * 10;
                        coords.y = coords.y * 10;
                    }

                    annotations.push({
                        id: ann.id || Date.now() + idx,
                        annotation_type: ann.annotation_type || 'bbox',
                        label_id: ann.label_id,
                        label_name: labelName,
                        label_color: labelColor,
                        coordinates: coords,
                        description: ann.description || ''
                    });
                });
            }

            renderShapes();
            renderRegionsList();
            setupKeyboardShortcuts();
            setupWheelZoom();
            updateUndoRedoButtons();
        });

        function saveState() {
            historyStack.push(JSON.stringify(annotations));
            redoStack = [];
            updateUndoRedoButtons();
        }

        function undo() {
            if (historyStack.length === 0) return;
            redoStack.push(JSON.stringify(annotations));
            const previousState = historyStack.pop();
            annotations = JSON.parse(previousState);
            renderShapes();
            renderRegionsList();
            updateUndoRedoButtons();
            hideShapeTooltip();
            autoSaveAnnotations();
        }

        function redo() {
            if (redoStack.length === 0) return;
            historyStack.push(JSON.stringify(annotations));
            const nextState = redoStack.pop();
            annotations = JSON.parse(nextState);
            renderShapes();
            renderRegionsList();
            updateUndoRedoButtons();
            hideShapeTooltip();
            autoSaveAnnotations();
        }

        function updateUndoRedoButtons() {
            const btnUndo = document.getElementById('btn-undo');
            const btnRedo = document.getElementById('btn-redo');
            if (btnUndo) btnUndo.disabled = (historyStack.length === 0);
            if (btnRedo) btnRedo.disabled = (redoStack.length === 0);
        }

        function toggleSidebar(side) {
            const sidebar = document.getElementById(`sidebar-${side}`);
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        }

        function setTool(tool) {
            currentTool = tool;
            document.querySelectorAll('.tool-btn').forEach(btn => btn.classList.remove('active'));
            const targetBtn = document.getElementById(`tool-${tool}`);
            if (targetBtn) targetBtn.classList.add('active');

            const viewport = document.getElementById('canvas-viewport');
            const infoEl = document.getElementById('status-tool-info');
            const btnFinishPoly = document.getElementById('btn-finish-poly');

            if (tool === 'pan') {
                viewport.classList.add('pan-mode');
            } else {
                viewport.classList.remove('pan-mode');
            }

            if (tool === 'rect') {
                infoEl.innerHTML =
                    '<i class="bi bi-info-circle me-1"></i> Bounding Box Mode: Kéo thả chuột trên ảnh để vẽ hình chữ nhật [Phím R]';
                btnFinishPoly.classList.add('d-none');
            } else if (tool === 'poly') {
                infoEl.innerHTML =
                    '<i class="bi bi-info-circle me-1"></i> Đa Giác Mode: Nhấp chuột chọn các đỉnh, Nhấn Enter hoặc nhấp đúp để kết thúc [Phím P]';
                btnFinishPoly.classList.remove('d-none');
            } else if (tool === 'point') {
                infoEl.innerHTML =
                    '<i class="bi bi-info-circle me-1"></i> Chấm Điểm Mode: Nhấp chuột để chọn điểm tọa độ [Phím O]';
                btnFinishPoly.classList.add('d-none');
            } else if (tool === 'pan') {
                infoEl.innerHTML =
                    '<i class="bi bi-info-circle me-1"></i> Di Chuyển Mode: Giữ & kéo chuột để di chuyển ảnh khi Zoom [Phím V]';
                btnFinishPoly.classList.add('d-none');
            }
        }

        function selectLabel(labelId) {
            activeLabelId = labelId;
            document.querySelectorAll('.quick-label-pill').forEach(pill => pill.classList.remove('selected'));
            const targets = document.querySelectorAll(`.quick-label-pill[data-id="${labelId}"]`);
            targets.forEach(t => t.classList.add('selected'));
        }

        function updateTransform() {
            const wrapper = document.getElementById('image-transform-wrapper');
            const badge = document.getElementById('zoom-level-badge');
            if (wrapper) {
                wrapper.style.transform = `scale(${zoomLevel}) translate(${panX}px, ${panY}px) rotate(${rotationAngle}deg)`;
            }
            if (badge) {
                badge.textContent = `${Math.round(zoomLevel * 100)}%`;
            }
            const coordsEl = document.getElementById('status-coords');
            if (coordsEl) {
                const currentText = coordsEl.textContent.split('| Zoom:')[0];
                coordsEl.textContent = `${currentText} | Zoom: ${Math.round(zoomLevel * 100)}% | Rotate: ${rotationAngle}°`;
            }
            renderShapes();
        }

        function zoomIn() {
            zoomLevel = Math.min(10.0, zoomLevel + 0.5);
            updateTransform();
        }

        function zoomOut() {
            zoomLevel = Math.max(0.5, zoomLevel - 0.5);
            updateTransform();
        }

        function resetZoom() {
            zoomLevel = 1.0;
            panX = 0;
            panY = 0;
            updateTransform();
        }

        function fitImage() {
            zoomLevel = 1.0;
            panX = 0;
            panY = 0;
            rotationAngle = 0;
            updateTransform();
        }

        const imagesList = @json($images->pluck('id')->toArray());
        const currentImgIdx = imagesList.indexOf(currentImageId);

        function prevImage() {
            if (currentImgIdx > 0) {
                const prevId = imagesList[currentImgIdx - 1];
                location.href = `?task_id={{ $selectedTaskId }}&image_id=${prevId}`;
            } else {
                alert('Đã ở ảnh đầu tiên trong Task!');
            }
        }

        function nextImage() {
            if (currentImgIdx < imagesList.length - 1) {
                const nextId = imagesList[currentImgIdx + 1];
                location.href = `?task_id={{ $selectedTaskId }}&image_id=${nextId}`;
            } else {
                alert('Đã ở ảnh cuối cùng trong Task!');
            }
        }

        function rotateImage() {
            rotationAngle = (rotationAngle + 90) % 360;
            updateTransform();
        }

        function setupWheelZoom() {
            const viewport = document.getElementById('canvas-viewport');
            if (!viewport) return;

            viewport.addEventListener('wheel', function(e) {
                e.preventDefault();
                if (e.deltaY < 0) {
                    zoomIn();
                } else {
                    zoomOut();
                }
            }, {
                passive: false
            });
        }

        function setupKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                    return;
                }

                const key = e.key.toUpperCase();

                if (e.key === 'PageUp' || e.key === '[') {
                    e.preventDefault();
                    prevImage();
                } else if (e.key === 'PageDown' || e.key === ']') {
                    e.preventDefault();
                    nextImage();
                } else if ((e.ctrlKey || e.metaKey) && key === 'Z') {
                    e.preventDefault();
                    if (e.shiftKey) {
                        redo();
                    } else {
                        undo();
                    }
                } else if ((e.ctrlKey || e.metaKey) && key === 'Y') {
                    e.preventDefault();
                    redo();
                } else if (e.shiftKey && key === 'R') {
                    e.preventDefault();
                    rotateImage();
                } else if (key === 'F') {
                    fitImage();
                } else if (key === 'R') {
                    setTool('rect');
                } else if (key === 'P') {
                    setTool('poly');
                } else if (key === 'O') {
                    setTool('point');
                } else if (key === 'V') {
                    setTool('pan');
                } else if (key === 'ENTER') {
                    if (currentTool === 'poly') finishPolygon();
                } else if (key === 'ESCAPE') {
                    activePolyPoints = [];
                    renderShapes();
                } else if (e.key === '+' || e.key === '=') {
                    zoomIn();
                } else if (e.key === '-') {
                    zoomOut();
                }
            });
        }

        const tooltipEl = document.getElementById('shape-hover-tooltip');

        function attachHoverTooltip(element, ann, index) {
            element.style.cursor = 'pointer';

            element.addEventListener('mouseenter', function(e) {
                element.setAttribute('fill-opacity', '0.65');
                element.setAttribute('stroke-width', (parseFloat(element.getAttribute('stroke-width')) || 2) * 1.5);

                if (tooltipEl) {
                    let typeBadge = ann.annotation_type.toUpperCase();
                    if (typeBadge === 'BBOX') typeBadge = 'Bounding Box';

                    let details = '';
                    if (ann.annotation_type === 'bbox') {
                        details = `W: ${ann.coordinates.width}px | H: ${ann.coordinates.height}px`;
                    } else if (ann.annotation_type === 'polygon') {
                        details = `${ann.coordinates.points ? ann.coordinates.points.length : 0} Đỉnh đa giác`;
                    } else if (ann.annotation_type === 'point') {
                        details = `Tọa độ: X:${ann.coordinates.x}, Y:${ann.coordinates.y}`;
                    }

                    tooltipEl.innerHTML = `
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge" style="background: ${ann.label_color}; font-size: 11px;">#${index + 1} ${ann.label_name}</span>
                            <span class="badge bg-secondary" style="font-size: 10px;">${typeBadge}</span>
                        </div>
                        <div class="fw-semibold text-light mb-1" style="font-size: 11px;">
                            ${ann.description ? `<i class="bi bi-chat-left-text me-1 text-info"></i> ${ann.description}` : '<i class="text-muted">Chưa có ghi chú</i>'}
                        </div>
                        <div class="text-muted-labeler" style="font-size: 10px;">${details}</div>
                    `;
                    tooltipEl.style.display = 'block';
                    updateTooltipPosition(e);
                }
            });

            element.addEventListener('mousemove', function(e) {
                updateTooltipPosition(e);
            });

            element.addEventListener('mouseleave', function() {
                element.setAttribute('fill-opacity', '0.3');
                element.setAttribute('stroke-width', element.getAttribute('data-orig-stroke') || '1');
                hideShapeTooltip();
            });
        }

        function updateTooltipPosition(e) {
            if (!tooltipEl) return;
            const viewport = document.getElementById('canvas-viewport');
            if (!viewport) return;
            const rect = viewport.getBoundingClientRect();

            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            tooltipEl.style.left = `${mouseX}px`;
            tooltipEl.style.top = `${mouseY}px`;
        }

        function hideShapeTooltip() {
            if (tooltipEl) {
                tooltipEl.style.display = 'none';
            }
        }

        const svg = document.getElementById('annotation-svg-overlay');
        let isDrawingRect = false;
        let startX = 0,
            startY = 0;

        if (svg) {
            svg.addEventListener('mousemove', function(e) {
                const rect = svg.getBoundingClientRect();
                const scaleX = 1000 / rect.width;
                const scaleY = 1000 / rect.height;
                const currentX = Math.round((e.clientX - rect.left) * scaleX);
                const currentY = Math.round((e.clientY - rect.top) * scaleY);

                const coordsEl = document.getElementById('status-coords');
                if (coordsEl) {
                    coordsEl.textContent =
                        `X: ${Math.round(currentX / 10)}% | Y: ${Math.round(currentY / 10)}% | Zoom: ${Math.round(zoomLevel * 100)}% | Rotate: ${rotationAngle}°`;
                }

                if (isDrawingRect && currentTool === 'rect') {
                    if (annotations.length > 0) {
                        const lastAnn = annotations[annotations.length - 1];
                        if (lastAnn.annotation_type === 'bbox') {
                            const x = Math.min(startX, currentX);
                            const y = Math.min(startY, currentY);
                            const width = Math.max(5, Math.abs(currentX - startX));
                            const height = Math.max(5, Math.abs(currentY - startY));

                            lastAnn.coordinates.x = x;
                            lastAnn.coordinates.y = y;
                            lastAnn.coordinates.width = width;
                            lastAnn.coordinates.height = height;
                            renderShapes();
                        }
                    }
                }

                if (isPanning && currentTool === 'pan') {
                    panX += (e.clientX - startPanX) / zoomLevel;
                    panY += (e.clientY - startPanY) / zoomLevel;
                    startPanX = e.clientX;
                    startPanY = e.clientY;
                    updateTransform();
                }
            });

            svg.addEventListener('mousedown', function(e) {
                if (e.button !== 0) return;

                if (currentTool === 'pan') {
                    isPanning = true;
                    startPanX = e.clientX;
                    startPanY = e.clientY;
                    return;
                }

                if (!activeLabelId) {
                    alert('Vui lòng chọn 1 Nhãn Mô Tả trên thanh công cụ trước khi vẽ!');
                    return;
                }

                const rect = svg.getBoundingClientRect();
                const scaleX = 1000 / rect.width;
                const scaleY = 1000 / rect.height;
                const x = Math.round((e.clientX - rect.left) * scaleX);
                const y = Math.round((e.clientY - rect.top) * scaleY);

                const activeLabelEl = document.querySelector(`.quick-label-pill[data-id="${activeLabelId}"]`);
                const labelName = activeLabelEl ? activeLabelEl.dataset.name : '';
                const labelColor = activeLabelEl ? activeLabelEl.dataset.color : '#ef4444';

                if (currentTool === 'rect') {
                    saveState(); 
                    isDrawingRect = true;
                    startX = x;
                    startY = y;

                    annotations.push({
                        id: Date.now(),
                        annotation_type: 'bbox',
                        label_id: activeLabelId,
                        label_name: labelName,
                        label_color: labelColor,
                        coordinates: {
                            x: startX,
                            y: startY,
                            width: 10,
                            height: 10
                        },
                        description: ''
                    });
                    renderShapes();
                    renderRegionsList();
                } else if (currentTool === 'point') {
                    saveState(); 
                    annotations.push({
                        id: Date.now(),
                        annotation_type: 'point',
                        label_id: activeLabelId,
                        label_name: labelName,
                        label_color: labelColor,
                        coordinates: {
                            x,
                            y
                        },
                        description: ''
                    });
                    renderShapes();
                    renderRegionsList();
                    autoSaveAnnotations();
                } else if (currentTool === 'poly') {
                    activePolyPoints.push({
                        x,
                        y
                    });
                    renderShapes();
                }
            });

            svg.addEventListener('mouseup', function() {
                if (isDrawingRect) {
                    isDrawingRect = false;
                    autoSaveAnnotations();
                }
                isPanning = false;
            });

            svg.addEventListener('dblclick', function(e) {
                e.preventDefault();
                if (currentTool === 'poly') {
                    finishPolygon();
                }
            });
        }

        function finishPolygon() {
            if (activePolyPoints.length < 3) {
                alert('Vui lòng nhấp ít nhất 3 điểm trên hình để tạo đa giác!');
                return;
            }

            const activeLabelEl = document.querySelector(`.quick-label-pill[data-id="${activeLabelId}"]`);
            const labelName = activeLabelEl ? activeLabelEl.dataset.name : '';
            const labelColor = activeLabelEl ? activeLabelEl.dataset.color : '#ef4444';

            saveState(); 

            annotations.push({
                id: Date.now(),
                annotation_type: 'polygon',
                label_id: activeLabelId,
                label_name: labelName,
                label_color: labelColor,
                coordinates: {
                    points: [...activePolyPoints]
                },
                description: ''
            });

            activePolyPoints = [];
            renderShapes();
            renderRegionsList();
            autoSaveAnnotations();
        }

        function renderShapes() {
            if (!svg) return;
            svg.innerHTML = '';

            const dotRadius = Math.max(0.8, 2.2 / zoomLevel);
            const dotStroke = Math.max(0.3, 0.8 / zoomLevel);
            const lineStroke = Math.max(0.8, 2.0 / zoomLevel);
            const fontSize = Math.max(10, Math.round(24 / zoomLevel));

            annotations.forEach((ann, index) => {
                const color = ann.label_color || '#ef4444';

                if (ann.annotation_type === 'bbox') {
                    const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                    rect.setAttribute('x', ann.coordinates.x);
                    rect.setAttribute('y', ann.coordinates.y);
                    rect.setAttribute('width', ann.coordinates.width);
                    rect.setAttribute('height', ann.coordinates.height);
                    rect.setAttribute('fill', `${color}33`);
                    rect.setAttribute('stroke', color);
                    rect.setAttribute('stroke-width', lineStroke);
                    rect.setAttribute('data-orig-stroke', lineStroke);
                    rect.setAttribute('vector-effect', 'non-scaling-stroke');

                    attachHoverTooltip(rect, ann, index);
                    svg.appendChild(rect);
                } else if (ann.annotation_type === 'point') {
                    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    circle.setAttribute('cx', ann.coordinates.x);
                    circle.setAttribute('cy', ann.coordinates.y);
                    circle.setAttribute('r', dotRadius * 1.8);
                    circle.setAttribute('fill', color);
                    circle.setAttribute('stroke', '#ffffff');
                    circle.setAttribute('stroke-width', dotStroke);
                    circle.setAttribute('data-orig-stroke', dotStroke);

                    attachHoverTooltip(circle, ann, index);
                    svg.appendChild(circle);
                } else if (ann.annotation_type === 'polygon' && ann.coordinates.points) {
                    const pointsStr = ann.coordinates.points.map(p => `${p.x},${p.y}`).join(' ');
                    const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
                    polygon.setAttribute('points', pointsStr);
                    polygon.setAttribute('fill', `${color}40`);
                    polygon.setAttribute('stroke', color);
                    polygon.setAttribute('stroke-width', lineStroke);
                    polygon.setAttribute('data-orig-stroke', lineStroke);
                    polygon.setAttribute('vector-effect', 'non-scaling-stroke');

                    attachHoverTooltip(polygon, ann, index);
                    svg.appendChild(polygon);

                    ann.coordinates.points.forEach(p => {
                        const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                        dot.setAttribute('cx', p.x);
                        dot.setAttribute('cy', p.y);
                        dot.setAttribute('r', dotRadius);
                        dot.setAttribute('fill', color);
                        dot.setAttribute('stroke', '#ffffff');
                        dot.setAttribute('stroke-width', dotStroke);
                        dot.style.pointerEvents = 'none';
                        svg.appendChild(dot);
                    });
                }
            });

            if (activePolyPoints.length > 0) {
                const activeLabelEl = document.querySelector(`.quick-label-pill[data-id="${activeLabelId}"]`);
                const activeColor = activeLabelEl ? activeLabelEl.dataset.color : '#ef4444';

                if (activePolyPoints.length >= 2) {
                    const pointsStr = activePolyPoints.map(p => `${p.x},${p.y}`).join(' ');
                    const polyline = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
                    polyline.setAttribute('points', pointsStr);
                    polyline.setAttribute('fill', `${activeColor}22`);
                    polyline.setAttribute('stroke', activeColor);
                    polyline.setAttribute('stroke-width', lineStroke);
                    polyline.setAttribute('stroke-dasharray', '6,3');
                    polyline.setAttribute('vector-effect', 'non-scaling-stroke');
                    svg.appendChild(polyline);
                }

                activePolyPoints.forEach(p => {
                    const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                    dot.setAttribute('cx', p.x);
                    dot.setAttribute('cy', p.y);
                    dot.setAttribute('r', dotRadius);
                    dot.setAttribute('fill', activeColor);
                    dot.setAttribute('stroke', '#ffffff');
                    dot.setAttribute('stroke-width', dotStroke);
                    dot.style.pointerEvents = 'none';
                    svg.appendChild(dot);
                });
            }
        }

        function renderRegionsList() {
            const listEl = document.getElementById('regions-list');
            const countEl = document.getElementById('regions-count');
            const btnCountEl = document.getElementById('btn-regions-count');
            if (!listEl) return;

            if (countEl) countEl.textContent = annotations.length;
            if (btnCountEl) btnCountEl.textContent = annotations.length;

            listEl.innerHTML = '';

            if (annotations.length === 0) {
                listEl.innerHTML =
                    `<div class="text-center py-4 text-muted-labeler small">Chưa khoanh vùng nào. Dùng công cụ để bắt đầu.</div>`;
                return;
            }

            annotations.forEach((ann, idx) => {
                const item = document.createElement('div');
                item.className = 'region-item-box';
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="badge" style="background: ${ann.label_color}; color: #fff; font-size: 11px;">
                            #${idx + 1} [${ann.annotation_type.toUpperCase()}] ${ann.label_name}
                        </span>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="deleteRegion(${idx})" title="Xóa vùng này">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                    <div>
                        <input type="text" class="form-control form-control-sm" placeholder="Ghi chú mô tả vùng khoanh..." 
                            value="${ann.description || ''}" 
                            oninput="updateRegionNote(${idx}, this.value)"
                            style="font-size: 12px;">
                    </div>
                `;
                listEl.appendChild(item);
            });
        }

        function updateRegionNote(idx, text) {
            if (annotations[idx]) {
                annotations[idx].description = text;
                autoSaveAnnotations();
            }
        }

        function deleteRegion(idx) {
            saveState(); 
            annotations.splice(idx, 1);
            renderShapes();
            renderRegionsList();
            hideShapeTooltip();
            autoSaveAnnotations();
        }

        function clearTempShapes() {
            if (confirm('Bạn có chắc muốn xóa tất cả các vùng đã khoanh trên ảnh này?')) {
                saveState(); 
                annotations = [];
                activePolyPoints = [];
                renderShapes();
                renderRegionsList();
                hideShapeTooltip();
                autoSaveAnnotations();
            }
        }

        let autoSaveTimer = null;
        function autoSaveAnnotations() {
            clearTimeout(autoSaveTimer);

            const coordsEl = document.getElementById('status-coords');
            if (coordsEl) {
                coordsEl.innerHTML = `<span class="text-warning fw-bold"><i class="bi bi-cloud-arrow-up-fill me-1"></i> Đang tự động lưu CSDL...</span>`;
            }

            autoSaveTimer = setTimeout(() => {
                if (!currentImageId) return;

                fetch('/labeler/annotation/save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            image_id: currentImageId,
                            job_id: currentJobId,
                            annotations: annotations
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (coordsEl) {
                                coordsEl.innerHTML = `<span class="text-success fw-bold me-2"><i class="bi bi-cloud-check-fill me-1"></i> Đã tự động lưu CSDL</span> | Zoom: ${Math.round(zoomLevel * 100)}% | Rotate: ${rotationAngle}°`;
                            }

                            const progressBadge = document.getElementById('job-progress-badge');
                            const progressBar = document.getElementById('job-progress-bar');
                            if (data.job_progress !== undefined) {
                                if (progressBadge) progressBadge.textContent = `${data.job_progress}%`;
                                if (progressBar) progressBar.style.width = `${data.job_progress}%`;
                            }

                            const currentImgItem = document.querySelector(`.gallery-item.active`);
                            if (currentImgItem) {
                                const badgeContainer = currentImgItem.querySelector(`.badge`);
                                if (badgeContainer) {
                                    if (data.image_status === 'labeled') {
                                        badgeContainer.className = 'badge badge-soft-success';
                                        badgeContainer.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Đã nhãn`;
                                    } else {
                                        badgeContainer.className = 'badge badge-soft-secondary';
                                        badgeContainer.innerHTML = `<i class="bi bi-dash-circle me-1"></i>Chưa nhãn`;
                                    }
                                }
                            }
                        }
                    })
                    .catch(err => {
                        console.error('AutoSave Error:', err);
                        if (coordsEl) {
                            coordsEl.innerHTML = `<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Lỗi tự động lưu CSDL!</span>`;
                        }
                    });
            }, 300);
        }

        function openPreviewModal() {
            if (!currentImageId) {
                alert('Vui lòng chọn ảnh trước!');
                return;
            }

            const tbody = document.getElementById('preview-table-body');
            tbody.innerHTML = '';

            if (annotations.length === 0) {
                tbody.innerHTML =
                    `<tr><td colspan="4" class="text-center text-muted-labeler py-3">Chưa có vùng khoanh nào trên ảnh.</td></tr>`;
            } else {
                annotations.forEach((ann, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>#${idx + 1}</td>
                        <td><span class="badge badge-soft-secondary">${ann.annotation_type}</span></td>
                        <td><span class="fw-bold" style="color: ${ann.label_color};">${ann.label_name}</span></td>
                        <td class="text-muted-labeler">${ann.description || '<i class="text-muted">Chưa có ghi chú</i>'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        }

        function confirmSaveAnnotations() {
            const btn = document.getElementById('btn-confirm-save');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Đang đồng bộ...`;

            fetch('/labeler/annotation/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        image_id: currentImageId,
                        job_id: currentJobId,
                        annotations: annotations
                    })
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Xác Nhận & Đồng Bộ CSDL`;

                    if (data.success) {
                        const modalEl = document.getElementById('previewModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        alert('Đồng bộ thành công! Tiến độ Job đã được cập nhật.');
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + (data.message || 'Không thể lưu.'));
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Xác Nhận & Đồng Bộ CSDL`;
                    alert('Lỗi kết nối máy chủ!');
                    console.error(err);
                });
        }
    </script>
@endpush