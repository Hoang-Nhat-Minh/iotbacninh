@extends('layouts.app')

@section('title', 'Quản Lý Tọa Độ Góc Chụp PTZ Camera')

@section('content')
    <x-page-header title="Tọa Độ Góc Chụp Camera PTZ">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>IoT</span>
            <span>/</span>
            <span class="text-primary fw-bold">Tọa độ chụp PTZ</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <button type="button" class="btn btn-primary" onclick="openModal('modal-add-location')">
                <i class="bi bi-plus-circle-fill me-1"></i> Thêm Góc Chụp PTZ
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="custom-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">STT</th>
                            <th>Tên điểm góc chụp</th>
                            <th>Trạm quan trắc</th>
                            <th>Camera</th>
                            <th>Lịch trình</th>
                            <th>Góc Pan</th>
                            <th>Góc Tilt</th>
                            <th>Mức Zoom</th>
                            <th>Trạng thái</th>
                            <th style="width: 120px; text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($locations as $i => $loc)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $i + 1 }}</td>
                                <td class="fw-bold text-dark">{{ $loc->name }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <i class="bi bi-broadcast"></i>
                                        {{ $loc->monitoringStation->name ?? 'Trạm #' . $loc->monitoring_station_id }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
                                        {{ $loc->camera_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($loc->schedule)
                                        <span class="badge bg-light text-secondary border font-monospace" title="{{ $loc->schedule->name }}">
                                            <i class="bi bi-clock me-1 text-primary"></i>{{ substr($loc->schedule->start_time, 0, 5) }} - {{ substr($loc->schedule->end_time, 0, 5) }}
                                        </span>
                                    @else
                                        <span class="text-muted small">--</span>
                                    @endif
                                </td>
                                <td><code
                                        class="font-monospace text-primary fw-bold">{{ number_format($loc->pan_angle, 1) }}°</code>
                                </td>
                                <td><code
                                        class="font-monospace text-warning fw-bold">{{ number_format($loc->tilt_angle, 1) }}°</code>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace">
                                        {{ number_format($loc->zoom_level, 1) }}x
                                    </span>
                                </td>
                                <td>
                                    @if (($loc->status ?? 'active') === 'active')
                                        <span class="badge-status badge-active"><i class="bi bi-check-circle-fill"></i> Sẵn
                                            sàng</span>
                                    @else
                                        <span class="badge-status badge-inactive"><i class="bi bi-pause-circle-fill"></i>
                                            Tạm ngưng</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-secondary btn-icon btn-sm text-success" title="Quay PTZ & Chụp ảnh góc này"
                                            onclick="testCaptureLocation(event, {{ $loc->id }}, '{{ addslashes($loc->name) }}')">
                                            <i class="bi bi-camera-fill"></i>
                                        </button>
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Sửa góc chụp"
                                            onclick="openEditLocationModal({{ $loc->id }}, '{{ addslashes($loc->name) }}', {{ $loc->pan_angle ?? 0.0 }}, {{ $loc->tilt_angle ?? 0.0 }}, {{ $loc->zoom_level ?? 1.0 }}, '{{ $loc->camera_id ?? 'cam_1' }}', '{{ $loc->schedule_id ?? '' }}')">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </button>
                                        <button class="btn btn-secondary btn-icon btn-sm" title="Xóa góc chụp"
                                            onclick="openDeleteLocationModal({{ $loc->id }}, '{{ addslashes($loc->name) }}')">
                                            <i class="bi bi-trash3 text-danger"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Chưa có điểm góc chụp PTZ nào trong
                                    cơ sở dữ liệu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="app-modal" id="modal-add-location">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-camera-reels text-primary"></i> Thêm Góc Chụp PTZ Mới</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form action="{{ url('/iot/locations/store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên điểm góc chụp <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                            placeholder="Ví dụ: Preset #5: Luống Dưa Leo Phía Nam" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạm quan trắc <span class="text-danger">*</span></label>
                        <select name="monitoring_station_id" class="form-select" required>
                            @foreach ($stations as $st)
                                <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Camera áp dụng <span class="text-danger">*</span></label>
                            <select name="camera_id" class="form-select" required>
                                <option value="cam_1">Camera 01</option>
                                <option value="cam_2">Camera 02</option>
                                <option value="cam_3">Camera 03</option>
                                <option value="cam_4">Camera 04</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lịch trình tự động</label>
                            <select name="schedule_id" class="form-select">
                                <option value="">-- Không gắn lịch trình --</option>
                                @foreach($schedules ?? [] as $sch)
                                    <option value="{{ $sch->id }}">
                                        {{ $sch->name }} ({{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Góc Pan (°)</label>
                            <input type="number" step="0.1" min="-180" max="180" name="pan_angle"
                                class="form-control" placeholder="0.0" value="0.0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Góc Tilt (°)</label>
                            <input type="number" step="0.1" min="-45" max="45" name="tilt_angle"
                                class="form-control" placeholder="0.0" value="0.0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Zoom Level</label>
                            <input type="number" step="0.1" min="1" max="4" name="zoom_level"
                                class="form-control" placeholder="1.0" value="1.0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu Góc Chụp</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-edit-location">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil text-primary"></i> Sửa Tọa Độ Góc Chụp</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-edit-location" action="{{ url('/iot/locations/update') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="edit-location-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên điểm góc chụp</label>
                        <input type="text" name="name" id="edit-location-name" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Camera áp dụng <span class="text-danger">*</span></label>
                            <select name="camera_id" id="edit-location-cam" class="form-select" required>
                                <option value="cam_1">Camera 01</option>
                                <option value="cam_2">Camera 02</option>
                                <option value="cam_3">Camera 03</option>
                                <option value="cam_4">Camera 04</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lịch trình tự động</label>
                            <select name="schedule_id" id="edit-location-schedule" class="form-select">
                                <option value="">-- Không gắn lịch trình --</option>
                                @foreach($schedules ?? [] as $sch)
                                    <option value="{{ $sch->id }}">
                                        {{ $sch->name }} ({{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Góc Pan (°)</label>
                            <input type="number" step="0.1" min="-180" max="180" name="pan_angle"
                                id="edit-location-pan" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Góc Tilt (°)</label>
                            <input type="number" step="0.1" min="-45" max="45" name="tilt_angle"
                                id="edit-location-tilt" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Zoom Level</label>
                            <input type="number" step="0.1" min="1" max="4" name="zoom_level"
                                id="edit-location-zoom" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập Nhật</button>
                </div>
            </form>
        </div>
    </div>

    <div class="app-modal" id="modal-delete-location">
        <div class="modal-dialog" style="max-width: 420px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Điểm Chụp</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <form id="form-delete-location" action="{{ url('/iot/locations/delete') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="delete-location-id">
                <div class="modal-body text-center py-4">
                    <p>Bạn có chắc muốn xóa điểm góc chụp: <br><strong id="delete-location-name"
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
        function openEditLocationModal(id, name, pan, tilt, zoom, cameraId = 'cam_1', scheduleId = '') {
            const form = document.getElementById('form-edit-location');
            if (form) form.action = '{{ url("/iot/locations/update") }}/' + id;
            document.getElementById('edit-location-id').value = id;
            document.getElementById('edit-location-name').value = name;
            document.getElementById('edit-location-pan').value = pan;
            document.getElementById('edit-location-tilt').value = tilt;
            document.getElementById('edit-location-zoom').value = zoom;
            const camSelect = document.getElementById('edit-location-cam');
            if (camSelect) camSelect.value = cameraId || 'cam_1';
            const schSelect = document.getElementById('edit-location-schedule');
            if (schSelect) schSelect.value = scheduleId || '';
            openModal('modal-edit-location');
        }

        function openDeleteLocationModal(id, name) {
            const form = document.getElementById('form-delete-location');
            if (form) form.action = '{{ url("/iot/locations/delete") }}/' + id;
            document.getElementById('delete-location-id').value = id;
            document.getElementById('delete-location-name').textContent = name;
            openModal('modal-delete-location');
        }

        function testCaptureLocation(event, id, name) {
            if (!confirm('Bạn có muốn kích hoạt quay camera và chụp ảnh thử nghiệm tại điểm: "' + name + '" không?')) {
                return;
            }
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm text-success" role="status"></span>';

            fetch('{{ url("/iot/locations/test-capture") }}/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                if (data.success) {
                    alert(data.message || 'Đã gửi lệnh xoay PTZ và chụp ảnh tới trạm thành công!');
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể gửi lệnh tới trạm.'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('Lỗi kết nối máy chủ: ' + err);
            });
        }
    </script>
@endpush
