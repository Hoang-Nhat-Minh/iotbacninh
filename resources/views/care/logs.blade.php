@extends('layouts.app')

@section('title', 'Quản Lý Lịch Sử Chăm Sóc')

@section('content')
    <x-page-header title="Nhật Ký Canh Tác & Chăm Sóc">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Canh tác</span>
            <span>/</span>
            <span class="text-primary fw-bold">Nhật ký chăm sóc</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <button type="button" class="btn btn-secondary" onclick="openModal('modal-import-care')">
                <i class="bi bi-file-earmark-excel"></i> Nhập Excel
            </button>
            <a href="{{ route('care.logs.export') }}" class="btn btn-secondary">
                <i class="bi bi-download"></i> Xuất Excel
            </a>
            <button type="button" class="btn btn-primary" onclick="openModal('modal-create-care')">
                <i class="bi bi-plus-lg"></i> Tạo Nhật Ký
            </button>
        </x-slot:actions>
    </x-page-header>



    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('care.logs') }}" class="row g-3 align-items-center mb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Tên chủ vườn, nội dung bón phân, tưới nước..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <select name="user_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả chủ vườn --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}"
                        onchange="this.form.submit()">
                </div>

                <div class="col-lg-2 col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Lọc</button>
                    @if (request('search') || request('user_id') || request('date'))
                        <a href="{{ route('care.logs') }}" class="btn btn-secondary" title="Đặt lại bộ lọc"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Chủ vườn & Vùng trồng</th>
                            <th>Danh mục công việc</th>
                            <th>Ngày thực hiện</th>
                            <th>Nội dung chi tiết</th>
                            <th style="width: 120px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $idx => $c)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $logs->firstItem() + $idx }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $c->user->name ?? 'Người dùng' }}</div>
                                    <div class="text-muted small"><i class="bi bi-geo-alt"></i>
                                        {{ $c->garden->name ?? 'Vườn Bắc Ninh' }} &bull; {{ $c->user->phone ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="badge-status badge-role"><i class="bi bi-tag-fill"></i>
                                        {{ $c->category->name ?? 'Canh tác' }}</span>
                                </td>
                                <td class="text-dark fw-medium" style="font-size: 13px;">
                                    <i class="bi bi-calendar3 text-muted me-1"></i>
                                    {{ $c->performed_at ? \Carbon\Carbon::parse($c->performed_at)->format('d/m/Y H:i') : '' }}
                                </td>
                                <td style="max-width: 320px;">
                                    <div class="text-secondary" style="font-size: 13px; line-height: 1.5;">
                                        {{ $c->content }}</div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Sửa nhật ký"
                                            onclick="openEditCareModal({{ $c->id }}, '{{ addslashes($c->user->name ?? '') }}', '{{ addslashes($c->content) }}', {{ $c->care_category_id ?? 0 }})">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </button>
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Xóa nhật ký"
                                            onclick="openDeleteCareModal({{ $c->id }})">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-calendar-x"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Chưa có bản ghi nhật ký nào</h6>
                                        <p class="text-muted small mb-0">Tạo nhật ký mới để hỗ trợ nông dân theo dõi mùa vụ
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top"
                style="border-color: var(--border-color) !important;">
                <span class="text-muted" style="font-size: 14px;">
                    Hiển thị {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} trên tổng số
                    {{ $logs->total() }} bản ghi
                </span>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="app-modal" id="modal-create-care">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-journal-plus text-primary"></i> Thêm Nhật Ký Chăm Sóc Hỗ Trợ Nông
                    Dân</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form action="{{ route('care.logs.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn nông dân / chủ vườn <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Danh mục công việc <span class="text-danger">*</span></label>
                            <select name="care_category_id" class="form-select" required>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thời gian thực hiện <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="performed_at" class="form-control" required
                                value="{{ date('Y-m-d\TH:i') }}">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nội dung hướng dẫn / Ghi chú công việc <span
                                class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="4"
                            placeholder="Nhập chi tiết liều lượng phân bón, hướng dẫn kỹ thuật..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu Nhật Ký</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-import-care">
        <div class="modal-dialog" style="max-width: 480px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-excel text-success"></i> Nhập File Excel Công Việc
                    Hướng Dẫn</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form action="{{ route('care.logs.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body text-center py-4">
                    <div class="mb-3 text-success"><i class="bi bi-cloud-arrow-up" style="font-size: 48px;"></i></div>
                    <p class="mb-3">Tải lên file Excel (.xlsx, .xls, .csv) chứa lịch trình và hướng dẫn kỹ thuật chăm sóc
                        cho người dân.</p>
                    <input type="file" name="file" class="form-control mb-2" accept=".xlsx,.xls,.csv" required>
                    <small class="text-muted">Hệ thống sẽ tự động đối chiếu mã vườn và lưu vào cơ sở dữ liệu.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary">Bắt Đầu Nhập Dữ Liệu</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-edit-care">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil text-primary"></i> Sửa Nhật Ký Chăm Sóc</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-edit-care" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chủ vườn</label>
                        <input type="text" id="edit-care-user" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Danh mục công việc <span class="text-danger">*</span></label>
                        <select name="care_category_id" id="edit-care-category" class="form-select" required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nội dung chăm sóc <span class="text-danger">*</span></label>
                        <textarea id="edit-care-content" name="content" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-delete-care">
        <div class="modal-dialog" style="max-width: 420px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Nhật Ký</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-delete-care" action="" method="POST">
                @csrf
                <div class="modal-body text-center py-4">
                    <p>Bạn có chắc muốn xóa bản ghi lịch sử chăm sóc này không?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa Ngay</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openEditCareModal(id, user, content, categoryId) {
            document.getElementById('form-edit-care').action = window.location.origin + '/care/logs/update/' + id;
            document.getElementById('edit-care-user').value = user;
            document.getElementById('edit-care-content').value = content;
            if (document.getElementById('edit-care-category')) {
                document.getElementById('edit-care-category').value = categoryId || '';
            }
            openModal('modal-edit-care');
        }

        function openDeleteCareModal(id) {
            document.getElementById('form-delete-care').action = window.location.origin + '/care/logs/delete/' + id;
            openModal('modal-delete-care');
        }
    </script>
@endpush
