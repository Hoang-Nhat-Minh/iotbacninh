@extends('layouts.labeler')

@section('title', 'Quản Lý Dữ Liệu Hình Ảnh & Task Gán Nhãn')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="page-title"><i class="bi bi-card-checklist text-primary me-2"></i> Quản Lý Task Dữ Liệu Hình Ảnh
            </h4>
            <p class="page-subtitle">Quản lý danh sách nhiệm vụ gán nhãn, tải thêm dữ liệu ảnh mới, phân công & dọn dẹp dữ
                liệu</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-danger btn-sm fw-bold d-none" id="btn-bulk-delete"
                onclick="openBulkDeleteModal()">
                <i class="bi bi-trash-fill me-1"></i> Xóa Đã Chọn (<span id="selected-count">0</span>)
            </button>
            <button type="button" class="btn btn-primary-gradient btn-sm py-2 px-3 fw-bold" data-bs-toggle="modal"
                data-bs-target="#createTaskModal">
                <i class="bi bi-plus-circle-fill me-1"></i> Thêm Task & Tải Dữ Liệu Mới
            </button>
        </div>
    </div>

    <!-- Thanh Bộ Lọc & Tìm Kiếm Tinh Gọn -->
    <div class="dash-card mb-4">
        <form action="{{ route('labeler.tasks') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-lg-3 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nhập tên task..."
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <select name="project_id" class="form-select form-select-sm">
                    <option value="">-- Tất cả Dự án --</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả Trạng thái --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress
                    </option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-4">
                <select name="assignee_id" class="form-select form-select-sm">
                    <option value="">-- Tất cả Người phụ trách --</option>
                    @foreach ($assignees as $u)
                        <option value="{{ $u->id }}" {{ request('assignee_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-4">
                <div class="btn-group btn-group-sm w-100">
                    <button type="submit" class="btn btn-primary fw-bold" style="font-size: 12px;">
                        <i class="bi bi-funnel-fill me-1"></i> Lọc
                    </button>
                    <a href="{{ route('labeler.tasks') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bảng Danh Sách Tasks -->
    <div class="dash-card">
        <div class="table-responsive">
            <table class="table table-labeler table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input class="form-check-input" type="checkbox" id="check-all" onclick="toggleCheckAll(this)">
                        </th>
                        <th style="width: 90px;">Task ID</th>
                        <th>Tên Task & Mô Tả</th>
                        <th>Dự Án</th>
                        <th class="text-center">Số Ảnh</th>
                        <th style="width: 140px;">Tiến Độ</th>
                        <th>Người Phụ Trách</th>
                        <th>Trạng Thái</th>
                        <th class="text-end" style="width: 140px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $t)
                        <tr>
                            <td>
                                <input class="form-check-input task-checkbox" type="checkbox" value="{{ $t->id }}"
                                    onclick="updateBulkState()">
                            </td>
                            <td class="mono text-primary fw-bold">#TASK-{{ $t->id }}</td>
                            <td>
                                <div class="fw-semibold mb-0" style="font-size: 13.5px;">{{ $t->name }}</div>
                                <div class="text-muted-labeler small text-truncate" style="max-width: 280px;"
                                    title="{{ $t->description }}">
                                    {{ $t->description ?: 'Không có mô tả' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-secondary">{{ $t->project->name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary fw-bold">{{ $t->labeled_count }} /
                                    {{ $t->images_count }}</span>
                            </td>
                            <td>
                                @php
                                    $prog = $t->job->progress ?? 0;
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar" style="width: {{ $prog }}%;"></div>
                                    </div>
                                    <span class="small mono" style="font-size: 11px;">{{ $prog }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-circle text-muted-labeler"></i>
                                    <span class="small">{{ $t->assignee->name ?? 'Chưa phân công' }}</span>
                                </div>
                            </td>
                            <td>
                                @if ($t->status === 'completed')
                                    <span class="badge badge-soft-success"><i
                                            class="bi bi-check-circle-fill me-1"></i>Completed</span>
                                @elseif($t->status === 'in_progress')
                                    <span class="badge badge-soft-warning"><i class="bi bi-hourglass-split me-1"></i>In
                                        Progress</span>
                                @else
                                    <span class="badge badge-soft-secondary"><i class="bi bi-clock me-1"></i>Pending</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('labeler.annotation', ['task_id' => $t->id]) }}"
                                        class="btn btn-primary btn-sm" title="Gán nhãn ảnh">
                                        <i class="bi bi-bounding-box-circles"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editTaskModal{{ $t->id }}"
                                        title="Chỉnh sửa Task">
                                        <i class="bi bi-pencil-square text-warning"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#deleteTaskModal{{ $t->id }}"
                                        title="Xóa Task">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted-labeler">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Chưa có Task dữ liệu hình ảnh nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals Chỉnh Sửa & Xóa Độc Lập Cho Từng Task (Đặt ngoài Bảng HTML chuẩn) -->
    @foreach ($tasks as $t)
        <!-- Modal Chỉnh Sửa Task -->
        <div class="modal fade" id="editTaskModal{{ $t->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('labeler.tasks.update', $t->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i> Chỉnh
                                Sửa
                                Task #TASK-{{ $t->id }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Tên Task <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $t->name }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Dự Án <span class="text-danger">*</span></label>
                                <select name="project_id" class="form-select" required>
                                    @foreach ($projects as $p)
                                        <option value="{{ $p->id }}"
                                            {{ $t->project_id == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Trạng Thái</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" {{ $t->status == 'pending' ? 'selected' : '' }}>Pending
                                        </option>
                                        <option value="in_progress" {{ $t->status == 'in_progress' ? 'selected' : '' }}>In
                                            Progress</option>
                                        <option value="completed" {{ $t->status == 'completed' ? 'selected' : '' }}>
                                            Completed</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Người Phụ Trách</label>
                                    <select name="assignee_id" class="form-select">
                                        <option value="">-- Chưa phân công --</option>
                                        @foreach ($assignees as $u)
                                            <option value="{{ $u->id }}"
                                                {{ $t->assignee_id == $u->id ? 'selected' : '' }}>
                                                {{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô Tả Nhiệm Vụ</label>
                                <textarea name="description" class="form-control" rows="3">{{ $t->description }}</textarea>
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

        <!-- Modal Xóa Single Task -->
        <div class="modal fade" id="deleteTaskModal{{ $t->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <form action="{{ route('labeler.tasks.delete', $t->id) }}" method="POST">
                        @csrf
                        <div class="modal-body text-center p-4">
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-1 d-block mb-2"></i>
                            <h6 class="fw-bold mb-2">Xác nhận xóa Task?</h6>
                            <p class="text-muted-labeler small mb-3">
                                Bạn có chắc muốn xóa Task <strong>'{{ $t->name }}'</strong>? Hành động này sẽ xóa toàn
                                bộ bộ ảnh và nhãn đã gán liên quan.
                            </p>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary w-50"
                                    data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-danger w-50 fw-bold">Xóa Ngay</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal Tạo Task Mới -->
    <div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('labeler.tasks.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill text-primary me-2"></i> Tạo Task
                            Mới & Tải Ảnh</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="mb-3">
                                    <label class="form-label">Tên Task Gán Nhãn <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="Ví dụ: Gán nhãn bộ ảnh rệp sáp đợt 2" required>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Dự Án <span class="text-danger">*</span></label>
                                        <select name="project_id" class="form-select" required>
                                            @foreach ($projects as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Người Phụ Trách</label>
                                        <select name="assignee_id" class="form-select">
                                            <option value="">-- Chưa phân công --</option>
                                            @foreach ($assignees as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mô Tả Chi Tiết Nhiệm Vụ</label>
                                    <textarea name="description" class="form-control" rows="3"
                                        placeholder="Nhập ghi chú yêu cầu khoanh vùng đối tượng..."></textarea>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Tải Lên Nhiều File Ảnh (Multiple Upload)</label>
                                <div class="p-3 text-center rounded-3 border-2 border-dashed"
                                    style="background: #f8fafc; border-color: #c7d2fe !important;">
                                    <i class="bi bi-cloud-arrow-up-fill text-primary fs-1 d-block mb-2"></i>
                                    <input type="file" name="image_files[]" multiple
                                        class="form-control form-control-sm mb-2">
                                    <span class="text-muted-labeler small" style="font-size: 11.5px;">Hỗ trợ định dạng:
                                        JPG, PNG, WEBP. Tối đa 10MB/file. Chọn nhiều file cùng lúc.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary-gradient px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Khởi Tạo Task & Tải Ảnh
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Xác Nhận Xóa Hàng Loạt (Bulk Delete Confirmation Modal) -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('labeler.tasks.bulk-delete') }}" method="POST" id="form-bulk-delete">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Cảnh Báo Xóa Hàng Loạt Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-2">Bạn đang yêu cầu xóa vĩnh viễn <strong class="text-danger"
                                id="bulk-modal-count">0</strong> Task dữ liệu được chọn.</p>
                        <div class="alert alert-danger py-2 px-3 small">
                            <i class="bi bi-info-circle-fill me-1"></i> Hành động này sẽ xóa toàn bộ ảnh, vùng gán nhãn và
                            tiến độ Job liên quan. Không thể khôi phục!
                        </div>
                        <div id="hidden-task-inputs"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy Thao
                            Tác</button>
                        <button type="submit" class="btn btn-danger fw-bold px-4">Xác Nhận Xóa Vĩnh Viễn</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleCheckAll(source) {
            const checkboxes = document.querySelectorAll('.task-checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
            updateBulkState();
        }

        function updateBulkState() {
            const checked = document.querySelectorAll('.task-checkbox:checked');
            const btnBulk = document.getElementById('btn-bulk-delete');
            const countSpan = document.getElementById('selected-count');

            if (checked.length > 0) {
                btnBulk.classList.remove('d-none');
                countSpan.textContent = checked.length;
            } else {
                btnBulk.classList.add('d-none');
                countSpan.textContent = '0';
            }
        }

        function openBulkDeleteModal() {
            const checked = document.querySelectorAll('.task-checkbox:checked');
            if (checked.length === 0) return;

            const countModal = document.getElementById('bulk-modal-count');
            const hiddenInputs = document.getElementById('hidden-task-inputs');

            countModal.textContent = checked.length;
            hiddenInputs.innerHTML = '';

            checked.forEach(cb => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'task_ids[]';
                inp.value = cb.value;
                hiddenInputs.appendChild(inp);
            });

            const modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
            modal.show();
        }
    </script>
@endpush
