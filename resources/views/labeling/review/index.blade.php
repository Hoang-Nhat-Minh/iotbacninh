@extends('layouts.labeler')

@section('title', 'Kiểm Tra Chéo Hình Ảnh Gán Nhãn')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="page-title"><i class="bi bi-shield-check text-primary me-2"></i> Kiểm Tra Chéo Dữ Liệu Gán Nhãn</h4>
            <p class="page-subtitle">Nhà quản lý kiểm thử độ chính xác vùng gán nhãn, phát hiện và gắn marker báo lỗi (Open
                an Issue), chọn duyệt Đạt/Không Đạt</p>
        </div>
    </div>

    <!-- Bộ Lọc -->
    <!-- Bộ Lọc Tinh Gọn -->
    <div class="dash-card mb-4">
        <form action="{{ route('labeler.review') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <select name="stage" class="form-select form-select-sm">
                    <option value="">-- Tất cả Stage --</option>
                    <option value="annotation" {{ request('stage') == 'annotation' ? 'selected' : '' }}>Annotation (Gán
                        nhãn)</option>
                    <option value="validation" {{ request('stage') == 'validation' ? 'selected' : '' }}>Validation (Đang
                        kiểm thử)</option>
                    <option value="acceptance" {{ request('stage') == 'acceptance' ? 'selected' : '' }}>Acceptance (Đã
                        nghiệm thu)</option>
                </select>
            </div>

            <div class="col-md-5">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả Trạng Thái --</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress
                    </option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="col-md-2">
                <div class="btn-group btn-group-sm w-100">
                    <button type="submit" class="btn btn-primary fw-bold" style="font-size: 12px;">
                        <i class="bi bi-funnel-fill me-1"></i> Lọc
                    </button>
                    <a href="{{ route('labeler.review') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bảng Danh Sách Jobs Cần Review -->
    <div class="dash-card">
        <div class="table-responsive">
            <table class="table table-labeler table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 100px;">Job ID</th>
                        <th>Nhiệm Vụ (Task Name)</th>
                        <th>Dự Án</th>
                        <th>Người Gán Nhãn</th>
                        <th class="text-center">Số Ảnh Labeled</th>
                        <th style="width: 130px;">Tiến Độ %</th>
                        <th>Stage Hiện Tại</th>
                        <th class="text-center">Số Issues Mở</th>
                        <th class="text-end" style="width: 140px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $j)
                        <tr>
                            <td class="mono text-primary fw-bold">#JOB-{{ $j->id }}</td>
                            <td>
                                <div class="fw-semibold mb-0" style="font-size: 14px;">{{ $j->task->name ?? 'N/A' }}</div>
                                <div class="text-muted-labeler small">Task ID: #TASK-{{ $j->task_id }}</div>
                            </td>
                            <td>
                                <span class="badge badge-soft-secondary">{{ $j->project->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-circle text-muted-labeler"></i>
                                    <span class="small">{{ $j->assignee->name ?? 'Chưa rõ' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary fw-bold">{{ $j->labeled_images_count }} /
                                    {{ $j->images_count }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar" style="width: {{ $j->progress }}%;"></div>
                                    </div>
                                    <span class="small mono" style="font-size: 11px;">{{ $j->progress }}%</span>
                                </div>
                            </td>
                            <td>
                                @if ($j->stage === 'acceptance')
                                    <span class="badge badge-soft-success"><i
                                            class="bi bi-shield-check me-1"></i>Acceptance</span>
                                @elseif($j->stage === 'validation')
                                    <span class="badge badge-soft-info"><i class="bi bi-search me-1"></i>Validation</span>
                                @else
                                    <span class="badge badge-soft-warning"><i
                                            class="bi bi-pencil-square me-1"></i>Annotation</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($j->issues_count > 0)
                                    <span class="badge bg-danger text-white"><i
                                            class="bi bi-exclamation-triangle-fill me-1"></i>{{ $j->issues_count }}
                                        Issues</span>
                                @else
                                    <span class="badge badge-soft-secondary">0 Issues</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('labeler.review.workspace', $j->id) }}"
                                    class="btn btn-primary-gradient btn-sm fw-bold">
                                    <i class="bi bi-shield-check me-1"></i> Review Job
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted-labeler">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Không có Job nào cần kiểm tra chéo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
