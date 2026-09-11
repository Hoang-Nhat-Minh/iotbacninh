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

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('iot.media') }}" class="row g-2 align-items-center">
            <div class="col-md-4 col-sm-6">
                <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-geo-alt me-1"></i> Lọc theo Trạm quan trắc:</label>
                <select name="station_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả các trạm --</option>
                    @foreach($stations as $st)
                        <option value="{{ $st->id }}" {{ request('station_id') == $st->id ? 'selected' : '' }}>
                            {{ $st->name }} ({{ $st->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-sm-6">
                <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-camera me-1"></i> Lọc theo Camera:</label>
                <select name="device_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả camera --</option>
                    @foreach($stations as $st)
                        @if(!request('station_id') || request('station_id') == $st->id)
                            <optgroup label="{{ $st->name }}">
                                @foreach($st->devices as $dev)
                                    <option value="{{ $dev->id }}" {{ request('device_id') == $dev->id ? 'selected' : '' }}>
                                        {{ $dev->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-sm-12 d-flex align-items-end gap-2 mt-md-4">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-funnel me-1"></i> Lọc
                </button>
                @if(request()->hasAny(['station_id', 'device_id']))
                    <a href="{{ route('iot.media') }}" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="bi bi-x-circle me-1"></i> Đặt lại
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header p-2 bg-white border-bottom">
        <ul class="nav nav-pills" id="mediaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active px-4" id="photos-tab" data-bs-toggle="pill" data-bs-target="#photos-content" type="button">
                    <i class="bi bi-images me-1"></i> Hình Ảnh (Photos) <span class="badge bg-primary text-white ms-1">{{ $images->total() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4" id="videos-tab" data-bs-toggle="pill" data-bs-target="#videos-content" type="button">
                    <i class="bi bi-camera-reels me-1"></i> Video Streaming <span class="badge bg-secondary text-white ms-1">{{ $videos->total() }}</span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-4">
        <div class="tab-content" id="mediaTabsContent">
            <div class="tab-pane fade show active" id="photos-content" role="tabpanel">
                <div class="row g-3">
                    @forelse($images as $img)
                        @php
                            $imgUrl = asset('storage/' . $img->file_path);
                            $stName = $img->device->monitoringStation->name ?? 'Trạm quan trắc';
                            $camName = $img->device->name ?? 'Camera';
                            $timeStr = $img->created_at ? $img->created_at->format('d/m/Y H:i:s') : '';
                        @endphp
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="card h-100 border shadow-sm hover-shadow transition">
                                <div class="position-relative">
                                    <img src="{{ $imgUrl }}" alt="{{ $img->name }}" class="card-img-top"
                                         style="height: 180px; object-fit: cover; cursor: pointer;"
                                         onclick="openViewMediaModal('image', '{{ $imgUrl }}', '{{ addslashes($img->name) }}')"
                                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=600&h=400';">
                                    <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-2 px-2 py-1 font-monospace" style="font-size: 11px;">
                                        <i class="bi bi-camera-video me-1"></i>{{ $camName }}
                                    </span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="fw-bold text-truncate text-dark mb-1" style="font-size: 13px;" title="{{ $img->name }}">
                                            {{ $img->name }}
                                        </div>
                                        <div class="text-muted small mb-1">
                                            <i class="bi bi-geo-alt text-danger me-1"></i>{{ $stName }}
                                        </div>
                                        <div class="text-muted small font-monospace" style="font-size: 11px;">
                                            <i class="bi bi-clock me-1"></i>{{ $timeStr }}
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top gap-1">
                                        <a href="{{ $imgUrl }}" download="{{ $img->name }}" class="btn btn-outline-primary btn-sm py-1 px-2" title="Tải về máy">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <button class="btn btn-outline-secondary btn-sm py-1 px-2" onclick="openRenameMediaModal({{ $img->id }}, '{{ addslashes($img->name) }}')" title="Đổi tên">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm py-1 px-2" onclick="openDeleteMediaModal({{ $img->id }}, '{{ addslashes($img->name) }}')" title="Xóa file">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="bi bi-image" style="font-size: 48px;"></i>
                            <div class="mt-2 fw-medium">Chưa có ảnh chụp camera nào phù hợp bộ lọc.</div>
                            <small>Hãy vào màn hình giám sát Camera của trạm để thực hiện chụp ảnh trực tiếp.</small>
                        </div>
                    @endforelse
                </div>

                @if($images->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $images->links() }}
                    </div>
                @endif
            </div>

            <div class="tab-pane fade" id="videos-content" role="tabpanel">
                <div class="row g-3">
                    @forelse($videos as $vid)
                        @php
                            $vidUrl = asset('storage/' . $vid->file_path);
                            $stName = $vid->device->monitoringStation->name ?? 'Trạm quan trắc';
                            $camName = $vid->device->name ?? 'Camera';
                            $timeStr = $vid->created_at ? $vid->created_at->format('d/m/Y H:i') : '';
                        @endphp
                        <div class="col-lg-4 col-md-6">
                            <div class="card border shadow-sm">
                                <div class="p-4 bg-dark text-white text-center rounded-top" style="height: 180px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer;" onclick="openViewMediaModal('video', '{{ $vidUrl }}', '{{ addslashes($vid->name) }}')">
                                    <i class="bi bi-play-circle-fill text-danger" style="font-size: 54px;"></i>
                                    <span class="small mt-2 font-monospace">{{ $camName }}</span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 14px;">{{ $vid->name }}</div>
                                    <div class="text-muted small mb-2">{{ $stName }} &bull; {{ $timeStr }}</div>
                                    <div class="d-flex justify-content-between pt-2 border-top">
                                        <a href="{{ $vidUrl }}" download class="btn btn-outline-primary btn-sm py-1 px-2"><i class="bi bi-download"></i> Tải về</a>
                                        <button class="btn btn-outline-secondary btn-sm py-1 px-2" onclick="openRenameMediaModal({{ $vid->id }}, '{{ addslashes($vid->name) }}')"><i class="bi bi-pencil"></i> Đổi tên</button>
                                        <button class="btn btn-outline-danger btn-sm py-1 px-2" onclick="openDeleteMediaModal({{ $vid->id }}, '{{ addslashes($vid->name) }}')"><i class="bi bi-trash"></i> Xóa</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="bi bi-camera-reels" style="font-size: 48px;"></i>
                            <div class="mt-2 fw-medium">Chưa có video ghi hình nào được lưu trữ.</div>
                        </div>
                    @endforelse
                </div>

                @if($videos->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $videos->links() }}
                    </div>
                @endif
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
