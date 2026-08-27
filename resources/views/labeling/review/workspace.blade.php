@extends('layouts.labeler')

@section('title', 'Kiểm Tra Chéo Nhãn #JOB-' . $job->id)

@push('styles')
    <style>
        /* Force Modals above Canvas Layers & Backdrop */
        .modal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }

        /* Fullscreen Layout: Canvas takes 100% space */
        .review-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            height: calc(100vh - 90px);
            min-height: 650px;
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

        /* Floating Sidebars (Overlays on Top of Canvas) */
        .floating-sidebar {
            position: absolute;
            top: 52px;
            bottom: 30px;
            width: 300px;
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
            transform: translateX(-330px);
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
            width: 340px;
            transform: translateX(370px);
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

        /* 100% Full Canvas Workspace */
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
            min-height: 52px;
            background: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 6px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
            z-index: 50;
        }

        .tool-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .tool-btn {
            background: #334155;
            border: 1px solid #475569;
            color: #f1f5f9;
            padding: 5px 11px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .tool-btn:hover:not(:disabled) {
            background: #475569;
            color: #ffffff;
        }

        .tool-btn.active {
            background: #4f46e5;
            color: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 12px rgba(79, 70, 229, 0.4);
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

        #review-svg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: auto;
        }

        /* Modern Hover Info Card / Tooltip */
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

        /* Issue Pins on Canvas */
        .issue-pin {
            position: absolute;
            transform: translate(-50%, -100%);
            cursor: pointer;
            z-index: 80;
            font-size: 20px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6));
            transition: transform 0.2s ease;
        }

        .issue-pin:hover {
            transform: translate(-50%, -120%) scale(1.25);
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
    <div class="review-container">
        <!-- Top Bar Control Header -->
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-check text-success me-2"></i> Kiểm Trả Chéo & Nghiệm Thu #JOB-{{ $job->id }}
                </h4>

                <!-- Toggle Floating Sidebars Buttons -->
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" id="btn-toggle-left" onclick="toggleSidebar('left')">
                        <i class="bi bi-images me-1"></i> Kho Ảnh Task <span class="badge bg-secondary ms-1">{{ count($images) }}</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btn-toggle-right" onclick="toggleSidebar('right')">
                        <i class="bi bi-exclamation-octagon-fill text-warning me-1"></i> Báo Lỗi Issue (<span id="btn-issues-count">{{ count($allJobIssues) }}</span>)
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-success btn-sm fw-bold px-3" onclick="openFinishJobModal()">
                    <i class="bi bi-check-all me-1"></i> Chuyển Stage & Đánh Giá
                </button>
                <a href="{{ route('labeler.review') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Danh Sách Job
                </a>
            </div>
        </div>

        <!-- Main Workspace Stage (100% Full Width Canvas + Floating Overlays) -->
        <div class="workspace-main">

            <!-- Floating Sidebar Trái: Kho Ảnh Nhiệm Vụ & Thông Tin Task -->
            <div class="floating-sidebar sidebar-left" id="sidebar-left">
                <div class="sidebar-header">
                    <div class="fw-bold text-dark small">
                        <i class="bi bi-images me-1 text-primary"></i> Kho Ảnh Nhiệm Vụ #TASK-{{ $task->id }}
                    </div>
                    <button type="button" class="btn-close btn-close-sm" onclick="toggleSidebar('left')"></button>
                </div>

                <div class="p-3 border-bottom bg-light">
                    <div class="fw-bold text-dark small mb-1">{{ $task->name }}</div>
                    <div class="text-muted-labeler small mb-2" style="font-size: 11.5px;">Dự Án: <strong>{{ $project->name }}</strong></div>
                    
                    <div class="d-flex justify-content-between align-items-center small text-muted-labeler" style="font-size: 11px;">
                        <span>Tiến độ Gán Nhãn:</span>
                        <span class="fw-bold text-primary">{{ $job->progress }}%</span>
                    </div>
                    <div class="progress mt-1" style="height: 5px;">
                        <div class="progress-bar bg-success" style="width: {{ $job->progress }}%;"></div>
                    </div>
                </div>

                <div class="gallery-list">
                    @forelse($images as $img)
                        <div class="gallery-item {{ $selectedImage && $selectedImage->id == $img->id ? 'active' : '' }}"
                            onclick="location.href='?image_id={{ $img->id }}'">
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

            <!-- Canvas Stage Area (100% Full Width) -->
            <div class="canvas-area">
                <!-- Toolbar Header Bar -->
                <div class="canvas-toolbar">
                    <div class="tool-group">
                        <button type="button" class="tool-btn active" id="tool-view" onclick="setReviewTool('view')" title="Xem Nét Vẽ Annotator">
                            <i class="bi bi-eye-fill"></i> Hiển Thị Nhãn
                        </button>
                        <button type="button" class="tool-btn" id="tool-issue" onclick="setReviewTool('issue')" title="Nhấp chuột lên ảnh để đánh dấu lỗi Issue">
                            <i class="bi bi-flag-fill text-warning"></i> Báo Lỗi Issue
                        </button>
                        <button type="button" class="tool-btn" id="tool-pan" onclick="setReviewTool('pan')" title="Di Chuyển Ảnh (Phím V)">
                            <i class="bi bi-hand-index-thumb"></i> Di Chuyển <span class="key-badge">V</span>
                        </button>
                    </div>

                    <!-- Zoom, Rotate, Fit & Image Navigation Controls -->
                    <div class="tool-group">
                        <button type="button" class="tool-btn" onclick="prevImage()" title="Chuyển đến ảnh phía trước (Phím PageUp / [)">
                            <i class="bi bi-chevron-left"></i> Lùi
                        </button>
                        <button type="button" class="tool-btn" onclick="nextImage()" title="Chuyển đến ảnh kế tiếp (Phím PageDown / ])">
                            Tới <i class="bi bi-chevron-right"></i>
                        </button>

                        <div class="vr bg-secondary mx-1"></div>

                        <button type="button" class="tool-btn" onclick="zoomOut()" title="Thu Nhỏ (Phím -)">
                            <i class="bi bi-zoom-out"></i>
                        </button>
                        <button type="button" class="tool-btn" onclick="resetZoom()" title="Khôi Phục Tỉ Lệ 1:1">
                            <span id="zoom-level-badge" class="text-warning">100%</span>
                        </button>
                        <button type="button" class="tool-btn" onclick="zoomIn()" title="Phóng To Tối Đa 1000% (Phím +)">
                            <i class="bi bi-zoom-in"></i>
                        </button>
                        <button type="button" class="tool-btn" onclick="rotateImage()" title="Xoay ảnh 90° (Phím Shift+R)">
                            <i class="bi bi-arrow-clockwise"></i> Xoay <span class="key-badge">Shift+R</span>
                        </button>
                        <button type="button" class="tool-btn text-info" onclick="fitImage()" title="Reset vị trí & Căn vừa khung hình (Phím F)">
                            <i class="bi bi-aspect-ratio"></i> Fit Ảnh <span class="key-badge">F</span>
                        </button>
                    </div>
                </div>

                <!-- Canvas Viewport Stage -->
                <div class="canvas-viewport" id="canvas-viewport">
                    @if ($selectedImage)
                        <div id="image-transform-wrapper">
                            <img src="{{ $selectedImage->file_path }}" id="target-image" alt="Target Image">
                            <svg id="review-svg-overlay" viewBox="0 0 1000 1000" preserveAspectRatio="none">
                                <!-- Rendered SVG Shapes -->
                            </svg>
                            <!-- Container cho các ghim báo lỗi Issue Pins -->
                            <div id="issue-pins-container">
                                @foreach ($imageIssues as $iss)
                                    @php
                                        $c = is_array($iss->coordinates) ? $iss->coordinates : json_decode($iss->coordinates, true);
                                        $x = $c['x'] ?? 50;
                                        $y = $c['y'] ?? 50;
                                    @endphp
                                    <div class="issue-pin" style="left: {{ $x / 10 }}%; top: {{ $y / 10 }}%;"
                                        title="Lỗi #{{ $iss->id }}: [{{ strtoupper($iss->issue_type) }}] {{ $iss->description }}">
                                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <!-- Floating Hover Tooltip Card -->
                        <div id="shape-hover-tooltip"></div>
                    @else
                        <div class="text-center text-muted-labeler py-5">
                            <i class="bi bi-image fs-1 d-block mb-2"></i>
                            Vui lòng chọn một ảnh từ danh sách bên trái để kiểm tra chéo.
                        </div>
                    @endif
                </div>

                <!-- Statusbar -->
                <div class="canvas-statusbar">
                    <span id="status-tool-info">
                        <i class="bi bi-info-circle me-1"></i> Review Mode: Rê chuột lên vùng khoanh để kiểm tra nhãn Annotator đã gán
                    </span>
                    <span id="status-coords">X: 0% | Y: 0% | Zoom: 100% | Rotate: 0°</span>
                </div>
            </div>

            <!-- Floating Sidebar Phải: Danh Sách Lỗi Issue & Đánh Giá -->
            <div class="floating-sidebar sidebar-right" id="sidebar-right">
                <div class="sidebar-header">
                    <div class="fw-bold text-dark small">
                        <i class="bi bi-exclamation-octagon-fill me-1 text-warning"></i> Danh Sách Báo Lỗi Issue (<span id="issues-count">{{ count($allJobIssues) }}</span>)
                    </div>
                    <button type="button" class="btn-close btn-close-sm" onclick="toggleSidebar('right')"></button>
                </div>

                <div class="p-3 flex-grow-1 overflow-y-auto">
                    <div class="fw-bold text-dark small mb-2">Lỗi phát hiện trên ảnh này:</div>
                    @forelse($imageIssues as $iss)
                        <div class="p-2 mb-2 rounded bg-light border" style="font-size: 12px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <span class="badge bg-danger text-uppercase">{{ $iss->issue_type }}</span>
                                    <span class="text-muted-labeler ms-1" style="font-size: 10px;">#ISSUE-{{ $iss->id }}</span>
                                </div>
                                <form action="{{ route('labeler.review.issue.delete', $iss->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa báo lỗi Issue này?');" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0" title="Xóa lỗi này">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="text-dark">{{ $iss->description ?: 'Không có ghi chú' }}</div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted-labeler small">Không có báo lỗi nào trên ảnh này.</div>
                    @endforelse

                    <hr>

                    <div class="fw-bold text-dark small mb-2">Tất cả lỗi trong Job #{{ $job->id }}:</div>
                    <div class="list-group list-group-flush small">
                        @forelse($allJobIssues as $iss)
                            <div class="list-group-item px-2 py-1.5 d-flex justify-content-between align-items-center">
                                <a href="?image_id={{ $iss->image_id }}" class="text-decoration-none text-dark flex-grow-1 min-w-0 me-2">
                                    <span class="badge bg-warning text-dark me-1">{{ $iss->issue_type }}</span>
                                    <span class="text-truncate d-inline-block align-middle" style="max-width: 140px;">{{ $iss->description }}</span>
                                </a>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge badge-soft-secondary" style="font-size: 10px;">Ảnh #{{ $iss->image_id }}</span>
                                    <form action="{{ route('labeler.review.issue.delete', $iss->id) }}" method="POST" onsubmit="return confirm('Xóa báo lỗi Issue này?');" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0 ms-1" title="Xóa lỗi">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-2 text-muted-labeler" style="font-size: 11px;">Job chưa có báo lỗi nào.</div>
                        @endforelse
                    </div>
                </div>

                <div class="p-3 border-top bg-light">
                    <button type="button" class="btn btn-success w-100 py-2.5 fw-bold" onclick="openFinishJobModal()">
                        <i class="bi bi-check-circle-fill me-1"></i> Nghiệm Thu & Đổi Stage
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Đánh Giá Lỗi Issue -->
    <div class="modal fade" id="issueBoxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('labeler.review.issue') }}" method="POST">
                    @csrf
                    <input type="hidden" name="job_id" value="{{ $job->id }}">
                    <input type="hidden" name="image_id" value="{{ $selectedImage->id ?? '' }}">
                    <input type="hidden" name="coord_x" id="input-coord-x">
                    <input type="hidden" name="coord_y" id="input-coord-y">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i> Báo Lỗi Issue Vùng Nhãn</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Loại Lỗi Phát Hiện <span class="text-danger">*</span></label>
                            <select name="issue_type" class="form-select" required>
                                <option value="wrong_label">Wrong Label (Sai nhãn mô tả)</option>
                                <option value="missing_bbox">Missing Box (Thiếu khoanh vùng)</option>
                                <option value="bad_boundary">Bad Boundary (Khoanh sai vị trí / Lệch nét)</option>
                                <option value="blurry_image">Blurry Image (Ảnh mờ không gán nhãn được)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô Tả Lỗi Chi Tiết</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Nhập hướng dẫn Annotator sửa lại nhãn..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-warning fw-bold px-4">Ghi Nhận Lỗi Issue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hoàn Thành / Nghiệm Thu Job -->
    <div class="modal fade" id="finishJobModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('labeler.review.stage') }}" method="POST">
                    @csrf
                    <input type="hidden" name="job_id" value="{{ $job->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-success"><i class="bi bi-shield-check me-2"></i> Nghiệm Thu & Đổi Stage Job #{{ $job->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Chuyển Sang Stage Mới <span class="text-danger">*</span></label>
                            <select name="target_stage" class="form-select" required>
                                <option value="validation" {{ $job->stage == 'validation' ? 'selected' : '' }}>Validation (Đang Kiểm Thử / Sửa Lỗi)</option>
                                <option value="acceptance" {{ $job->stage == 'acceptance' ? 'selected' : '' }}>Acceptance (Đã Nghiệm Thu Chuẩn AI)</option>
                                <option value="annotation" {{ $job->stage == 'annotation' ? 'selected' : '' }}>Annotation (Trả về cho Annotator vẽ lại)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nhận Xét Của Reviewer</label>
                            <textarea name="reviewer_note" class="form-control" rows="3" placeholder="Nhập nhận xét tổng quan chất lượng nhãn..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success fw-bold px-4">Xác Nhận Đổi Stage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentReviewTool = 'view';
        let currentImageId = {{ $selectedImage->id ?? 'null' }};
        let currentJobId = {{ $job->id ?? 'null' }};
        let pendingClickCoords = null;

        // Zoom, Pan & Rotation Engine State
        let zoomLevel = 1.0;
        let panX = 0;
        let panY = 0;
        let rotationAngle = 0;
        let isPanning = false;
        let startPanX = 0;
        let startPanY = 0;

        // Raw Annotations from Backend
        const rawAnnotations = @json($annotations);
        const labelsList = @json($labels);

        // Parse Annotations Array into 1000x1000 scale
        const annotations = rawAnnotations.map(ann => {
            let coords = ann.coordinates || {};
            if (typeof coords === 'string') {
                try { coords = JSON.parse(coords); } catch (e) {}
            }

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

            const labelObj = labelsList.find(l => l.id == ann.label_id);
            return {
                id: ann.id,
                annotation_type: ann.annotation_type || 'bbox',
                label_id: ann.label_id,
                label_name: labelObj ? labelObj.name : 'Unassigned',
                label_color: labelObj ? labelObj.color : '#ef4444',
                coordinates: coords,
                description: ann.description || ''
            };
        });

        // Image Navigation
        const imagesList = @json($images->pluck('id')->toArray());
        const currentImgIdx = imagesList.indexOf(currentImageId);

        function prevImage() {
            if (currentImgIdx > 0) {
                const prevId = imagesList[currentImgIdx - 1];
                location.href = `?image_id=${prevId}`;
            } else {
                alert('Đã ở ảnh đầu tiên trong Task!');
            }
        }

        function nextImage() {
            if (currentImgIdx < imagesList.length - 1) {
                const nextId = imagesList[currentImgIdx + 1];
                location.href = `?image_id=${nextId}`;
            } else {
                alert('Đã ở ảnh cuối cùng trong Task!');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Append all modals directly to document.body
            document.querySelectorAll('.modal').forEach(modalEl => {
                document.body.appendChild(modalEl);
            });

            renderShapes();
            setupKeyboardShortcuts();
            setupWheelZoom();

            // Click handler & Pan Dragging handlers on Canvas Viewport
            const viewport = document.getElementById('canvas-viewport');
            const svg = document.getElementById('review-svg-overlay');

            if (viewport) {
                viewport.addEventListener('mousedown', function(e) {
                    if (e.button !== 0) return;
                    if (currentReviewTool === 'pan') {
                        isPanning = true;
                        startPanX = e.clientX;
                        startPanY = e.clientY;
                    }
                });

                viewport.addEventListener('mousemove', function(e) {
                    if (svg) {
                        const rect = svg.getBoundingClientRect();
                        const scaleX = 1000 / rect.width;
                        const scaleY = 1000 / rect.height;
                        const currentX = Math.round((e.clientX - rect.left) * scaleX);
                        const currentY = Math.round((e.clientY - rect.top) * scaleY);

                        const coordsEl = document.getElementById('status-coords');
                        if (coordsEl) {
                            coordsEl.textContent = `X: ${Math.round(currentX / 10)}% | Y: ${Math.round(currentY / 10)}% | Zoom: ${Math.round(zoomLevel * 100)}% | Rotate: ${rotationAngle}°`;
                        }
                    }

                    if (isPanning && currentReviewTool === 'pan') {
                        panX += (e.clientX - startPanX) / zoomLevel;
                        panY += (e.clientY - startPanY) / zoomLevel;
                        startPanX = e.clientX;
                        startPanY = e.clientY;
                        updateTransform();
                    }
                });

                viewport.addEventListener('mouseup', function() {
                    isPanning = false;
                });

                viewport.addEventListener('mouseleave', function() {
                    isPanning = false;
                });
            }

            if (svg) {
                svg.addEventListener('click', function(e) {
                    if (currentReviewTool !== 'issue') return;

                    const rect = svg.getBoundingClientRect();
                    const scaleX = 1000 / rect.width;
                    const scaleY = 1000 / rect.height;
                    const x = Math.round((e.clientX - rect.left) * scaleX);
                    const y = Math.round((e.clientY - rect.top) * scaleY);

                    document.getElementById('input-coord-x').value = x;
                    document.getElementById('input-coord-y').value = y;

                    const modal = new bootstrap.Modal(document.getElementById('issueBoxModal'));
                    modal.show();
                });
            }
        });

        function toggleSidebar(side) {
            const sidebar = document.getElementById(`sidebar-${side}`);
            if (sidebar) sidebar.classList.toggle('active');
        }

        function setReviewTool(tool) {
            currentReviewTool = tool;
            document.querySelectorAll('.tool-btn').forEach(btn => btn.classList.remove('active'));
            const btn = document.getElementById(`tool-${tool}`);
            if (btn) btn.classList.add('active');

            const viewport = document.getElementById('canvas-viewport');
            const infoEl = document.getElementById('status-tool-info');

            if (tool === 'pan') {
                viewport.classList.add('pan-mode');
                infoEl.innerHTML = '<i class="bi bi-info-circle me-1"></i> Di Chuyển Mode: Giữ & kéo chuột để di chuyển ảnh';
            } else if (tool === 'issue') {
                viewport.classList.remove('pan-mode');
                infoEl.innerHTML = '<i class="bi bi-info-circle me-1"></i> Báo Lỗi Mode: Nhấp chuột trực tiếp lên ảnh để gắn cờ Issue';
            } else {
                viewport.classList.remove('pan-mode');
                infoEl.innerHTML = '<i class="bi bi-info-circle me-1"></i> Review Mode: Rê chuột lên nét vẽ để kiểm tra thông tin nhãn';
            }
        }

        // Zoom, Pan & Rotate Handlers
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
                coordsEl.textContent = `Zoom: ${Math.round(zoomLevel * 100)}% | Rotate: ${rotationAngle}°`;
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

        function rotateImage() {
            rotationAngle = (rotationAngle + 90) % 360;
            updateTransform();
        }

        function setupWheelZoom() {
            const viewport = document.getElementById('canvas-viewport');
            if (!viewport) return;

            viewport.addEventListener('wheel', function(e) {
                e.preventDefault();
                if (e.deltaY < 0) zoomIn();
                else zoomOut();
            }, { passive: false });
        }

        function setupKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;

                const key = e.key.toUpperCase();

                if (e.key === 'PageUp' || e.key === '[') {
                    e.preventDefault();
                    prevImage();
                } else if (e.key === 'PageDown' || e.key === ']') {
                    e.preventDefault();
                    nextImage();
                } else if (e.shiftKey && key === 'R') {
                    e.preventDefault();
                    rotateImage();
                } else if (key === 'F') {
                    fitImage();
                } else if (key === 'V') {
                    setReviewTool('pan');
                }
            });
        }

        // Hover Info Card / Tooltip Display Engine
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

        /* SVG Rendering Engine with ViewBox 0 0 1000 1000 & FULL Polygon/Bbox Support */
        function renderShapes() {
            const svg = document.getElementById('review-svg-overlay');
            if (!svg) return;
            svg.innerHTML = '';

            const dotRadius = Math.max(0.8, 2.2 / zoomLevel);
            const dotStroke = Math.max(0.3, 0.8 / zoomLevel);
            const lineStroke = Math.max(0.8, 2.0 / zoomLevel);

            annotations.forEach((ann, index) => {
                const color = ann.label_color || '#ef4444';

                if (ann.annotation_type === 'bbox' && ann.coordinates) {
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
                } else if (ann.annotation_type === 'point' && ann.coordinates) {
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
                } else if (ann.annotation_type === 'polygon' && ann.coordinates && ann.coordinates.points) {
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
        }

        function openFinishJobModal() {
            const modal = new bootstrap.Modal(document.getElementById('finishJobModal'));
            modal.show();
        }
    </script>
@endpush
