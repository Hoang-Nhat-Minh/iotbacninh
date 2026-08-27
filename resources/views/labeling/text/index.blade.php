@extends('layouts.labeler')

@section('title', 'Gán Nhãn Dữ Liệu Chatbot / Văn Bản NLP')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="page-title"><i class="bi bi-file-text-fill text-primary me-2"></i> Gán Nhãn Dữ Liệu Chatbot /
                Văn Bản NLP</h4>
            <p class="page-subtitle">Quản lý task văn bản, tài liệu tư vấn nông nghiệp & gán nhãn thực thể NER (Bệnh cây, Sâu
                hại, Thuốc BVTV, Vùng canh tác)</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-danger btn-sm fw-bold d-none" id="btn-bulk-delete-doc"
                onclick="openBulkDeleteDocModal()">
                <i class="bi bi-trash-fill me-1"></i> Xóa Đã Chọn (<span id="selected-doc-count">0</span>)
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" data-bs-toggle="modal"
                data-bs-target="#createTextTaskModal">
                <i class="bi bi-folder-plus me-1"></i> + Tạo Task Văn Bản
            </button>
            <button type="button" class="btn btn-primary-gradient" data-bs-toggle="modal"
                data-bs-target="#createDocumentModal">
                <i class="bi bi-file-earmark-plus-fill me-1"></i> Thêm Tài Liệu Mới
            </button>
        </div>
    </div>

    <!-- Bộ Lọc Tinh Gọn -->
    <div class="dash-card mb-4">
        <form action="{{ route('labeler.text') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-lg-5 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Nhập tiêu đề văn bản hoặc nội dung..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <select name="task_id" class="form-select form-select-sm">
                    <option value="">-- Tất cả Task --</option>
                    @foreach ($tasks as $t)
                        <option value="{{ $t->id }}" {{ request('task_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả Trạng Thái --</option>
                    <option value="unlabeled" {{ request('status') == 'unlabeled' ? 'selected' : '' }}>Chưa gán nhãn
                    </option>
                    <option value="labeled" {{ request('status') == 'labeled' ? 'selected' : '' }}>Đã gán nhãn</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <div class="btn-group btn-group-sm w-100">
                    <button type="submit" class="btn btn-primary fw-bold" style="font-size: 12px;">
                        <i class="bi bi-funnel-fill me-1"></i> Lọc
                    </button>
                    <a href="{{ route('labeler.text') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bảng Danh Sách Tài Liệu Văn Bản -->
    <div class="dash-card">
        <div class="table-responsive">
            <table class="table table-labeler table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input class="form-check-input" type="checkbox" id="check-all-doc"
                                onclick="toggleCheckAllDoc(this)">
                        </th>
                        <th style="width: 80px;">ID</th>
                        <th>Tiêu Đề Văn Bản & Trích Đoạn</th>
                        <th>Nhiệm Vụ (Task)</th>
                        <th>Dự Án NLP</th>
                        <th class="text-center">Số Thực Thể</th>
                        <th>Trạng Thái</th>
                        <th class="text-end" style="width: 140px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td>
                                <input class="form-check-input doc-checkbox" type="checkbox" value="{{ $doc->id }}"
                                    onclick="updateDocBulkState()">
                            </td>
                            <td class="mono text-primary fw-bold">#DOC-{{ $doc->id }}</td>
                            <td>
                                <div class="fw-semibold mb-0" style="font-size: 14px;">{{ $doc->title }}</div>
                                <div class="text-muted-labeler small text-truncate" style="max-width: 320px;"
                                    title="{{ $doc->content }}">
                                    {{ $doc->content }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-secondary">{{ $doc->task->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="small">{{ $doc->project->name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary fw-bold">{{ $doc->annotations_count }} Thực
                                    thể</span>
                            </td>
                            <td>
                                @if ($doc->status === 'labeled')
                                    <span class="badge badge-soft-success"><i class="bi bi-check-circle-fill me-1"></i>Đã
                                        gán nhãn</span>
                                @else
                                    <span class="badge badge-soft-warning"><i class="bi bi-pencil-square me-1"></i>Chưa gán
                                        nhãn</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('labeler.text.workspace', $doc->id) }}"
                                    class="btn btn-primary-gradient btn-sm fw-bold">
                                    <i class="bi bi-highlighter me-1"></i> Gán Nhãn
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted-labeler">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Không có tài liệu văn bản nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tạo Task Văn Bản -->
    <div class="modal fade" id="createTextTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('labeler.text.task.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus text-primary me-2"></i> Tạo Task Văn
                            Bản Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên Task <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                placeholder="Ví dụ: Gán nhãn câu hỏi phòng trừ sâu bệnh đợt 2" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Dự Án NLP <span class="text-danger">*</span></label>
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
                            <label class="form-label">Mô Tả Task</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary-gradient px-4">Khởi Tạo Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Thêm Tài Liệu Văn Bản -->
    <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('labeler.text.document.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-plus-fill text-primary me-2"></i>
                            Thêm Tài Liệu Văn Bản Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Chọn Nhiệm Vụ (Task) <span class="text-danger">*</span></label>
                            <select name="task_id" class="form-select" required>
                                @foreach ($tasks as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tiêu Đề Văn Bản <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                placeholder="Ví dụ: Kỹ thuật xử lý nhện đỏ hại chanh dây" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội Dung Văn Bản Tư Vấn <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="6" placeholder="Nhập đoạn văn bản cần gán nhãn..."
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary-gradient px-4">Thêm Tài Liệu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Xóa Hàng Loạt Document -->
    <div class="modal fade" id="bulkDeleteDocModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('labeler.text.documents.delete') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Xác Nhận Xóa Hàng Loạt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-2">Bạn có chắc muốn xóa <strong class="text-danger"
                                id="bulk-doc-modal-count">0</strong> tài liệu văn bản được chọn?</p>
                        <div id="hidden-doc-inputs"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger fw-bold px-4">Xóa Ngay</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleCheckAllDoc(source) {
            const checkboxes = document.querySelectorAll('.doc-checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
            updateDocBulkState();
        }

        function updateDocBulkState() {
            const checked = document.querySelectorAll('.doc-checkbox:checked');
            const btnBulk = document.getElementById('btn-bulk-delete-doc');
            const countSpan = document.getElementById('selected-doc-count');

            if (checked.length > 0) {
                btnBulk.classList.remove('d-none');
                countSpan.textContent = checked.length;
            } else {
                btnBulk.classList.add('d-none');
                countSpan.textContent = '0';
            }
        }

        function openBulkDeleteDocModal() {
            const checked = document.querySelectorAll('.doc-checkbox:checked');
            if (checked.length === 0) return;

            document.getElementById('bulk-doc-modal-count').textContent = checked.length;
            const container = document.getElementById('hidden-doc-inputs');
            container.innerHTML = '';

            checked.forEach(cb => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'document_ids[]';
                inp.value = cb.value;
                container.appendChild(inp);
            });

            const modal = new bootstrap.Modal(document.getElementById('bulkDeleteDocModal'));
            modal.show();
        }
    </script>
@endpush
