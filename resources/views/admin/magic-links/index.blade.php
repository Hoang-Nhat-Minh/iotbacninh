@extends('layouts.app')

@section('title', 'Quản Lý Magic Link')

@section('content')
    <x-page-header title="Quản Lý Magic Link">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span class="text-primary fw-bold">Magic Link</span>
        </x-slot:breadcrumbs>

        <x-slot:actions>
            <a href="{{ route('admin.magic-links.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tạo Magic Link Mới
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="text-muted small mb-1">Tổng magic link</div>
                <h3 class="fw-bold text-primary mb-0">{{ $magicLinks->total() }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="text-muted small mb-1">Đang hoạt động</div>
                <h3 class="fw-bold text-success mb-0">{{ $magicLinks->where('is_active', true)->count() }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="text-muted small mb-1">Đã vô hiệu hóa</div>
                <h3 class="fw-bold text-danger mb-0">{{ $magicLinks->where('is_active', false)->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.magic-links.index') }}" class="row g-3 align-items-center mb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm theo token, tên, người dùng..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <select name="is_active" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Đã vô hiệu hóa</option>
                    </select>
                </div>
                <div class="col-lg-5 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Lọc</button>
                    @if (request('search') || request('is_active'))
                        <a href="{{ route('admin.magic-links.index') }}" class="btn btn-secondary" title="Đặt lại bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a>
                    @endif
                </div>
            </form>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Tên / Mô tả</th>
                            <th>Người dùng</th>
                            <th>Magic Link</th>
                            <th style="width: 110px;">Trạng thái</th>
                            <th style="width: 100px;">Lượt dùng</th>
                            <th style="width: 130px;">Lần dùng gần nhất</th>
                            <th style="width: 120px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($magicLinks as $i => $link)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $magicLinks->firstItem() + $i }}</td>
                                <td class="fw-bold text-dark">{{ $link->name ?? 'Không tên' }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $link->user->name ?? 'Người dùng' }}</div>
                                    <small class="text-muted">{{ $link->user->username ?? '' }}</small>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm mb-1">
                                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $link->url }}" readonly>
                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyLink(this)" title="Sao chép link"><i class="bi bi-copy"></i></button>
                                    </div>
                                    <small class="text-muted d-block">{{ $link->expires_at ? \Carbon\Carbon::parse($link->expires_at)->format('d/m/Y H:i') . ' hết hạn' : 'Vĩnh viễn' }}</small>
                                </td>
                                <td>
                                    @if ($link->is_active)
                                        <span class="badge bg-success"><i class="bi bi-toggle-on"></i> Hoạt động</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-toggle-off"></i> Vô hiệu</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $link->used_count }}</span>
                                </td>
                                <td>
                                    @if ($link->last_used_at)
                                        {{ \Carbon\Carbon::parse($link->last_used_at)->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted small">Chưa dùng</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.magic-links.toggle', $link->id) }}" method="POST" class="d-inline" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-{{ $link->is_active ? 'warning' : 'success' }} btn-sm" onclick="return confirm('Bạn có chắc muốn {{ $link->is_active ? 'vô hiệu hóa' : 'kích hoạt' }} magic link này?')">
                                            <i class="bi bi-{{ $link->is_active ? 'toggle-off' : 'toggle-on' }}"></i> {{ $link->is_active ? 'Tắt' : 'Bật' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.magic-links.edit', $link->id) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('admin.magic-links.destroy', $link->id) }}" method="POST" class="d-inline" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa magic link này?')"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="bi bi-link-45deg"></i></div>
                                        <h6 class="fw-bold mb-1">Chưa có magic link nào</h6>
                                        <p class="text-muted small mb-0">Hãy tạo magic link đầu tiên để chia sẻ đăng nhập một lần cho người dùng.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top" style="border-color: var(--border-color) !important;">
                <span class="text-muted" style="font-size: 14px;">
                    Hiển thị {{ $magicLinks->firstItem() ?? 0 }} - {{ $magicLinks->lastItem() ?? 0 }} trên tổng số {{ $magicLinks->total() }} magic link
                </span>
                <div>
                    {{ $magicLinks->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function copyLink(button) {
        const input = button.parentElement.querySelector('input');
        input.select();
        document.execCommand('copy');
        const original = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check2"></i>';
        button.classList.replace('btn-outline-secondary', 'btn-success');
        setTimeout(() => {
            button.innerHTML = original;
            button.classList.replace('btn-success', 'btn-outline-secondary');
        }, 1500);
    }
</script>
@endpush