@extends('layouts.app')

@section('title', 'Quản Lý Khung Thời Gian Gửi Dữ Liệu Ảnh')

@section('content')
<x-page-header title="Khung Giờ Chụp & Gửi Ảnh Camera">
    <x-slot:breadcrumbs>
        <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
        <span>/</span>
        <span>IoT</span>
        <span>/</span>
        <span class="text-primary fw-bold">Lịch chụp camera</span>
    </x-slot:breadcrumbs>

    <x-slot:actions>
        <button type="button" class="btn btn-primary" onclick="openModal('modal-add-schedule')">
            <i class="bi bi-plus-circle-fill me-1"></i> Thêm Khung Giờ
        </button>
    </x-slot:actions>
</x-page-header>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="custom-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>Tên khung giờ lịch trình</th>
                        <th>Trạm quan trắc</th>
                        <th>Thời gian bắt đầu</th>
                        <th>Thời gian kết thúc</th>
                        <th>Chu kỳ chụp</th>
                        <th>Trạng thái</th>
                        <th style="width: 120px; text-align: center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $i => $s)
                        <tr>
                            <td class="text-secondary fw-semibold">{{ $i + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $s->name }}</td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="bi bi-broadcast"></i> {{ $s->monitoringStation->name ?? 'Tất cả trạm' }}
                                </span>
                            </td>
                            <td><span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-clock"></i> {{ $s->start_time }}</span></td>
                            <td><span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-clock-history"></i> {{ $s->end_time }}</span></td>
                            <td><strong class="text-primary font-monospace">{{ $s->interval_minutes }}</strong> phút / lần</td>
                            <td>
                                <span class="badge-status {{ $s->status === 'active' ? 'badge-active' : 'badge-locked' }}">
                                    <i class="bi {{ $s->status === 'active' ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' }}"></i>
                                    {{ $s->status === 'active' ? 'Hoạt động' : 'Tạm dừng' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div class="d-inline-flex gap-1">
                                    <button class="btn btn-secondary btn-icon btn-sm" title="Sửa khung giờ"
                                            onclick="openEditScheduleModal({{ $s->id }}, '{{ $s->monitoring_station_id ?? '' }}', '{{ addslashes($s->name) }}', '{{ $s->start_time }}', '{{ $s->end_time }}', {{ $s->interval_minutes }}, '{{ $s->status }}')">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </button>
                                    <button class="btn btn-secondary btn-icon btn-sm" title="Xóa khung giờ"
                                            onclick="openDeleteScheduleModal({{ $s->id }}, '{{ addslashes($s->name) }}')">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state py-4">
                                    <div class="empty-state-icon"><i class="bi bi-clock"></i></div>
                                    <h6 class="fw-bold mb-1">Chưa có khung giờ chụp ảnh nào</h6>
                                    <p class="text-muted small mb-0">Nhấn nút thêm khung giờ để cấu hình chu kỳ chụp ảnh cho camera</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="app-modal" id="modal-add-schedule">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-clock-history text-primary"></i> Thêm Khung Giờ Gửi Ảnh Mới</h5>
            <button type="button" class="modal-close-btn">&times;</button>
        </div>
        <form action="{{ url('/iot/schedules/store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tên lịch trình <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Ví dụ: Chụp định kỳ sáng sớm" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trạm quan trắc áp dụng</label>
                    <select name="monitoring_station_id" class="form-select">
                        <option value="">-- Áp dụng cho tất cả trạm --</option>
                        @foreach($stations as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Giờ bắt đầu <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" required value="06:00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giờ kết thúc <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" required value="09:00">
                    </div>
                </div>
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Chu kỳ chụp (phút) <span class="text-danger">*</span></label>
                        <input type="number" name="interval_minutes" class="form-control" value="30" min="1" max="1440" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active" selected>Hoạt động</option>
                            <option value="inactive">Tạm dừng</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-modal-close">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu Khung Giờ</button>
            </div>
        </form>
    </div>
</div>

<div class="app-modal" id="modal-edit-schedule">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-pencil text-primary"></i> Chỉnh Sửa Khung Giờ</h5>
            <button type="button" class="modal-close-btn">&times;</button>
        </div>
        <form id="form-edit-schedule" action="" method="POST">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tên lịch trình</label>
                    <input type="text" name="name" id="edit-schedule-name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trạm quan trắc áp dụng</label>
                    <select name="monitoring_station_id" id="edit-schedule-station" class="form-select">
                        <option value="">-- Áp dụng cho tất cả trạm --</option>
                        @foreach($stations as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Giờ bắt đầu</label>
                        <input type="time" name="start_time" id="edit-schedule-start" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giờ kết thúc</label>
                        <input type="time" name="end_time" id="edit-schedule-end" class="form-control" required>
                    </div>
                </div>
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Chu kỳ chụp (phút)</label>
                        <input type="number" name="interval_minutes" id="edit-schedule-interval" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" id="edit-schedule-status" class="form-select">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Tạm dừng</option>
                        </select>
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

<div class="app-modal" id="modal-delete-schedule">
    <div class="modal-dialog" style="max-width: 420px;">
        <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Xóa Khung Giờ</h5>
            <button type="button" class="modal-close-btn">&times;</button>
        </div>
        <form id="form-delete-schedule" action="" method="POST">
            @csrf
            <div class="modal-body text-center py-4">
                <p>Bạn có chắc muốn xóa khung giờ <strong id="delete-schedule-name" class="text-danger"></strong>?</p>
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
function openEditScheduleModal(id, stationId, name, start, end, interval, status) {
    document.getElementById('form-edit-schedule').action = window.location.origin + '/iot/schedules/update/' + id;
    document.getElementById('edit-schedule-station').value = stationId;
    document.getElementById('edit-schedule-name').value = name;
    document.getElementById('edit-schedule-start').value = start;
    document.getElementById('edit-schedule-end').value = end;
    document.getElementById('edit-schedule-interval').value = interval;
    document.getElementById('edit-schedule-status').value = status;
    openModal('modal-edit-schedule');
}
function openDeleteScheduleModal(id, name) {
    document.getElementById('form-delete-schedule').action = window.location.origin + '/iot/schedules/delete/' + id;
    document.getElementById('delete-schedule-name').textContent = name;
    openModal('modal-delete-schedule');
}
</script>
@endpush
