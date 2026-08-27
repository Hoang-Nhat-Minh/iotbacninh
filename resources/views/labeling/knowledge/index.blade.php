@extends('layouts.labeler')

@section('title', 'Quản Lý Cơ Sở Tri Thức Chatbot')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="page-title"><i class="bi bi-database-fill-gear text-primary me-2"></i> Quản Lý Cơ Sở Tri Thức
                Chatbot</h4>
            <p class="page-subtitle">Quản lý kho tri thức RAG, nạp văn bản tư vấn nông nghiệp & tự động phân chia đoạn Chunks
                tích hợp Vector Store</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" data-bs-toggle="modal"
                data-bs-target="#createBaseModal">
                <i class="bi bi-folder-plus me-1"></i> + Tạo Kho Tri Thức Mới
            </button>
            <button type="button" class="btn btn-primary-gradient" data-bs-toggle="modal"
                data-bs-target="#createDocumentModal">
                <i class="bi bi-file-earmark-plus-fill me-1"></i> Thêm Tài Liệu Tri Thức Mới
            </button>
        </div>
    </div>

    <!-- Knowledge Base Cards Row -->
    <h6 class="fw-bold mb-3"><i class="bi bi-hdd-stack-fill text-warning me-2"></i> Danh Sách Kho Tri Thức RAG Active</h6>
    <div class="row g-3 mb-4">
        @forelse($knowledgeBases as $kb)
            <div class="col-xl-4 col-md-6">
                <div class="dash-card dash-card-hover">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge badge-soft-primary mono mb-1">#KB-{{ $kb->id }}</span>
                            <h6 class="fw-bold mb-1">{{ $kb->name }}</h6>
                        </div>
                        <form action="{{ route('labeler.knowledge.base.delete', $kb->id) }}" method="POST"
                            onsubmit="return confirm('Bạn có chắc muốn xóa Kho tri thức này cùng toàn bộ tài liệu?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm border-0" title="Xóa Kho Tri Thức">
                                <i class="bi bi-trash text-danger"></i>
                            </button>
                        </form>
                    </div>
                    <p class="text-muted-labeler small mb-3 text-truncate" style="font-size: 12.5px;"
                        title="{{ $kb->description }}">
                        {{ $kb->description ?: 'Không có mô tả kho tri thức' }}
                    </p>
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2 rounded-3" style="background: #f8fafc;">
                                <div class="small text-muted-labeler" style="font-size: 11px;">Tài Liệu</div>
                                <div class="fw-bold fs-6">{{ $kb->documents_count }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3" style="background: #f8fafc;">
                                <div class="small text-muted-labeler" style="font-size: 11px;">Chunks</div>
                                <div class="fw-bold text-success fs-6">{{ $kb->chunks_count }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3" style="background: #f8fafc;">
                                <div class="small text-muted-labeler" style="font-size: 11px;">Tokens</div>
                                <div class="fw-bold text-primary fs-6">{{ number_format($kb->tokens_count) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dash-card text-center py-4 text-muted-labeler">Chưa có kho tri thức RAG nào.</div>
            </div>
        @endforelse
    </div>

    <!-- Bộ Lọc Tìm Kiếm Tinh Gọn -->
    <div class="dash-card mb-4">
        <form action="{{ route('labeler.knowledge') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-lg-6 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Nhập từ khóa cần truy xuất trong tài liệu hoặc Chunks..."
                        value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <select name="knowledge_base_id" class="form-select form-select-sm">
                    <option value="">-- Tất cả Kho Tri Thức --</option>
                    @foreach ($knowledgeBases as $kb)
                        <option value="{{ $kb->id }}"
                            {{ request('knowledge_base_id') == $kb->id ? 'selected' : '' }}>{{ $kb->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-12">
                <div class="btn-group btn-group-sm w-100">
                    <button type="submit" class="btn btn-primary fw-bold" style="font-size: 12px;">
                        <i class="bi bi-search me-1"></i> Truy Xuất
                    </button>
                    <a href="{{ route('labeler.knowledge') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bảng Danh Sách Tài Liệu Tri Thức -->
    <div class="dash-card">
        <div class="table-responsive">
            <table class="table table-labeler table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 100px;">Doc ID</th>
                        <th>Tiêu Đề Tài Liệu Tri Thức & Nội Dung</th>
                        <th>Kho Tri Thức RAG</th>
                        <th class="text-center">Số Chunks</th>
                        <th class="text-center">Ước Tính Tokens</th>
                        <th>Trạng Thái</th>
                        <th class="text-end" style="width: 180px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td class="mono text-primary fw-bold">#KDOC-{{ $doc->id }}</td>
                            <td>
                                <div class="fw-semibold mb-0" style="font-size: 14px;">{{ $doc->title }}</div>
                                <div class="text-muted-labeler small text-truncate" style="max-width: 340px;"
                                    title="{{ $doc->content }}">
                                    {{ $doc->content }}
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-secondary">{{ $doc->knowledge_base->name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary fw-bold">{{ $doc->chunks_count }} Chunks</span>
                            </td>
                            <td class="text-center mono text-muted-labeler small">{{ number_format($doc->tokens_count) }}
                            </td>
                            <td>
                                @if ($doc->status === 'active')
                                    <span class="badge badge-soft-success"><i
                                            class="bi bi-check-circle-fill me-1"></i>Active</span>
                                @else
                                    <span class="badge badge-soft-secondary"><i
                                            class="bi bi-pause-circle me-1"></i>Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-primary btn-sm"
                                        onclick="viewDocumentChunks({{ $doc->id }})" title="Xem Chunks Tri Thức">
                                        <i class="bi bi-diagram-3-fill me-1"></i> Xem Chunks
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editDocModal{{ $doc->id }}"
                                        title="Chỉnh sửa tài liệu">
                                        <i class="bi bi-pencil-square text-warning"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#deleteDocModal{{ $doc->id }}"
                                        title="Xóa tài liệu">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </div>

                                <!-- Modal Chỉnh Sửa Tài Liệu -->
                                <div class="modal fade text-start" id="editDocModal{{ $doc->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <form action="{{ route('labeler.knowledge.document.update', $doc->id) }}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold"><i
                                                            class="bi bi-pencil-square text-warning me-2"></i> Chỉnh Sửa
                                                        Tài Liệu #KDOC-{{ $doc->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-8">
                                                            <label class="form-label">Kho Tri Thức RAG <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="knowledge_base_id" class="form-select" required>
                                                                @foreach ($knowledgeBases as $kb)
                                                                    <option value="{{ $kb->id }}"
                                                                        {{ $doc->knowledge_base_id == $kb->id ? 'selected' : '' }}>
                                                                        {{ $kb->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-4">
                                                            <label class="form-label">Trạng Thái</label>
                                                            <select name="status" class="form-select">
                                                                <option value="active"
                                                                    {{ $doc->status == 'active' ? 'selected' : '' }}>Active
                                                                </option>
                                                                <option value="inactive"
                                                                    {{ $doc->status == 'inactive' ? 'selected' : '' }}>
                                                                    Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Tiêu Đề Tài Liệu <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" name="title" class="form-control"
                                                            value="{{ $doc->title }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Nội Dung Văn Bản Tri Thức <span
                                                                class="text-danger">*</span></label>
                                                        <textarea name="content" class="form-control" rows="6" required>{{ $doc->content }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-warning fw-bold px-4">Lưu &
                                                        Re-chunk</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Xóa Tài Liệu -->
                                <div class="modal fade text-start" id="deleteDocModal{{ $doc->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content">
                                            <form action="{{ route('labeler.knowledge.document.delete', $doc->id) }}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-body text-center p-4">
                                                    <i
                                                        class="bi bi-exclamation-triangle-fill text-danger fs-1 d-block mb-2"></i>
                                                    <h6 class="fw-bold mb-2">Xác nhận xóa tài liệu?</h6>
                                                    <p class="text-muted-labeler small mb-3">
                                                        Bạn có chắc muốn loại bỏ <strong>'{{ $doc->title }}'</strong>
                                                        khỏi cơ sở tri thức? Hành động này sẽ xóa toàn bộ các đoạn Chunks
                                                        liên quan.
                                                    </p>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-outline-secondary w-50"
                                                            data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" class="btn btn-danger w-50 fw-bold">Xóa
                                                            Ngay</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted-labeler">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Không có tài liệu tri thức nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tạo Kho Tri Thức -->
    <div class="modal fade" id="createBaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('labeler.knowledge.base.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus text-primary me-2"></i> Tạo Kho Tri
                            Thức RAG Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên Kho Tri Thức <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                placeholder="Ví dụ: Cơ Sở Tri Thức Kỹ Thuật Trồng Vải 2026" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô Tả Tổng Quan Kho Tri Thức</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Nhập mô tả phạm vi tài liệu trong kho..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary-gradient px-4">Khởi Tạo Kho</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Thêm Tài Liệu & Auto-Chunking -->
    <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('labeler.knowledge.document.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-plus-fill text-primary me-2"></i>
                            Thêm Tài Liệu Tri Thức & Auto-Chunking</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Chọn Kho Tri Thức RAG <span class="text-danger">*</span></label>
                            <select name="knowledge_base_id" class="form-select" required>
                                @foreach ($knowledgeBases as $kb)
                                    <option value="{{ $kb->id }}">{{ $kb->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tiêu Đề Tài Liệu Tri Thức <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                placeholder="Ví dụ: Kỹ thuật bón phân đợt 2 cho bưởi Diễn" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội Dung Văn Bản Tri Thức <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="6"
                                placeholder="Dán văn bản tư vấn kỹ thuật nông nghiệp tại đây. Hệ thống sẽ tự động tách thành các Chunks vector..."
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary-gradient px-4">
                            <i class="bi bi-diagram-3-fill me-1"></i> Nạp & Tự Động Chia Chunks
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Xem Chi Tiết Chunks (View Chunks Preview Modal) -->
    <div class="modal fade" id="viewChunksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-diagram-3-fill text-primary me-2"></i> Chi Tiết Các
                        Đoạn Chunks Tri Thức</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 480px; overflow-y: auto;">
                    <h6 class="fw-bold text-primary mb-2" id="chunk-doc-title">Tài Liệu: ...</h6>
                    <div id="chunks-container" class="d-flex flex-column gap-2">
                        <!-- Dynamic Chunks -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function viewDocumentChunks(docId) {
            fetch(`/labeler/knowledge/document/${docId}/chunks`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('chunk-doc-title').textContent = data.document.title;
                        const container = document.getElementById('chunks-container');
                        container.innerHTML = '';

                        if (data.chunks.length === 0) {
                            container.innerHTML =
                                '<div class="text-center text-muted-labeler py-3">Chưa có chunk nào.</div>';
                        } else {
                            data.chunks.forEach((chunk, idx) => {
                                const box = document.createElement('div');
                                box.className = 'p-3 rounded-3';
                                box.style.background = '#f8fafc';
                                box.style.border = '1px solid #e2e8f0';

                                box.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge badge-soft-primary mono">Chunk #${idx + 1} (${chunk.vector_id || 'Vector ID'})</span>
                                <span class="small mono text-muted-labeler" style="font-size: 11px;">Tokens: ${chunk.token_count || 0}</span>
                            </div>
                            <div class="small" style="line-height: 1.6;">${chunk.chunk_text}</div>
                        `;
                                container.appendChild(box);
                            });
                        }

                        const modal = new bootstrap.Modal(document.getElementById('viewChunksModal'));
                        modal.show();
                    }
                })
                .catch(err => {
                    alert('Lỗi nạp danh sách chunks!');
                    console.error(err);
                });
        }
    </script>
@endpush
