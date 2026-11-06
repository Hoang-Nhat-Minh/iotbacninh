@extends('layouts.app')

@section('title', 'Phân Quyền Trạm Quan Trắc')

@push('styles')
<style>
    /* ── Stats ─────────────────────────────────────────── */
    .stat-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 767px) { .stat-strip { grid-template-columns: repeat(2,1fr); } }
    .stat-card {
        border-radius: 14px;
        padding: 1rem 1.25rem;
        display: flex; align-items: center; gap: 1rem;
        color: #fff; border: none;
    }
    .stat-card .si { font-size: 1.9rem; opacity: .65; }
    .stat-card .sn { font-size: 1.75rem; font-weight: 700; line-height: 1; }
    .stat-card .sl { font-size: .76rem; opacity: .85; margin-top: 2px; }
    .sc-blue   { background: linear-gradient(135deg,#2563eb,#60a5fa); }
    .sc-green  { background: linear-gradient(135deg,#059669,#34d399); }
    .sc-amber  { background: linear-gradient(135deg,#b45309,#fbbf24); }
    .sc-purple { background: linear-gradient(135deg,#7c3aed,#c4b5fd); }

    /* ── Main card ──────────────────────────────────────── */
    .perm-card {
        background: var(--bs-body-bg,#fff);
        border: 1px solid var(--bs-border-color,#e2e8f0);
        border-radius: 14px;
        overflow: hidden;
    }
    .perm-card-header {
        padding: .85rem 1.25rem;
        border-bottom: 1px solid var(--bs-border-color,#e2e8f0);
        display: flex; align-items: center; justify-content: space-between;
        background: var(--bs-tertiary-bg,#f8fafc);
    }

    /* ── Filter bar ─────────────────────────────────────── */
    .filter-bar {
        display: flex; gap: .5rem; flex-wrap: wrap;
        padding: .75rem 1.25rem;
        border-bottom: 1px solid var(--bs-border-color,#e2e8f0);
        background: var(--bs-body-bg,#fff);
    }
    .filter-bar input { max-width: 220px; }

    /* ── Garden rows ────────────────────────────────────── */
    .garden-row {
        display: grid;
        grid-template-columns: 110px 1fr 220px 160px 90px;
        align-items: center;
        gap: 1rem;
        padding: .75rem 1.25rem;
        border-bottom: 1px solid var(--bs-border-color,#e2e8f0);
        font-size: .86rem;
        transition: background .12s;
    }
    .garden-row:last-child { border-bottom: none; }
    .garden-row:hover { background: var(--bs-tertiary-bg,#f8fafc); }
    .garden-row.gh {
        font-size: .74rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--bs-secondary-color,#64748b);
        background: var(--bs-tertiary-bg,#f8fafc);
        border-bottom: 2px solid var(--bs-border-color,#dee2e6);
        pointer-events: none;
        padding-top: .6rem; padding-bottom: .6rem;
    }
    @media (max-width: 991px) {
        .garden-row { grid-template-columns: 110px 1fr 1fr 120px; }
        .garden-row .col-stations { display: none; }
    }
    @media (max-width: 767px) {
        .garden-row { grid-template-columns: 1fr 1fr; gap: .5rem; padding: .75rem 1rem; }
        .garden-row.gh { display: none; }
    }

    .code-pill {
        font-family: monospace;
        font-size: .78rem; font-weight: 600;
        background: var(--bs-tertiary-bg,#f1f5f9);
        border: 1px solid var(--bs-border-color,#e2e8f0);
        border-radius: 6px; padding: 2px 8px;
        display: inline-block;
    }
    .user-select-ctrl {
        width: 100%;
        border: 1px solid var(--bs-border-color,#e2e8f0);
        border-radius: 8px;
        padding: 5px 9px;
        font-size: .83rem;
        background: var(--bs-body-bg,#fff);
        color: var(--bs-body-color,#1e293b);
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s;
    }
    .user-select-ctrl:focus {
        outline: none; border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }
    .stations-chips { display: flex; flex-wrap: wrap; gap: 4px; }
    .st-chip {
        font-family: monospace; font-size: .72rem; font-weight: 600;
        background: #eff6ff; color: #1d4ed8;
        border: 1px solid #bfdbfe; border-radius: 5px;
        padding: 1px 6px; display: inline-block;
    }
    .st-chip-none { font-size: .78rem; color: var(--bs-secondary-color,#94a3b8); }

    /* ── Save toast ─────────────────────────────────────── */
    .save-toast {
        position: fixed; bottom: 1.5rem; right: 1.5rem;
        background: #0f172a; color: #fff;
        border-radius: 10px; padding: .6rem 1.1rem;
        font-size: .84rem; display: flex; align-items: center; gap: .5rem;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
        opacity: 0; transform: translateY(10px);
        transition: opacity .2s, transform .2s;
        pointer-events: none; z-index: 9999;
    }
    .save-toast.show { opacity: 1; transform: translateY(0); }
</style>
@endpush

@section('content')
    <x-page-header title="Phân Quyền Trạm Quan Trắc">
        <x-slot:actions>
            <a href="{{ route('iot.stations') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Danh sách trạm
            </a>
        </x-slot:actions>
    </x-page-header>

    @php
        $totalGardens    = $gardens->count();
        $assignedGardens = $gardens->whereNotNull('user_id')->count();
        $freeGardens     = $gardens->whereNull('user_id')->count();
        $totalUsers      = $users->count();
    @endphp

    {{-- Stats --}}
    <div class="stat-strip">
        <div class="stat-card sc-blue">
            <i class="bi bi-tree si"></i>
            <div><div class="sn">{{ $totalGardens }}</div><div class="sl">Tổng vườn</div></div>
        </div>
        <div class="stat-card sc-green">
            <i class="bi bi-person-check si"></i>
            <div><div class="sn">{{ $assignedGardens }}</div><div class="sl">Đã có chủ</div></div>
        </div>
        <div class="stat-card sc-amber">
            <i class="bi bi-person-dash si"></i>
            <div><div class="sn">{{ $freeGardens }}</div><div class="sl">Chưa có chủ</div></div>
        </div>
        <div class="stat-card sc-purple">
            <i class="bi bi-people si"></i>
            <div><div class="sn">{{ $totalUsers }}</div><div class="sl">Người dùng</div></div>
        </div>
    </div>

    {{-- Main table --}}
    <div class="perm-card">
        <div class="perm-card-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-gear text-primary fs-5"></i>
                <span class="fw-semibold">Gán người dùng → Vườn</span>
                <span class="badge bg-secondary-subtle text-secondary ms-1" id="rowCount">{{ $totalGardens }} vườn</span>
            </div>
            <span class="text-muted" style="font-size:.78rem;">
                <i class="bi bi-info-circle me-1"></i>Chọn chủ vườn → tự động lưu
            </span>
        </div>

        {{-- Filter bar --}}
        <div class="filter-bar">
            <input type="text" id="gardenSearch" class="form-control form-control-sm"
                   placeholder="Tìm theo tên / mã vườn...">
            <select id="filterUser" class="form-select form-select-sm" style="max-width:200px;">
                <option value="">Tất cả người dùng</option>
                <option value="__none__">— Chưa có chủ</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Header row --}}
        <div class="garden-row gh">
            <div>Mã vườn</div>
            <div>Tên vườn</div>
            <div>Chủ vườn (người dùng)</div>
            <div class="col-stations">Trạm trong vườn</div>
            <div>Loại cây</div>
        </div>

        {{-- Data rows --}}
        @forelse($gardens as $garden)
            <div class="garden-row"
                 data-name="{{ strtolower($garden->name . ' ' . $garden->code) }}"
                 data-uid="{{ $garden->user_id ?? '' }}">

                {{-- Code --}}
                <div><span class="code-pill">{{ $garden->code }}</span></div>

                {{-- Name --}}
                <div>
                    <div class="fw-medium text-truncate" title="{{ $garden->name }}">{{ $garden->name }}</div>
                    @if($garden->location)
                        <div class="text-muted" style="font-size:.74rem;">
                            <i class="bi bi-geo-alt"></i> {{ $garden->location }}
                        </div>
                    @endif
                </div>

                {{-- User select --}}
                <div>
                    <form class="perm-form" action="{{ route('iot.stations.permissions.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="garden_id" value="{{ $garden->id }}">
                        <select name="user_id" class="user-select-ctrl"
                                data-garden-code="{{ $garden->code }}"
                                data-current="{{ $garden->user_id }}">
                            <option value="">— Chưa có chủ —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}"
                                    {{ $garden->user_id == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                    @if($u->isLocked()) (đã khóa) @endif
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                {{-- Stations --}}
                <div class="col-stations">
                    @if($garden->stations->isEmpty())
                        <span class="st-chip-none">Chưa có trạm</span>
                    @else
                        <div class="stations-chips">
                            @foreach($garden->stations as $st)
                                <span class="st-chip" title="{{ $st->name }}">{{ $st->code }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Crop type --}}
                <div class="text-muted" style="font-size:.8rem;">
                    {{ $garden->crop_type ?? '—' }}
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-tree fs-1 opacity-25 d-block mb-3"></i>
                <p class="mb-0">Chưa có vườn nào trong hệ thống.</p>
            </div>
        @endforelse

        {{-- No results --}}
        <div id="noMatch" class="text-center py-5 text-muted" style="display:none;">
            <i class="bi bi-funnel fs-1 opacity-25 d-block mb-3"></i>
            <p class="mb-0">Không có vườn nào khớp.</p>
        </div>
    </div>

    {{-- Save toast --}}
    <div class="save-toast" id="saveToast">
        <span class="spinner-border spinner-border-sm" id="toastSpinner"></span>
        <i class="bi bi-check-circle-fill text-success" id="toastCheck" style="display:none;"></i>
        <span id="toastMsg">Đang lưu...</span>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    /* ── Filter ──────────────────────────────────────────── */
    function applyFilter() {
        const q   = document.getElementById('gardenSearch').value.toLowerCase();
        const uid = document.getElementById('filterUser').value;
        const rows = document.querySelectorAll('.garden-row:not(.gh)');
        let n = 0;
        rows.forEach(row => {
            const nameOk = !q || row.dataset.name.includes(q);
            let userOk = true;
            if (uid === '__none__') userOk = !row.dataset.uid;
            else if (uid)          userOk = row.dataset.uid == uid;
            const show = nameOk && userOk;
            row.style.display = show ? '' : 'none';
            if (show) n++;
        });
        document.getElementById('rowCount').textContent = n + ' vườn';
        document.getElementById('noMatch').style.display = (n === 0) ? '' : 'none';
    }
    document.getElementById('gardenSearch').addEventListener('input', applyFilter);
    document.getElementById('filterUser').addEventListener('change', applyFilter);

    /* ── Toast ───────────────────────────────────────────── */
    const toast   = document.getElementById('saveToast');
    const spinner = document.getElementById('toastSpinner');
    const check   = document.getElementById('toastCheck');
    const msg     = document.getElementById('toastMsg');
    let hideTimer;

    function showSaving(text) {
        clearTimeout(hideTimer);
        spinner.style.display = '';
        check.style.display   = 'none';
        msg.textContent = text;
        toast.classList.add('show');
    }
    function showDone(text) {
        spinner.style.display = 'none';
        check.style.display   = '';
        msg.textContent = text;
        hideTimer = setTimeout(() => toast.classList.remove('show'), 2200);
    }
    function showError(text) {
        spinner.style.display = 'none';
        check.style.display   = 'none';
        msg.textContent = '⚠ ' + text;
        hideTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    }

    /* ── Auto-save ───────────────────────────────────────── */
    document.querySelectorAll('.user-select-ctrl').forEach(sel => {
        sel.addEventListener('change', function () {
            const form       = this.closest('form');
            const gardenCode = this.dataset.gardenCode;
            const newUid     = this.value;
            const prevUid    = this.dataset.current;
            const row        = this.closest('.garden-row');

            showSaving('Đang lưu...');

            fetch(form.action, {
                method : 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept'      : 'application/json',
                },
                body: new FormData(form),
            })
            .then(res => { if (!res.ok) throw new Error(); return res.json(); })
            .then(() => {
                this.dataset.current = newUid;
                row.dataset.uid      = newUid;
                applyFilter(); // re-count
                const label = this.options[this.selectedIndex].textContent.trim();
                showDone(newUid ? `Vườn ${gardenCode} → ${label}` : `Đã bỏ chủ vườn ${gardenCode}`);
            })
            .catch(() => {
                this.value           = prevUid;    // revert
                showError('Lưu thất bại, thử lại!');
            });
        });
    });
})();
</script>
@endpush
