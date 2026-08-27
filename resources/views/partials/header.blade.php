<header class="app-header">
    <div class="header-left">
        <div class="header-search">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Tìm kiếm tính năng hoặc dữ liệu...">
        </div>
    </div>

    <div class="header-right">
        <a href="{{ url('/notifications') }}" class="header-action-btn" title="Thông báo hệ thống">
            <i class="bi bi-bell"></i>
            <span class="header-badge-dot"></span>
        </a>

        <div class="dropdown">
            <div class="user-profile-menu" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                @if(Auth::check() && Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="user-avatar">
                @else
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120&h=120" alt="Avatar" class="user-avatar">
                @endif
                <div class="user-info d-none d-sm-flex">
                    <span class="user-name">{{ Auth::user()->name ?? 'Người dùng' }}</span>
                    <span class="user-role">{{ Auth::user()->role->name ?? 'Người dùng' }}</span>
                </div>
                <i class="bi bi-chevron-down text-muted ms-1 fs-6"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-md border-0 p-2 mt-2" style="border-radius: var(--radius-md); min-width: 200px;">
                <li>
                    <a class="dropdown-item py-2 px-3 rounded d-flex align-items-center gap-2 text-secondary" href="{{ url('/account/profile') }}">
                        <i class="bi bi-person text-primary"></i> Thông tin cá nhân
                    </a>
                </li>
                @if(Auth::check() && Auth::user()->isAdmin())
                    <li>
                        <a class="dropdown-item py-2 px-3 rounded d-flex align-items-center gap-2 text-secondary" href="{{ url('/account/users') }}">
                            <i class="bi bi-people text-primary"></i> Quản lý tài khoản
                        </a>
                    </li>
                @endif
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 px-3 rounded d-flex align-items-center gap-2 text-danger border-0 bg-transparent w-100 text-start">
                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
