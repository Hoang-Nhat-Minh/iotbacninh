@extends('layouts.app')

@section('title', 'Log Lỗi Hệ Thống & Cảnh Báo Thiết Bị')

@section('content')
    <x-page-header title="Nhật Ký Lỗi & Cảnh Báo Thiết Bị">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Hệ thống</span>
            <span>/</span>
            <span class="text-primary fw-bold">Log lỗi hệ thống</span>
        </x-slot:breadcrumbs>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('system.error_logs') }}" class="row g-3 align-items-center mb-4">
                <div class="col-lg-6 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Nội dung thông điệp, tên trạm, file..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3">
                    <select name="level" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả mức độ --</option>
                        <option value="CRITICAL" {{ request('level') == 'CRITICAL' ? 'selected' : '' }}>CRITICAL (Nghiêm
                            trọng)</option>
                        <option value="ERROR" {{ request('level') == 'ERROR' ? 'selected' : '' }}>ERROR (Lỗi)</option>
                        <option value="WARNING" {{ request('level') == 'WARNING' ? 'selected' : '' }}>WARNING (Cảnh báo)
                        </option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Lọc</button>
                    @if (request('search') || request('level'))
                        <a href="{{ route('system.error_logs') }}" class="btn btn-secondary" title="Đặt lại bộ lọc"><i
                                class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Mức độ</th>
                            <th>Thông điệp lỗi</th>
                            <th>Nguồn phát sinh</th>
                            <th>Thời gian ghi nhận</th>
                            <th style="width: 100px; text-align: center;">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $i => $l)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $logs->firstItem() + $i }}</td>
                                <td>
                                    @if ($l->level === 'CRITICAL')
                                        <span class="badge bg-danger"><i class="bi bi-fire"></i> CRITICAL</span>
                                    @elseif($l->level === 'ERROR')
                                        <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> ERROR</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i
                                                class="bi bi-exclamation-triangle-fill"></i> WARNING</span>
                                    @endif
                                </td>
                                <td>
                                    <code class="text-dark fw-bold" style="font-size: 13px;">{{ $l->message }}</code>
                                </td>
                                <td><span
                                        class="badge bg-light text-dark border px-2 py-1">{{ $l->file ?? 'System Kernel' }}:{{ $l->line ?? 0 }}</span>
                                </td>
                                <td class="text-muted small">
                                    {{ $l->created_at ? $l->created_at->format('d/m/Y H:i:s') : '' }}</td>
                                <td style="text-align: center;">
                                    <button class="btn btn-secondary btn-sm px-2 py-1"
                                        onclick="openLogDetailModal('{{ $l->level }}', '{{ addslashes($l->message) }}', '{{ $l->file ?? '' }}', '{{ $l->created_at ? $l->created_at->format('d/m/Y H:i:s') : '' }}', '{{ addslashes($l->context ? json_encode($l->context) : '{}') }}')">
                                        <i class="bi bi-eye"></i> Xem
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-check2-circle text-success"></i></div>
                                        <h6 class="fw-bold mb-1">Hệ thống đang hoạt động an toàn & ổn định</h6>
                                        <p class="text-muted small mb-0">Không có lỗi hoặc cảnh báo nghiêm trọng nào phát
                                            sinh gần đây</p>
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
                    {{ $logs->total() }} log
                </span>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="app-modal" id="modal-log-detail">
        <div class="modal-dialog" style="max-width: 600px;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-code-square text-primary"></i> Chi Tiết Log Lỗi Hệ Thống</h5>
                <button type="button" class="modal-close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Mức độ & Thời gian</label>
                    <div class="d-flex align-items-center gap-2">
                        <span id="log-detail-level" class="badge bg-danger"></span>
                        <span id="log-detail-time" class="text-dark fw-semibold"></span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Thông điệp lỗi</label>
                    <div id="log-detail-message" class="p-3 bg-light border rounded fw-bold text-danger font-monospace"
                        style="font-size: 13px;"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Nguồn phát sinh</label>
                    <input type="text" id="log-detail-source" class="form-control bg-light" readonly>
                </div>
                <div>
                    <label class="form-label text-muted small">Dữ liệu Context (JSON)</label>
                    <pre id="log-detail-context" class="p-3 bg-dark text-light rounded font-monospace"
                        style="font-size: 12px; max-height: 180px; overflow-y: auto;"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-modal-close">Đóng</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openLogDetailModal(level, message, source, time, context) {
            document.getElementById('log-detail-level').textContent = level;
            document.getElementById('log-detail-time').textContent = time;
            document.getElementById('log-detail-message').textContent = message;
            document.getElementById('log-detail-source').value = source;
            try {
                const parsed = JSON.parse(context);
                document.getElementById('log-detail-context').textContent = JSON.stringify(parsed, null, 2);
            } catch (e) {
                document.getElementById('log-detail-context').textContent = context;
            }
            openModal('modal-log-detail');
        }
    </script>
@endpush
