@extends('layouts.app')

@section('title', 'Cẩm Nang Tra Cứu Kiến Thức Nông Nghiệp')

@push('styles')
    <style>
        .knowledge-card {
            border-radius: 16px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            padding: 1.5rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .knowledge-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }

        .knowledge-category-tag {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background-color: var(--primary-subtle);
            color: var(--primary-dark);
            display: inline-block;
            margin-bottom: 0.75rem;
            width: fit-content;
        }

        .knowledge-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            line-height: 1.4;
            cursor: pointer;
        }

        .knowledge-card-title:hover {
            color: var(--primary);
        }

        .knowledge-excerpt {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 1.25rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }

        .knowledge-footer {
            border-top: 1px dashed var(--border-color);
            padding-top: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: #94a3b8;
        }

        .knowledge-modal-body {
            font-size: 15px;
            line-height: 1.75;
            color: #334155;
            white-space: pre-wrap;
        }
    </style>
@endpush

@section('content')
    <x-page-header title="Cẩm Nang Tri Thức Nông Nghiệp">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Tri thức</span>
            <span>/</span>
            <span class="text-primary fw-bold">Cẩm nang tra cứu</span>
        </x-slot:breadcrumbs>

        @if (Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isManager()))
            <x-slot:actions>
                <a href="{{ route('content.knowledge.manage') }}" class="btn btn-secondary">
                    <i class="bi bi-gear-fill"></i> Quản Lý Tri Thức (QTV)
                </a>
            </x-slot:actions>
        @endif
    </x-page-header>

    <!-- THANH TÌM KIẾM & LỌC THEO DANH MỤC -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('content.knowledge') }}" class="row g-3 align-items-center">
                <div class="col-lg-6 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-color: var(--border-color);">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                            placeholder="Nhập từ khóa kỹ thuật canh tác, sâu bệnh, bón phân..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-4 col-md-4">
                    <select name="category_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả danh mục tri thức --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-funnel"></i> Tra Cứu
                    </button>
                    @if (request('search') || request('category_id'))
                        <a href="{{ route('content.knowledge') }}" class="btn btn-secondary" title="Đặt lại lọc">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- DANH SÁCH BÀI VIẾT TRI THỨC NÔNG NGHIỆP DẠNG CARD -->
    <div class="row g-4 mb-4">
        @forelse($articles as $item)
            <div class="col-lg-4 col-md-6">
                <div class="knowledge-card">
                    <div class="knowledge-category-tag">
                        <i class="bi bi-bookmark-star me-1"></i> {{ $item->category->name ?? 'Kiến thức chung' }}
                    </div>

                    <h5 class="knowledge-card-title" onclick="openReadKnowledgeModal({{ json_encode([
                        'id' => $item->id,
                        'title' => $item->title,
                        'category' => $item->category->name ?? 'Kiến thức chung',
                        'content' => $item->content,
                        'date' => $item->created_at ? $item->created_at->format('d/m/Y') : '10/08/2026',
                        'author' => $item->creator->name ?? 'Chuyên gia Nông Nghiệp'
                    ]) }})">
                        {{ $item->title }}
                    </h5>

                    <p class="knowledge-excerpt">{{ Str::limit(strip_tags($item->content), 130) }}</p>

                    <div class="knowledge-footer">
                        <span><i class="bi bi-calendar3 me-1"></i> {{ $item->created_at ? $item->created_at->format('d/m/Y') : '' }}</span>
                        <button type="button" class="btn btn-link btn-sm p-0 text-primary fw-bold text-decoration-none"
                            onclick="openReadKnowledgeModal({{ json_encode([
                                'id' => $item->id,
                                'title' => $item->title,
                                'category' => $item->category->name ?? 'Kiến thức chung',
                                'content' => $item->content,
                                'date' => $item->created_at ? $item->created_at->format('d/m/Y') : '10/08/2026',
                                'author' => $item->creator->name ?? 'Chuyên gia Nông Nghiệp'
                            ]) }})">
                            Xem chi tiết <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-book-half"></i></div>
                        <h5 class="fw-bold mb-1">Chưa tìm thấy tri thức nông nghiệp phù hợp</h5>
                        <p class="text-muted small mb-0">Thử thay đổi từ khóa hoặc bộ lọc danh mục.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- PHÂN TRANG -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="text-muted" style="font-size: 14px;">
            Hiển thị {{ $articles->firstItem() ?? 0 }} - {{ $articles->lastItem() ?? 0 }} trên tổng số {{ $articles->total() }} tri thức
        </span>
        <div>
            {{ $articles->links() }}
        </div>
    </div>

    <!-- MODAL ĐỌC CHI TIẾT TRI THỨC -->
    <div class="app-modal" id="modal-read-knowledge">
        <div class="modal-dialog" style="max-width: 720px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-book text-primary me-2"></i> Chi Tiết Hướng Dẫn Kỹ Thuật</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <div class="modal-body py-4">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 mb-2" id="read-knowledge-category"></span>
                
                <h4 class="fw-bold text-dark mb-2" id="read-knowledge-title" style="line-height: 1.35;"></h4>
                
                <div class="text-muted small mb-3 pb-2 border-bottom">
                    <i class="bi bi-calendar3 me-1"></i> <span id="read-knowledge-date"></span> &bull; 
                    <i class="bi bi-person me-1 ms-2"></i> Bien biên soạn: <strong id="read-knowledge-author"></strong>
                </div>

                <div class="knowledge-modal-body p-3 bg-light rounded border" id="read-knowledge-content">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-modal-close">Đóng Hướng Dẫn</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openReadKnowledgeModal(data) {
            document.getElementById('read-knowledge-title').textContent = data.title;
            document.getElementById('read-knowledge-category').textContent = data.category;
            document.getElementById('read-knowledge-date').textContent = data.date;
            document.getElementById('read-knowledge-author').textContent = data.author;
            document.getElementById('read-knowledge-content').textContent = data.content;

            openModal('modal-read-knowledge');
        }
    </script>
@endpush
