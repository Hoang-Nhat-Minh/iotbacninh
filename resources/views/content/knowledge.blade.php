@extends('layouts.app')

@section('title', 'Quản Lý Tri Thức Nông Nghiệp')

@section('content')
    <x-page-header title="Cẩm Nang Tri Thức Nông Nghiệp">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Tri thức</span>
            <span>/</span>
            <span class="text-primary fw-bold">Cẩm nang kỹ thuật</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <button type="button" class="btn btn-primary" onclick="openModal('modal-add-knowledge')">
                <i class="bi bi-plus-circle-fill"></i> Thêm Bài Tri Thức
            </button>
        </x-slot:actions>
    </x-page-header>



    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('content.knowledge.manage') }}" class="row g-3 align-items-center mb-4">
                <div class="col-lg-6 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Tên bệnh, triệu chứng, kỹ thuật bón phân..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3">
                    <select name="category_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả danh mục --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Lọc</button>
                    @if (request('search') || request('category_id'))
                        <a href="{{ route('content.knowledge.manage') }}" class="btn btn-secondary" title="Đặt lại bộ lọc"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Tiêu đề cẩm nang</th>
                            <th>Danh mục</th>
                            <th>Trạng thái</th>
                            <th>Ngày cập nhật</th>
                            <th style="width: 120px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($knowledge as $idx => $k)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $knowledge->firstItem() + $idx }}</td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 15px;">{{ $k->title }}</div>
                                </td>
                                <td><span class="badge-status badge-role"><i class="bi bi-tag-fill"></i>
                                        {{ $k->category->name ?? 'Kỹ thuật' }}</span></td>
                                <td><span class="badge-status badge-active"><i class="bi bi-check-circle-fill"></i>
                                        {{ $k->status === 'published' ? 'Hiển thị' : 'Bản nháp' }}</span></td>
                                <td class="text-muted small">{{ $k->updated_at ? $k->updated_at->format('d/m/Y') : '' }}
                                </td>
                                <td style="text-align: center;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Sửa bài tri thức"
                                            onclick="openEditKnowledgeModal({{ $k->id }}, '{{ addslashes($k->title) }}', {{ $k->knowledge_category_id }}, '{{ addslashes($k->content ?? '') }}', '{{ $k->status }}')">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </button>
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Xóa bài tri thức"
                                            onclick="openDeleteKnowledgeModal({{ $k->id }}, '{{ addslashes($k->title) }}')">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-book"></i></div>
                                        <h6 class="fw-bold mb-1">Chưa có tài liệu tri thức nào</h6>
                                        <p class="text-muted small mb-0">Tạo tài liệu hướng dẫn kỹ thuật nông nghiệp để chia
                                            sẻ cho người dân</p>
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
                    Hiển thị {{ $knowledge->firstItem() ?? 0 }} - {{ $knowledge->lastItem() ?? 0 }} trên tổng số
                    {{ $knowledge->total() }} bài viết
                </span>
                <div>
                    {{ $knowledge->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="app-modal" id="modal-add-knowledge">
        <div class="modal-dialog" style="max-width: 600px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-book-half text-primary"></i> Thêm Bài Tri Thức Nông Nghiệp</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form action="{{ url('/content/knowledge/store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tiêu đề bài viết tri thức <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control"
                            placeholder="Ví dụ: Quy trình phòng trừ nấm sương mai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Danh mục tri thức <span class="text-danger">*</span></label>
                        <select name="knowledge_category_id" class="form-select" required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nội dung hướng dẫn chi tiết <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="6"
                            placeholder="Soạn thảo triệu chứng, hình ảnh nhận diện, hoạt chất khuyên dùng..." required></textarea>
                    </div>
                    <input type="hidden" name="status" value="published">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu Tri Thức</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-edit-knowledge">
        <div class="modal-dialog" style="max-width: 600px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil text-primary"></i> Sửa Tri Thức</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-edit-knowledge" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tiêu đề</label>
                        <input type="text" name="title" id="edit-knowledge-title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Danh mục</label>
                        <select name="knowledge_category_id" id="edit-knowledge-category" class="form-select">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nội dung</label>
                        <textarea name="content" id="edit-knowledge-content" class="form-control" rows="6"></textarea>
                    </div>
                    <input type="hidden" name="status" value="published">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-delete-knowledge">
        <div class="modal-dialog" style="max-width: 420px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Tri Thức</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-delete-knowledge" action="" method="POST">
                @csrf
                <div class="modal-body text-center py-4">
                    <p>Bạn có chắc muốn xóa bài tri thức: <br><strong id="delete-knowledge-title"
                            class="text-danger"></strong>?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác Nhận Xóa</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openEditKnowledgeModal(id, title, categoryId, content, status) {
            document.getElementById('form-edit-knowledge').action = window.location.origin + '/content/knowledge/update/' +
                id;
            document.getElementById('edit-knowledge-title').value = title;
            document.getElementById('edit-knowledge-category').value = categoryId;
            document.getElementById('edit-knowledge-content').value = content;
            openModal('modal-edit-knowledge');
        }

        function openDeleteKnowledgeModal(id, title) {
            document.getElementById('form-delete-knowledge').action = window.location.origin +
                '/content/knowledge/delete/' + id;
            document.getElementById('delete-knowledge-title').textContent = title;
            openModal('modal-delete-knowledge');
        }
    </script>
@endpush
