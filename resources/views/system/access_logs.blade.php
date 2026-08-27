@extends('layouts.app')

@section('title', 'Lịch Sử Đăng Nhập & Truy Cập Hệ Thống')

@section('content')
    <x-page-header title="Nhật Ký Truy Cập & Đăng Nhập">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Hệ thống</span>
            <span>/</span>
            <span class="text-primary fw-bold">Lịch sử đăng nhập</span>
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="text-muted small mb-1">Tổng lượt truy cập ghi nhận</div>
                <h3 class="fw-bold text-primary mb-0">{{ $logs->total() }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="text-muted small mb-1">Thiết bị Di động (Mobile App)</div>
                <h3 class="fw-bold text-success mb-0">65%</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="text-muted small mb-1">Trình duyệt Máy tính (Desktop Web)</div>
                <h3 class="fw-bold text-secondary mb-0">35%</h3>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('system.access_logs') }}" class="row g-3 align-items-center mb-4">
                <div class="col-lg-5 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Tên tài khoản, IP (14.232.x.x)..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3">
                    <select name="device_type" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả thiết bị --</option>
                        <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>Di động (Mobile
                            App)</option>
                        <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>Máy tính
                            (Desktop Web)</option>
                    </select>
                </div>

                <div class="col-lg-4 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Lọc Dữ Liệu</button>
                    @if (request('search') || request('device_type'))
                        <a href="{{ route('system.access_logs') }}" class="btn btn-secondary" title="Đặt lại bộ lọc"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Tài khoản</th>
                            <th>Thời gian đăng nhập</th>
                            <th>Địa chỉ IP</th>
                            <th>Vị trí ước tính</th>
                            <th>Thiết bị & Nền tảng</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $i => $a)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $logs->firstItem() + $i }}</td>
                                <td class="fw-bold text-dark">{{ $a->user->name ?? 'Người dùng' }}
                                    ({{ $a->user->username ?? '' }})
                                </td>
                                <td class="text-muted small"><i class="bi bi-clock"></i>
                                    {{ $a->login_at ? \Carbon\Carbon::parse($a->login_at)->format('d/m/Y H:i:s') : '' }}
                                </td>
                                <td><code class="text-primary">{{ $a->ip_address }}</code></td>
                                <td><i class="bi bi-geo-alt text-muted"></i> {{ $a->location ?? 'Bắc Ninh, Việt Nam' }}
                                </td>
                                <td>
                                    @if ($a->device_type === 'mobile')
                                        <span class="badge bg-light text-dark border"><i class="bi bi-phone"></i> Di
                                            động</span>
                                    @else
                                        <span class="badge bg-light text-dark border"><i class="bi bi-laptop"></i> Máy
                                            tính</span>
                                    @endif
                                </td>
                                <td>
                                    @if (!$a->logout_at)
                                        <span class="badge-status badge-active"><i class="bi bi-circle-fill"
                                                style="font-size: 8px;"></i> Đang online</span>
                                    @else
                                        <span class="badge-status badge-locked"><i class="bi bi-check2"></i> Đã đăng
                                            xuất</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-clock-history"></i></div>
                                        <h6 class="fw-bold mb-1">Chưa có nhật ký đăng nhập nào</h6>
                                        <p class="text-muted small mb-0">Lịch sử các phiên đăng nhập của người dùng sẽ hiển
                                            thị tại đây</p>
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
                    Hiển thị {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} trên tổng số
                    {{ $logs->total() }} lượt đăng nhập
                </span>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
