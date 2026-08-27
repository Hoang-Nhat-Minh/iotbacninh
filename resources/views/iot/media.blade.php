@extends('layouts.app')

@section('title', 'Giám Sát Hình Ảnh & Video Camera IoT')

@section('content')
<x-page-header title="Kho Ảnh & Video Camera IoT">
    <x-slot:breadcrumbs>
        <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
        <span>/</span>
        <span>IoT</span>
        <span>/</span>
        <span class="text-primary fw-bold">Kho đa phương tiện</span>
    </x-slot:breadcrumbs>
</x-page-header>

<div class="card">
    <div class="card-header p-2">
        <ul class="nav nav-pills" id="mediaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4" id="photos-tab" data-bs-toggle="pill" data-bs-target="#photos-content" type="button">
                    <i class="bi bi-images me-1"></i> Hình Ảnh (Photos) <span class="badge bg-light text-dark ms-1">4</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4" id="videos-tab" data-bs-toggle="pill" data-bs-target="#videos-content" type="button">
                    <i class="bi bi-camera-reels me-1"></i> Video Streaming <span class="badge bg-light text-dark ms-1">2</span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="mediaTabsContent">
            <div class="tab-pane fade show active" id="photos-content" role="tabpanel">
                <div class="row g-3">
                    @php
                        $photos = [
                            [
                                'id' => 1,
                                'name' => 'CAM_TT01_20260814_063000.jpg',
                                'station' => 'Trạm Thuận Thành (TT-01)',
                                'time' => '14/08/2026 06:30',
                                'url' => 'https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=600&h=400'
                            ],
                            [
                                'id' => 2,
                                'name' => 'CAM_GB02_20260814_060000.jpg',
                                'station' => 'Trạm Gia Bình (GB-02)',
                                'time' => '14/08/2026 06:00',
                                'url' => 'https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=600&h=400'
                            ],
                            [
                                'id' => 3,
                                'name' => 'CAM_LT03_20260813_173000.jpg',
                                'station' => 'Trạm Lương Tài (LT-03)',
                                'time' => '13/08/2026 17:30',
                                'url' => 'https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=600&h=400'
                            ],
                            [
                                'id' => 4,
                                'name' => 'CAM_QV04_20260813_063000.jpg',
                                'station' => 'Trạm Quế Võ (QV-04)',
                                'time' => '13/08/2026 06:30',
                                'url' => 'https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=600&h=400'
                            ]
                        ];
                    @endphp

                    @foreach($photos as $p)
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 border shadow-sm">
                                <img src="{{ $p['url'] }}" alt="{{ $p['name'] }}" class="card-img-top" style="height: 180px; object-fit: cover; cursor: pointer;" onclick="openViewMediaModal('image', '{{ $p['url'] }}', '{{ $p['name'] }}')">
                                <div class="card-body p-3">
                                    <div class="fw-bold text-truncate text-dark" style="font-size: 13px;" title="{{ $p['name'] }}">{{ $p['name'] }}</div>
                                    <div class="text-muted small mb-2">{{ $p['station'] }} &bull; {{ $p['time'] }}</div>
                                    <div class="d-flex justify-content-between pt-2 border-top">
                                        <button class="btn btn-secondary btn-sm py-1 px-2" onclick="openRenameMediaModal({{ $p['id'] }}, '{{ $p['name'] }}')">
                                            <i class="bi bi-pencil"></i> Đổi tên
                                        </button>
                                        <button class="btn btn-secondary btn-sm py-1 px-2 text-danger" onclick="openDeleteMediaModal({{ $p['id'] }}, '{{ $p['name'] }}')">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="tab-pane fade" id="videos-content" role="tabpanel">
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <div class="card border shadow-sm">
                            <div class="p-4 bg-dark text-white text-center rounded-top" style="height: 180px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer;" onclick="showToast('Đang mở trình phát video camera...', 'info')">
                                <i class="bi bi-play-circle-fill text-danger" style="font-size: 54px;"></i>
                                <span class="small mt-2">Thời lượng: 00:15:00</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="fw-bold text-dark" style="font-size: 14px;">REC_TT01_20260814_0600.mp4</div>
                                <div class="text-muted small mb-2">Trạm Thuận Thành &bull; 14/08/2026 06:00</div>
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <button class="btn btn-secondary btn-sm py-1 px-2" onclick="openRenameMediaModal(101, 'REC_TT01_20260814_0600.mp4')"><i class="bi bi-pencil"></i> Đổi tên</button>
                                    <button class="btn btn-secondary btn-sm py-1 px-2 text-danger" onclick="openDeleteMediaModal(101, 'REC_TT01_20260814_0600.mp4')"><i class="bi bi-trash"></i> Xóa</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card border shadow-sm">
                            <div class="p-4 bg-dark text-white text-center rounded-top" style="height: 180px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer;" onclick="showToast('Đang mở trình phát video camera...', 'info')">
                                <i class="bi bi-play-circle-fill text-danger" style="font-size: 54px;"></i>
                                <span class="small mt-2">Thời lượng: 00:15:00</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="fw-bold text-dark" style="font-size: 14px;">REC_GB02_20260814_0600.mp4</div>
                                <div class="text-muted small mb-2">Trạm Gia Bình &bull; 14/08/2026 06:00</div>
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <button class="btn btn-secondary btn-sm py-1 px-2" onclick="openRenameMediaModal(102, 'REC_GB02_20260814_0600.mp4')"><i class="bi bi-pencil"></i> Đổi tên</button>
                                    <button class="btn btn-secondary btn-sm py-1 px-2 text-danger" onclick="openDeleteMediaModal(102, 'REC_GB02_20260814_0600.mp4')"><i class="bi bi-trash"></i> Xóa</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-modal" id="modal-view-media">
    <div class="modal-dialog" style="max-width: 700px;">
        <div class="modal-header">
            <h5 class="modal-title" id="view-media-title">Xem Nội Dung Media</h5>
            <button type="button" class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body text-center p-0">
            <img id="view-media-img" src="" alt="media" class="img-fluid" style="max-height: 500px; width: 100%; object-fit: contain;">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-modal-close">Đóng</button>
        </div>
    </div>
</div>

<div class="app-modal" id="modal-rename-media">
    <div class="modal-dialog" style="max-width: 440px;">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-pencil text-primary"></i> Đổi Tên File Media</h5>
            <button type="button" class="modal-close-btn">&times;</button>
        </div>
        <form action="{{ url('/iot/media/rename') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="rename-media-id">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tên file mới</label>
                    <input type="text" name="name" id="rename-media-name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu Tên Mới</button>
            </div>
        </form>
    </div>
</div>

<div class="app-modal" id="modal-delete-media">
    <div class="modal-dialog" style="max-width: 420px;">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa File Khỏi Kho</h5>
            <button type="button" class="modal-close-btn">&times;</button>
        </div>
        <form action="{{ url('/iot/media/delete') }}" method="POST">
            @csrf
            <div class="modal-body text-center py-4">
                <p>Bạn có chắc muốn xóa file: <br><strong id="delete-media-name" class="text-danger"></strong>?</p>
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
function openViewMediaModal(type, url, name) {
    document.getElementById('view-media-title').textContent = name;
    document.getElementById('view-media-img').src = url;
    openModal('modal-view-media');
}
function openRenameMediaModal(id, name) {
    document.getElementById('rename-media-id').value = id;
    document.getElementById('rename-media-name').value = name;
    openModal('modal-rename-media');
}
function openDeleteMediaModal(id, name) {
    document.getElementById('delete-media-name').textContent = name;
    openModal('modal-delete-media');
}
</script>
@endpush
