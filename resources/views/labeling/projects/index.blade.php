@extends('layouts.labeler')

@section('title', 'Quản Lý Dự Án AI')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="page-title"><i class="bi bi-folder-fill text-primary me-2"></i> Quản Lý Dự Án AI (Projects)</h4>
            <p class="page-subtitle">Quản lý tập trung danh sách các dự án huấn luyện AI, tạo mới, chỉnh sửa thông tin & bộ nhãn định nghĩa</p>
        </div>

        <button type="button" class="btn btn-primary-gradient py-2 px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createProjectModal">
            <i class="bi bi-plus-circle-fill me-1"></i> Tạo Dự Án Mới
        </button>
    </div>

    <!-- Thanh Bộ Lọc & Tìm Kiếm -->
    <div class="dash-card mb-4">
        <form action="{{ route('labeler.projects') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-lg-6 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên dự án hoặc mô tả..."
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-lg-4 col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả Trạng Thái --</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt Động (Active)</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Lưu Trữ (Archived)</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-2">
                <div class="btn-group btn-group-sm w-100">
                    <button type="submit" class="btn btn-primary fw-bold" style="font-size: 12px;">
                        <i class="bi bi-funnel-fill me-1"></i> Lọc
                    </button>
                    <a href="{{ route('labeler.projects') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bảng Danh Sách Dự Án -->
    <div class="dash-card">
        <div class="table-responsive">
            <table class="table table-labeler table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 90px;">Project ID</th>
                        <th>Tên Dự Án AI</th>
                        <th class="text-center">Số Nhiệm Vụ (Tasks)</th>
                        <th class="text-center">Số Bộ Nhãn (Labels)</th>
                        <th class="text-center">Tổng Dữ Liệu Ảnh</th>
                        <th>Trạng Thái</th>
                        <th class="text-end" style="width: 160px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $p)
                        <tr>
                            <td class="mono text-primary fw-bold">#PRJ-{{ $p->id }}</td>
                            <td>
                                <div class="fw-bold text-dark mb-0" style="font-size: 14px;">{{ $p->name }}</div>
                                <div class="text-muted-labeler small text-truncate" style="max-width: 320px;" title="{{ $p->description }}">
                                    {{ $p->description ?: 'Không có mô tả' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary fw-bold">{{ $p->tasks_count }} Tasks</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-success fw-bold">{{ $p->labels_count }} Nhãn</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-secondary fw-bold">{{ $p->images_count }} Ảnh</span>
                            </td>
                            <td>
                                @if ($p->status === 'active')
                                    <span class="badge badge-soft-success"><i class="bi bi-check-circle-fill me-1"></i>Active</span>
                                @else
                                    <span class="badge badge-soft-secondary"><i class="bi bi-archive-fill me-1"></i>Archived</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editProjectModal{{ $p->id }}"
                                        title="Chỉnh sửa Dự án">
                                        <i class="bi bi-pencil-square text-warning"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#deleteProjectModal{{ $p->id }}"
                                        title="Xóa Dự án">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted-labeler">
                                <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                                Chưa có Dự án AI nào trong hệ thống.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals Chỉnh Sửa & Xóa Cho Từng Dự Án -->
    @foreach ($projects as $p)
        <!-- Modal Edit Project -->
        <div class="modal fade" id="editProjectModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('labeler.projects.update', $p->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i> Chỉnh Sửa Dự Án #PRJ-{{ $p->id }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Tên Dự Án AI <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $p->name }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Trạng Thái</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ $p->status == 'active' ? 'selected' : '' }}>Hoạt Động (Active)</option>
                                    <option value="archived" {{ $p->status == 'archived' ? 'selected' : '' }}>Lưu Trữ (Archived)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô Tả Dự Án</label>
                                <textarea name="description" class="form-control" rows="3">{{ $p->description }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-warning fw-bold px-4">Lưu Cập Nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Delete Project -->
        <div class="modal fade" id="deleteProjectModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <form action="{{ route('labeler.projects.delete', $p->id) }}" method="POST">
                        @csrf
                        <div class="modal-body text-center p-4">
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-1 d-block mb-2"></i>
                            <h6 class="fw-bold mb-2">Xác nhận xóa Dự Án?</h6>
                            <p class="text-muted-labeler small mb-3">
                                Bạn có chắc muốn xóa <strong>'{{ $p->name }}'</strong>? Thao tác này sẽ xóa toàn bộ Tasks, Nhãn và Ảnh liên quan.
                            </p>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-danger w-50 fw-bold">Xóa Ngay</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal Tạo Dự Án Mới -->
    <div class="modal fade" id="createProjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('labeler.projects.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill text-primary me-2"></i> Tạo Dự Án Huấn Luyện AI Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên Dự Án AI <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Ví dụ: Dự Án Nhận Dạng Nấm Bệnh Cây Lúa 2026" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng Thái</label>
                            <select name="status" class="form-select">
                                <option value="active">Hoạt Động (Active)</option>
                                <option value="archived">Lưu Trữ (Archived)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô Tả Chi Tiết Dự Án</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Nhập mục tiêu huấn luyện mô hình AI..."></textarea>
                        </div>

                        <div class="form-check form-switch bg-light p-3 rounded border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="create_default_labels" value="1" id="chk-default-labels" checked>
                            <label class="form-check-label fw-semibold small" for="chk-default-labels">
                                Tự động khởi tạo bộ nhãn mô tả nông nghiệp mẫu (CVAT Style Labels)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary-gradient px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Khởi Tạo Dự Án
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
