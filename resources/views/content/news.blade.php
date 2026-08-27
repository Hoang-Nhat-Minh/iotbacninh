@extends('layouts.app')

@section('title', 'Quản Lý Tin Tức Nông Nghiệp')

@section('content')
    <x-page-header title="Quản Lý Tin Tức Nông Nghiệp">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Tin tức</span>
            <span>/</span>
            <span class="text-primary fw-bold">Bản tin nông nghiệp</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <button type="button" class="btn btn-primary" onclick="openModal('modal-add-news')">
                <i class="bi bi-plus-circle-fill"></i> Thêm Bài Viết
            </button>
        </x-slot:actions>
    </x-page-header>



    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('content.news.manage') }}" class="row g-3 align-items-center mb-4">
                <div class="col-lg-6 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Tiêu đề bài viết, từ khóa..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Đã xuất bản
                        </option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Lọc</button>
                    @if (request('search') || request('status'))
                        <a href="{{ route('content.news.manage') }}" class="btn btn-secondary" title="Đặt lại bộ lọc"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Ảnh bìa & Tiêu đề</th>
                            <th>Đường dẫn (Slug)</th>
                            <th>Trạng thái</th>
                            <th>Ngày đăng</th>
                            <th style="width: 120px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($news as $idx => $item)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $news->firstItem() + $idx }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($item->thumbnail)
                                            <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="thumb"
                                                class="rounded border"
                                                style="width: 54px; height: 42px; object-fit: cover;">
                                        @else
                                            <img src="https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=120&h=80"
                                                alt="thumb" class="rounded border"
                                                style="width: 54px; height: 42px; object-fit: cover;">
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 15px;">{{ $item->title }}
                                            </div>
                                            <div class="text-muted small text-truncate" style="max-width: 380px;">
                                                {{ $item->summary }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="text-primary small">/{{ $item->slug }}</code></td>
                                <td>
                                    @if ($item->status === 'published')
                                        <span class="badge-status badge-active"><i class="bi bi-check-circle-fill"></i> Đã
                                            xuất bản</span>
                                    @else
                                        <span class="badge-status badge-locked"><i class="bi bi-file-earmark"></i> Bản
                                            nháp</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $item->created_at ? $item->created_at->format('d/m/Y') : '' }}</td>
                                <td style="text-align: center;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Sửa bài viết"
                                            onclick="openEditNewsModal({{ $item->id }}, '{{ addslashes($item->title) }}', '{{ $item->slug }}', '{{ addslashes($item->summary ?? '') }}', '{{ addslashes($item->content ?? '') }}', '{{ $item->status }}')">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </button>
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Xóa bài viết"
                                            onclick="openDeleteNewsModal({{ $item->id }}, '{{ addslashes($item->title) }}')">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-newspaper"></i></div>
                                        <h6 class="fw-bold mb-1">Chưa có bài viết tin tức nào</h6>
                                        <p class="text-muted small mb-0">Thêm bài viết mới để cung cấp thông tin hữu ích cho
                                            bà con nông dân</p>
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
                    Hiển thị {{ $news->firstItem() ?? 0 }} - {{ $news->lastItem() ?? 0 }} trên tổng số
                    {{ $news->total() }} bài viết
                </span>
                <div>
                    {{ $news->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="app-modal" id="modal-add-news">
        <div class="modal-dialog" style="max-width: 600px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-newspaper text-primary"></i> Thêm Bài Viết Mới</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form action="{{ url('/content/news/store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="news-title-input" class="form-control"
                            placeholder="Nhập tiêu đề tin tức..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Đường dẫn tĩnh (Slug)</label>
                        <input type="text" name="slug" id="news-slug-input" class="form-control bg-light"
                            placeholder="tu-dong-tao-slug">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tóm tắt ngắn</label>
                        <textarea name="summary" class="form-control" rows="2" placeholder="Tóm tắt nội dung 1-2 câu..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ảnh đại diện bài viết</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nội dung bài viết <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="5" placeholder="Soạn thảo chi tiết bài viết..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Trạng thái xuất bản</label>
                        <select name="status" class="form-select">
                            <option value="published" selected>Xuất bản ngay</option>
                            <option value="draft">Lưu bản nháp</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu Bài Viết</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-edit-news">
        <div class="modal-dialog" style="max-width: 600px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil text-primary"></i> Sửa Bài Viết</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-edit-news" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tiêu đề</label>
                        <input type="text" name="title" id="edit-news-title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tóm tắt</label>
                        <textarea name="summary" id="edit-news-summary" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nội dung</label>
                        <textarea name="content" id="edit-news-content" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" id="edit-news-status" class="form-select">
                            <option value="published">Xuất bản</option>
                            <option value="draft">Bản nháp</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-delete-news">
        <div class="modal-dialog" style="max-width: 420px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Bài Viết</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-delete-news" action="" method="POST">
                @csrf
                <div class="modal-body text-center py-4">
                    <p>Bạn có chắc muốn xóa bài viết: <br><strong id="delete-news-title" class="text-danger"></strong>?
                    </p>
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
        document.getElementById('news-title-input').addEventListener('input', function() {
            const slug = this.value.toLowerCase()
                .replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a")
                .replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e")
                .replace(/ì|í|ị|ỉ|ĩ/g, "i")
                .replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o")
                .replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u")
                .replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y")
                .replace(/đ/g, "d")
                .replace(/[^a-z0-9\s-]/g, "")
                .replace(/\s+/g, "-");
            document.getElementById('news-slug-input').value = slug;
        });

        function openEditNewsModal(id, title, slug, summary, content, status) {
            document.getElementById('form-edit-news').action = window.location.origin + '/content/news/update/' + id;
            document.getElementById('edit-news-title').value = title;
            document.getElementById('edit-news-summary').value = summary;
            document.getElementById('edit-news-content').value = content;
            document.getElementById('edit-news-status').value = status;
            openModal('modal-edit-news');
        }

        function openDeleteNewsModal(id, title) {
            document.getElementById('form-delete-news').action = window.location.origin + '/content/news/delete/' + id;
            document.getElementById('delete-news-title').textContent = title;
            openModal('modal-delete-news');
        }
    </script>
@endpush
