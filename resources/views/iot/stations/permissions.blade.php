@extends('layouts.app')

@section('title', 'Phân Quyền Trạm Quan Trắc')

@push('styles')
<style>
    .perm-table th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0;
    }
    .perm-table td {
        vertical-align: middle;
        font-size: 13px;
    }
    .badge-role {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.04em;
    }
    .form-select-sm {
        padding: 4px 8px;
        font-size: 13px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }
</style>
@endpush

@section('content')
    <x-page-header title="Phân Quyền Trạm Quan Trắc" subtitle="Quản lý việc gán trạm quan trắc cho các chủ vườn (người dùng)">
        <x-slot:actions>
            <a href="{{ route('iot.stations') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Về danh sách trạm
            </a>
        </x-slot:actions>
    </x-page-header>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="fw-light mb-1">Tổng trạm</h6>
                            <h3 class="mb-0">{{ $stations->count() }}</h3>
                        </div>
                        <div class="align-self-end">
                            <i class="bi bi-tower fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="fw-light mb-1">Đã phân quyền</h6>
                            <h3 class="mb-0">{{ $stations->whereNotNull('garden_id')->count() }}</h3>
                        </div>
                        <div class="align-self-end">
                            <i class="bi bi-shield-check fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="fw-light mb-1">Chưa phân quyền</h6>
                            <h3 class="mb-0">{{ $stations->whereNull('garden_id')->count() }}</h3>
                        </div>
                        <div class="align-self-end">
                            <i class="bi bi-exclamation-circle fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="fw-light mb-1">Chủ vườn (User)</h6>
                            <h3 class="mb-0">{{ $users->count() }}</h3>
                        </div>
                        <div class="align-self-end">
                            <i class="bi bi-people fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng phân quyền -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-person-gear me-2"></i>Danh sách trạm & phân quyền</h5>
            <span class="badge bg-light text-dark fs-6">Chỉ Quản trị viên có thể thay đổi phân quyền</span>
        </div>
        <div class="card-body p-0">
            @if ($stations->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-tower-off fs-1 mb-3 opacity-25"></i>
                    <p class="mb-0">Chưa có trạm quan trắc nào trong hệ thống.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle perm-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Mã trạm</th>
                                <th style="width: 25%;">Tên trạm</th>
                                <th style="width: 20%;">Vườn hiện tại</th>
                                <th style="width: 18%;">Chủ vườn hiện tại</th>
                                <th style="width: 12%;">Trạng thái</th>
                                <th style="width: 10%;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stations as $station)
                                <tr>
                                    <td><span class="badge bg-light text-dark border font-monospace">{{ $station->code }}</span></td>
                                    <td><span class="fw-medium">{{ $station->name }}</span></td>
                                    <td>
                                        @if ($station->garden)
                                            <span class="badge bg-primary-subtle text-primary border">{{ $station->garden->name }}</span>
                                        @else
                                            <span class="badge bg-light text-muted border">Chưa có vườn</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($station->garden && $station->garden->user)
                                            <div>
                                                <div class="fw-medium">{{ $station->garden->user->name }}</div>
                                                <div class="badge-role bg-{{ $station->garden->user->isLocked() ? 'danger' : 'success' }}-subtle text-{{ $station->garden->user->isLocked() ? 'danger' : 'success' }} border">{{ $station->garden->user->isLocked() ? 'Đã khóa' : 'Người dùng' }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted small">Chưa gán</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusBadge = ['active' => 'bg-success text-white', 'inactive' => 'bg-secondary text-white', 'maintenance' => 'bg-warning text-dark', 'danger' => 'bg-danger text-white', 'offline' => 'bg-dark text-white'];
                                            $statusLabel = ['active' => 'Hoạt động', 'inactive' => 'Ngừng hoạt động', 'maintenance' => 'Bảo trì', 'danger' => 'Nguy hiểm', 'offline' => 'Mất kết nối'];
                                            $cls = $statusBadge[$station->status] ?? 'bg-secondary text-white';
                                            $lbl = $statusLabel[$station->status] ?? $station->status;
                                        @endphp
                                        <span class="badge {{ $cls }}">{{ $lbl }}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('iot.stations.permissions.store') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="station_id" value="{{ $station->id }}">
                                            <select name="garden_id" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                <option value="{{ $station->garden_id ?? '' }}" {{ ($station->garden_id ?? '') === '' ? 'selected' : '' }}>{{ $station->garden ? $station->garden->name : 'Giữ nguyên' }}</option>
                                                @foreach ($gardens as $garden)
                                                    <option value="{{ $garden->id }}" {{ $station->garden_id === $garden->id ? 'selected' : '' }}>{{ $garden->name }} ({{ $garden->user->name ?? 'Chưa có chủ' }})</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Danh sách chủ vườn tham khảo -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="card-title mb-0"><i class="bi bi-people me-2"></i>Danh sách chủ vườn (Role: User)</h6>
        </div>
        <div class="card-body">
            @if ($users->isEmpty())
                <p class="text-muted mb-0">Chưa có chủ vườn nào trong hệ thống.</p>
            @else
                <div class="row g-3">
                    @foreach ($users as $user)
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title fw-medium mb-1">{{ $user->name }}</h6>
                                        <span class="badge bg-{{ $user->isLocked() ? 'danger' : 'success' }}">{{ $user->isLocked() ? 'Đã khóa' : 'Hoạt động' }}</span>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        <i class="bi bi-telephone me-1"></i> {{ $user->phone ?? 'N/A' }}<br>
                                        <i class="bi bi-envelope me-1"></i> {{ $user->email ?? 'N/A' }}
                                    </p>
                                    <div class="small text-muted">
                                        <i class="bi bi-garden me-1"></i>
                                        {{ $user->gardens->count() }} vườn
                                        @foreach ($user->gardens as $garden)
                                            <span class="badge bg-light text-dark border ms-1">{{ $garden->code }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('form[action*="permissions.store"]').forEach(form => {
        const select = form.querySelector('select');
        if (select) {
            select.addEventListener('change', function() {
                if (!confirm('Bạn có chắc muốn thay đổi phân quyền trạm này?')) {
                    this.selectedIndex = 0;
                }
            });
        }
    });
</script>
@endpush