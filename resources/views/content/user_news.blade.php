@extends('layouts.app')

@section('title', 'Tin Tức Nông Nghiệp & Cảnh Báo Nông Vụ')

@push('styles')
    <style>
        .news-card {
            border-radius: 16px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .news-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }

        .news-card-img-wrapper {
            position: relative;
            height: 180px;
            overflow: hidden;
            background-color: #f1f5f9;
        }

        .news-card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .news-card:hover .news-card-img {
            transform: scale(1.05);
        }

        .news-bookmark-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
            z-index: 2;
        }

        .news-bookmark-btn:hover {
            transform: scale(1.1);
            background: #ffffff;
        }

        .news-bookmark-btn.active i {
            color: #f59e0b;
        }

        .news-card-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .news-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            cursor: pointer;
        }

        .news-title:hover {
            color: var(--primary);
        }

        .news-summary {
            font-size: 13.5px;
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }

        .news-meta {
            font-size: 12.5px;
            color: #94a3b8;
            border-top: 1px dashed var(--border-color);
            padding-top: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Reading Modal */
        .news-modal-img {
            width: 100%;
            max-height: 320px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 1.25rem;
        }

        .news-modal-content {
            font-size: 15px;
            line-height: 1.7;
            color: #334155;
            white-space: pre-wrap;
        }
    </style>
@endpush

@section('content')
    <x-page-header title="Tin Tức Nông Nghiệp & Cảnh Báo">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Tin tức</span>
            <span>/</span>
            <span class="text-primary fw-bold">Bản tin nông nghiệp</span>
        </x-slot:breadcrumbs>

        @if (Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isManager()))
            <x-slot:actions>
                <a href="{{ route('content.news.manage') }}" class="btn btn-secondary">
                    <i class="bi bi-gear-fill"></i> Quản Lý Bài Viết (QTV)
                </a>
            </x-slot:actions>
        @endif
    </x-page-header>

    <!-- THANH TÌM KIẾM & BỘ LỌC DÀNH CHO NÔNG DÂN -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('content.news') }}" class="row g-3 align-items-center">
                <div class="col-lg-6 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-color: var(--border-color);">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                            placeholder="Nhập từ khóa tìm kiếm tin tức nông nghiệp..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-4 col-md-4">
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('content.news') }}"
                            class="btn {{ !request('bookmarked') ? 'btn-primary' : 'btn-outline-secondary' }}">
                            <i class="bi bi-newspaper"></i> Tất cả tin tức
                        </a>
                        <a href="{{ route('content.news', ['bookmarked' => 1]) }}"
                            class="btn {{ request('bookmarked') ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">
                            <i class="bi bi-star-fill text-warning"></i> Tin quan trọng
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-funnel"></i> Lọc Tin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DANH SÁCH BẢN TIN DẠNG THẺ CARD -->
    <div class="row g-4 mb-4">
        @forelse($news as $item)
            @php
                $isBookmarked = in_array($item->id, $bookmarkedIds);
            @endphp
            <div class="col-lg-4 col-md-6">
                <div class="news-card">
                    <div class="news-card-img-wrapper">
                        @if ($item->thumbnail)
                            <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="{{ $item->title }}"
                                class="news-card-img">
                        @else
                            <img src="https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=600&h=360"
                                alt="{{ $item->title }}" class="news-card-img">
                        @endif

                        <!-- Nút Bookmark / Đánh dấu tin tức quan trọng -->
                        <button type="button" class="news-bookmark-btn {{ $isBookmarked ? 'active' : '' }}"
                            title="{{ $isBookmarked ? 'Bỏ đánh dấu tin quan trọng' : 'Đánh dấu là tin tức quan trọng' }}"
                            onclick="toggleBookmark({{ $item->id }}, this)">
                            <i class="bi {{ $isBookmarked ? 'bi-star-fill' : 'bi-star' }} fs-5"></i>
                        </button>
                    </div>

                    <div class="news-card-body">
                        <h5 class="news-title" onclick="openReadNewsModal({{ json_encode([
                            'id' => $item->id,
                            'title' => $item->title,
                            'summary' => $item->summary,
                            'content' => $item->content,
                            'thumbnail' => $item->thumbnail ? asset('storage/' . $item->thumbnail) : 'https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=800&h=400',
                            'date' => $item->published_at ? $item->published_at->format('d/m/Y H:i') : ($item->created_at ? $item->created_at->format('d/m/Y') : ''),
                            'author' => $item->author->name ?? 'Ban Biên Tập Nông Nghiệp',
                            'is_bookmarked' => $isBookmarked
                        ]) }})">
                            {{ $item->title }}
                        </h5>

                        <p class="news-summary">{{ $item->summary ?? Str::limit(strip_tags($item->content), 120) }}</p>

                        <div class="news-meta">
                            <span><i class="bi bi-clock me-1"></i> {{ $item->published_at ? $item->published_at->format('d/m/Y') : '' }}</span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-primary fw-bold text-decoration-none"
                                onclick="openReadNewsModal({{ json_encode([
                                    'id' => $item->id,
                                    'title' => $item->title,
                                    'summary' => $item->summary,
                                    'content' => $item->content,
                                    'thumbnail' => $item->thumbnail ? asset('storage/' . $item->thumbnail) : 'https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=800&h=400',
                                    'date' => $item->published_at ? $item->published_at->format('d/m/Y H:i') : ($item->created_at ? $item->created_at->format('d/m/Y') : ''),
                                    'author' => $item->author->name ?? 'Ban Biên Tập Nông Nghiệp',
                                    'is_bookmarked' => $isBookmarked
                                ]) }})">
                                Đọc chi tiết <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-5 text-center">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-newspaper"></i></div>
                        <h5 class="fw-bold mb-1">Chưa có bài viết tin tức nào</h5>
                        <p class="text-muted small mb-0">Thử thay đổi bộ lọc tìm kiếm hoặc quay lại sau.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- PHÂN TRANG -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="text-muted" style="font-size: 14px;">
            Hiển thị {{ $news->firstItem() ?? 0 }} - {{ $news->lastItem() ?? 0 }} trên tổng số {{ $news->total() }} tin tức
        </span>
        <div>
            {{ $news->links() }}
        </div>
    </div>

    <!-- MODAL ĐỌC BÀI VIẾT CHI TIẾT -->
    <div class="app-modal" id="modal-read-news">
        <div class="modal-dialog" style="max-width: 720px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-book-half text-primary me-2"></i> Chi Tiết Bản Tin Nông Nghiệp</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <div class="modal-body py-4">
                <img id="read-news-img" src="" alt="thumbnail" class="news-modal-img d-none">
                
                <h4 class="fw-bold text-dark mb-2" id="read-news-title" style="line-height: 1.35;"></h4>
                
                <div class="d-flex align-items-center justify-content-between text-muted small mb-3 pb-2 border-bottom">
                    <div>
                        <i class="bi bi-calendar3 me-1"></i> <span id="read-news-date"></span> &bull; 
                        <i class="bi bi-person me-1 ms-2"></i> <span id="read-news-author"></span>
                    </div>
                    <button type="button" id="modal-bookmark-btn" class="btn btn-sm btn-outline-warning" onclick="toggleBookmarkFromModal()">
                        <i class="bi bi-star me-1"></i> Đánh dấu tin quan trọng
                    </button>
                </div>

                <div class="p-3 bg-light rounded border mb-3 text-secondary italic" id="read-news-summary" style="font-style: italic; font-size: 14.5px;">
                </div>

                <div class="news-modal-content" id="read-news-content">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-modal-close">Đóng Đọc Tin</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentModalNewsId = null;

        function openReadNewsModal(data) {
            currentModalNewsId = data.id;
            document.getElementById('read-news-title').textContent = data.title;
            document.getElementById('read-news-date').textContent = data.date;
            document.getElementById('read-news-author').textContent = data.author;
            document.getElementById('read-news-content').textContent = data.content;

            const summaryEl = document.getElementById('read-news-summary');
            if (data.summary) {
                summaryEl.textContent = data.summary;
                summaryEl.style.display = 'block';
            } else {
                summaryEl.style.display = 'none';
            }

            const imgEl = document.getElementById('read-news-img');
            if (data.thumbnail) {
                imgEl.src = data.thumbnail;
                imgEl.classList.remove('d-none');
            } else {
                imgEl.classList.add('d-none');
            }

            updateModalBookmarkBtn(data.is_bookmarked);
            openModal('modal-read-news');
        }

        function updateModalBookmarkBtn(isBookmarked) {
            const btn = document.getElementById('modal-bookmark-btn');
            if (isBookmarked) {
                btn.className = 'btn btn-sm btn-warning text-dark fw-bold';
                btn.innerHTML = '<i class="bi bi-star-fill me-1"></i> Đã đánh dấu tin quan trọng';
            } else {
                btn.className = 'btn btn-sm btn-outline-warning';
                btn.innerHTML = '<i class="bi bi-star me-1"></i> Đánh dấu tin quan trọng';
            }
        }

        function toggleBookmark(newsId, btnElement) {
            fetch('{{ url('/content/news/bookmark') }}/' + newsId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const icon = btnElement.querySelector('i');
                    if (data.is_bookmarked) {
                        btnElement.classList.add('active');
                        icon.className = 'bi bi-star-fill fs-5 text-warning';
                    } else {
                        btnElement.classList.remove('active');
                        icon.className = 'bi bi-star fs-5';
                    }
                    if (typeof showToast === 'function') {
                        showToast(data.message, 'success');
                    }
                }
            })
            .catch(err => {
                if (typeof showToast === 'function') {
                    showToast('Có lỗi xảy ra khi cập nhật đánh dấu.', 'danger');
                }
            });
        }

        function toggleBookmarkFromModal() {
            if (!currentModalNewsId) return;

            fetch('{{ url('/content/news/bookmark') }}/' + currentModalNewsId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateModalBookmarkBtn(data.is_bookmarked);
                    
                    // Cập nhật icon trên danh sách thẻ card ngoài
                    const cardBtns = document.querySelectorAll(`.news-bookmark-btn`);
                    cardBtns.forEach(btn => {
                        if (btn.getAttribute('onclick').includes(`toggleBookmark(${currentModalNewsId},`)) {
                            const icon = btn.querySelector('i');
                            if (data.is_bookmarked) {
                                btn.classList.add('active');
                                icon.className = 'bi bi-star-fill fs-5 text-warning';
                            } else {
                                btn.classList.remove('active');
                                icon.className = 'bi bi-star fs-5';
                            }
                        }
                    });

                    if (typeof showToast === 'function') {
                        showToast(data.message, 'success');
                    }
                }
            })
            .catch(err => {
                if (typeof showToast === 'function') {
                    showToast('Có lỗi xảy ra khi cập nhật đánh dấu.', 'danger');
                }
            });
        }
    </script>
@endpush
