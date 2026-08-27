@extends('layouts.labeler')

@section('title', 'Tổng Quan Phân Hệ AI Data Labeling')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="page-title"><i class="bi bi-grid-1x2-fill text-primary me-2"></i> Workspace Tổng Quan AI Data Labeling
            </h4>
            <p class="page-subtitle">Phân hệ quản lý & thực thi gán nhãn dữ liệu huấn luyện mô hình AI (Hình ảnh, Văn bản &
                RAG)</p>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="dash-card dash-card-hover d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                    style="width: 52px; height: 52px; background: rgba(79, 70, 229, 0.1); color: #4f46e5;">
                    <i class="bi bi-images fs-3"></i>
                </div>
                <div>
                    <div class="text-muted-labeler small">Dự Án Gán Nhãn Ảnh</div>
                    <h3 class="fw-bold mb-0">{{ $imageProjectsCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="dash-card dash-card-hover d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                    style="width: 52px; height: 52px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                    <i class="bi bi-card-checklist fs-3"></i>
                </div>
                <div>
                    <div class="text-muted-labeler small">Nhiệm Vụ (Tasks)</div>
                    <h3 class="fw-bold mb-0">{{ $imageTasksCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="dash-card dash-card-hover d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                    style="width: 52px; height: 52px; background: rgba(16, 185, 129, 0.1); color: #059669;">
                    <i class="bi bi-file-earmark-text fs-3"></i>
                </div>
                <div>
                    <div class="text-muted-labeler small">Dự Án Văn Bản (NLP)</div>
                    <h3 class="fw-bold mb-0">{{ $textProjectsCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="dash-card dash-card-hover d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                    style="width: 52px; height: 52px; background: rgba(245, 158, 11, 0.1); color: #d97706;">
                    <i class="bi bi-database-fill-gear fs-3"></i>
                </div>
                <div>
                    <div class="text-muted-labeler small">Kho Tri Thức RAG AI</div>
                    <h3 class="fw-bold mb-0">{{ $knowledgeBasesCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards Row -->
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="dash-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-task text-primary me-2"></i> Danh Sách Công Việc Gán Nhãn
                        Mới Nhất</h6>
                    <span class="badge badge-soft-primary">{{ count($recentJobs) }} Jobs</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-labeler table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Job ID</th>
                                <th>Nhiệm Vụ (Task ID)</th>
                                <th>Tiến Độ (Stage)</th>
                                <th>Phần Trăm</th>
                                <th>Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentJobs as $j)
                                <tr>
                                    <td class="mono text-primary fw-bold">#JOB-{{ $j->id }}</td>
                                    <td>#TASK-{{ $j->task_id }}</td>
                                    <td><span class="badge badge-soft-secondary">{{ $j->stage }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar" style="width: {{ $j->progress }}%;"></div>
                                            </div>
                                            <span class="small mono" style="font-size: 11px;">{{ $j->progress }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($j->status === 'completed')
                                            <span class="badge badge-soft-success"><i
                                                    class="bi bi-check-circle-fill me-1"></i>Completed</span>
                                        @elseif($j->status === 'in_progress')
                                            <span class="badge badge-soft-warning"><i
                                                    class="bi bi-hourglass-split me-1"></i>In Progress</span>
                                        @else
                                            <span class="badge badge-soft-secondary"><i
                                                    class="bi bi-clock me-1"></i>Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted-labeler">
                                        <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                        Chưa có Job gán nhãn nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
