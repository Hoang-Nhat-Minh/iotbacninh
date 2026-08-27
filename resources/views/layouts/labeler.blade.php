<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Phân Hệ AI Data Labeling Workspace')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

    <style>
        :root {
            --labeler-primary: #4f46e5;
            --labeler-primary-light: #6366f1;
            --labeler-primary-dark: #4338ca;
            --labeler-accent: #8b5cf6;
            --labeler-bg: #f8fafc;
            --labeler-sidebar-bg: #ffffff;
            --labeler-border: #e2e8f0;
            --labeler-text: #1e293b;
            --labeler-text-muted: #64748b;
            --labeler-card-bg: #ffffff;
            --labeler-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
            --labeler-shadow-lg: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            --font-base: 'Be Vietnam Pro', sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-base);
            background-color: var(--labeler-bg);
            color: var(--labeler-text);
            margin: 0;
            min-height: 100vh;
            font-size: 14px;
        }

        /* Layout */
        .labeler-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .labeler-sidebar {
            width: 260px;
            background-color: var(--labeler-sidebar-bg);
            border-right: 1px solid var(--labeler-border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .labeler-brand {
            height: 70px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--labeler-border);
            color: var(--labeler-text);
            font-weight: 700;
            font-size: 15px;
        }

        .labeler-brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--labeler-primary), var(--labeler-accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .labeler-brand-sub {
            font-size: 11px;
            color: var(--labeler-primary);
            font-weight: 500;
        }

        .labeler-nav {
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
        }

        .labeler-nav-section {
            padding: 8px 16px 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--labeler-text-muted);
        }

        .labeler-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            color: var(--labeler-text-muted);
            text-decoration: none;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .labeler-nav-link:hover {
            background-color: #f1f5f9;
            color: var(--labeler-primary);
        }

        .labeler-nav-link.active {
            background-color: rgba(79, 70, 229, 0.08);
            color: var(--labeler-primary);
            font-weight: 600;
        }

        .labeler-nav-link .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .labeler-nav-divider {
            margin: 12px 16px;
            border-top: 1px solid var(--labeler-border);
        }

        /* Main */
        .labeler-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .labeler-header {
            height: 70px;
            background-color: var(--labeler-sidebar-bg);
            border-bottom: 1px solid var(--labeler-border);
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .labeler-content {
            padding: 28px;
            flex: 1;
        }

        /* Cards */
        .dash-card {
            background: var(--labeler-card-bg);
            border: 1px solid var(--labeler-border);
            border-radius: 12px;
            padding: 24px;
            box-shadow: var(--labeler-shadow);
        }

        .dash-card-hover:hover {
            box-shadow: var(--labeler-shadow-lg);
            transform: translateY(-2px);
            transition: all 0.2s ease;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--labeler-text);
            margin-bottom: 4px;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--labeler-text-muted);
            margin-bottom: 0;
        }

        /* Badges */
        .badge-soft-primary {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--labeler-primary);
        }

        .badge-soft-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .badge-soft-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .badge-soft-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .badge-soft-info {
            background-color: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .badge-soft-secondary {
            background-color: rgba(100, 116, 139, 0.1);
            color: var(--labeler-text-muted);
        }

        /* Buttons */
        .btn-primary-gradient {
            background: linear-gradient(135deg, var(--labeler-primary), var(--labeler-accent));
            border: none;
            color: #ffffff;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-primary-gradient:hover {
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transform: translateY(-1px);
        }

        .btn-success-gradient {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            color: #ffffff;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-success-gradient:hover {
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            transform: translateY(-1px);
        }

        /* Tables */
        .table-labeler {
            --bs-table-bg: transparent;
            --bs-table-hover-bg: #f8fafc;
            color: var(--labeler-text);
        }

        .table-labeler thead th {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: var(--labeler-text-muted);
            border-bottom: 1px solid var(--labeler-border);
            padding: 12px 16px;
            white-space: nowrap;
        }

        .table-labeler tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .table-labeler tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Forms */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--labeler-text);
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-color: var(--labeler-border);
            border-radius: 8px;
            font-size: 14px;
            padding: 8px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--labeler-primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Modals */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: var(--labeler-shadow-lg);
        }

        .modal-header {
            border-bottom: 1px solid var(--labeler-border);
            padding: 20px 24px;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            border-top: 1px solid var(--labeler-border);
            padding: 16px 24px;
        }

        /* Progress */
        .progress {
            background-color: #e2e8f0;
            border-radius: 4px;
        }

        .progress-bar {
            background-color: var(--labeler-primary);
            border-radius: 4px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--labeler-text-muted);
        }

        .empty-state i {
            font-size: 40px;
            display: block;
            margin-bottom: 12px;
            color: #cbd5e1;
        }

        /* Utility */
        .text-muted-labeler {
            color: var(--labeler-text-muted) !important;
        }

        .fw-semibold {
            font-weight: 600;
        }

        .mono {
            font-family: 'Consolas', 'Monaco', monospace;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>

<body>

    <div class="labeler-container">
        <!-- Sidebar -->
        <aside class="labeler-sidebar">
            <div class="labeler-brand">
                <div class="labeler-brand-icon">
                    <i class="bi bi-tags-fill"></i>
                </div>
                <div>
                    <div>AI Data Labeler</div>
                    <div class="labeler-brand-sub">Subsystem Workspace</div>
                </div>
            </div>

            <nav class="labeler-nav">
                <div class="labeler-nav-section">Tổng Quan</div>
                <a href="{{ route('labeler.dashboard') }}"
                    class="labeler-nav-link {{ request()->is('labeler/dashboard') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span> Tổng quan AI Labeling
                </a>

                <div class="labeler-nav-section">Dự Án & Nhiệm Vụ</div>
                <a href="{{ route('labeler.projects') }}"
                    class="labeler-nav-link {{ request()->is('labeler/projects*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-folder-fill"></i></span> Quản Lý Dự Án AI
                </a>
                <a href="{{ route('labeler.tasks') }}"
                    class="labeler-nav-link {{ request()->is('labeler/tasks*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-card-checklist"></i></span> Quản lý Task Ảnh
                </a>
                <a href="{{ route('labeler.annotation') }}"
                    class="labeler-nav-link {{ request()->is('labeler/annotation*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-bounding-box-circles"></i></span> Gán Nhãn Ảnh
                </a>
                <a href="{{ route('labeler.review') }}"
                    class="labeler-nav-link {{ request()->is('labeler/review*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-shield-check"></i></span> Review & Kiểm Tra
                </a>
                <a href="{{ route('labeler.export') }}"
                    class="labeler-nav-link {{ request()->is('labeler/export') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-download"></i></span> Export Dataset Ảnh
                </a>

                <div class="labeler-nav-section">Văn Bản</div>
                <a href="{{ route('labeler.text') }}"
                    class="labeler-nav-link {{ request()->is('labeler/text*') && !request()->is('labeler/text/export*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-file-text"></i></span> Gán Nhãn Văn Bản
                </a>
                <a href="{{ route('labeler.text.export') }}"
                    class="labeler-nav-link {{ request()->is('labeler/text/export*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-file-earmark-arrow-down"></i></span> Export Dataset Text
                </a>

                <div class="labeler-nav-section">Tri Thức</div>
                <a href="{{ route('labeler.knowledge') }}"
                    class="labeler-nav-link {{ request()->is('labeler/knowledge*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-database-fill-gear"></i></span> RAG Knowledge Base
                </a>

                <div class="labeler-nav-divider"></div>
                <a href="{{ route('dashboard') }}" class="labeler-nav-link">
                    <span class="nav-icon"><i class="bi bi-house-door-fill text-success"></i></span> Về Hệ Thống IoT
                    Chính
                </a>
            </nav>
        </aside>

        <!-- Main Workspace -->
        <div class="labeler-main">
            <!-- Header -->
            <header class="labeler-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-soft-primary px-3 py-2">
                        <i class="bi bi-cpu-fill me-1"></i> Data Labeling Environment
                    </span>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                            style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--labeler-primary), var(--labeler-accent));">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <div class="fw-semibold small mb-0">{{ Auth::user()->name ?? 'Người dùng' }}</div>
                            <div class="text-muted-labeler" style="font-size: 11px;">
                                {{ Auth::user()->email ?? Auth::user()->username }}</div>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm border-0" title="Đăng xuất">
                            <i class="bi bi-box-arrow-right fs-5"></i>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Content Area -->
            <main class="labeler-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
