<aside class="app-sidebar">
    <div class="sidebar-brand">
        <a href="{{ url('/dashboard') }}" class="brand-logo">
            <div class="brand-icon">
                <i class="bi bi-flower1"></i>
            </div>
            <span class="brand-text">IoT Bắc Ninh</span>
        </a>
        <button type="button" class="sidebar-toggle-btn" title="Thu gọn / Mở rộng menu">
            <i class="bi bi-chevron-left toggle-icon"></i>
        </button>
    </div>

    <div class="sidebar-nav">
        <!-- Trang tổng quan & Dán nhãn Tổng Nhiệt hữu hiệu (Direct Links) -->
        <ul class="sidebar-menu mb-2">
            <li class="sidebar-item">
                <a href="{{ url('/dashboard') }}" class="sidebar-link {{ Request::is('dashboard*') || Request::is('/') ? 'active' : '' }}" data-tooltip="Trang tổng quan">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Trang tổng quan</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('degree-days.surveys.index') }}" class="sidebar-link {{ Request::is('degree-days*') ? 'active' : '' }}" data-tooltip="Khảo sát hàng ngày">
                    <i class="bi bi-calendar-check"></i>
                    <span>Khảo sát hàng ngày</span>
                </a>
            </li>
        </ul>



        <!-- 1. Thư mục: Phân hệ tài khoản -->
        @php
            $isAccountActive = Request::is('account*');
        @endphp
        <div class="sidebar-folder {{ $isAccountActive ? 'open active-group' : '' }}" data-folder="account">
            <button type="button" class="sidebar-folder-toggle" data-tooltip="Phân hệ tài khoản">
                <div class="folder-left">
                    <i class="bi bi-person-gear"></i>
                    <span>Phân hệ tài khoản</span>
                </div>
                <i class="bi bi-chevron-down folder-chevron"></i>
            </button>
            <ul class="sidebar-submenu">
                @if(Auth::check() && Auth::user()->isAdmin())
                    <li class="sidebar-item">
                        <a href="{{ url('/account/users') }}" class="sidebar-link {{ Request::is('account/users*') ? 'active' : '' }}" data-tooltip="Quản lý tài khoản">
                            <i class="bi bi-people-fill"></i>
                            <span>Quản lý tài khoản</span>
                        </a>
                    </li>
                @endif
                <li class="sidebar-item">
                    <a href="{{ url('/account/profile') }}" class="sidebar-link {{ Request::is('account/profile*') ? 'active' : '' }}" data-tooltip="Thông tin cá nhân">
                        <i class="bi bi-person-badge-fill"></i>
                        <span>Thông tin cá nhân</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- 2. Thư mục: Vườn & Canh tác -->
        @php
            $isFarmActive = Request::is('gardens*') || Request::is('map*') || Request::is('care*');
        @endphp
        <div class="sidebar-folder {{ $isFarmActive ? 'open active-group' : '' }}" data-folder="farm">
            <button type="button" class="sidebar-folder-toggle" data-tooltip="Vườn & Canh tác">
                <div class="folder-left">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Vườn & Canh tác</span>
                </div>
                <i class="bi bi-chevron-down folder-chevron"></i>
            </button>
            <ul class="sidebar-submenu">
                <li class="sidebar-item">
                    <a href="{{ url('/gardens/map') }}" class="sidebar-link {{ Request::is('gardens*') || Request::is('map*') ? 'active' : '' }}" data-tooltip="Bản đồ vị trí vườn">
                        <i class="bi bi-map-fill"></i>
                        <span>Bản đồ vị trí vườn</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ url('/care/logs') }}" class="sidebar-link {{ Request::is('care*') ? 'active' : '' }}" data-tooltip="Lịch sử chăm sóc">
                        <i class="bi bi-calendar-check-fill"></i>
                        <span>Lịch sử chăm sóc</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- 3. Thư mục: IoT & Quan trắc -->
        @php
            $isIotActive = Request::is('iot*') || Request::is('stations*');
        @endphp
        <div class="sidebar-folder {{ $isIotActive ? 'open active-group' : '' }}" data-folder="iot">
            <button type="button" class="sidebar-folder-toggle" data-tooltip="IoT & Quan trắc">
                <div class="folder-left">
                    <i class="bi bi-broadcast-pin"></i>
                    <span>IoT & Quan trắc</span>
                </div>
                <i class="bi bi-chevron-down folder-chevron"></i>
            </button>
            <ul class="sidebar-submenu">
                <li class="sidebar-item">
                    <a href="{{ url('/iot/stations') }}" class="sidebar-link {{ Request::is('iot/stations*') || Request::is('stations*') ? 'active' : '' }}" data-tooltip="Giám sát trạm IoT">
                        <i class="bi bi-hdd-network-fill"></i>
                        <span>Giám sát trạm IoT</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ url('/iot/weather-history') }}" class="sidebar-link {{ Request::is('iot/weather-history*') ? 'active' : '' }}" data-tooltip="Lịch sử thời tiết">
                        <i class="bi bi-cloud-sun-fill"></i>
                        <span>Lịch sử thời tiết</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ url('/iot/media') }}" class="sidebar-link {{ Request::is('iot/media*') ? 'active' : '' }}" data-tooltip="Giám sát ảnh / video">
                        <i class="bi bi-images"></i>
                        <span>Giám sát ảnh / video</span>
                    </a>
                </li>
                @if(Auth::check() && Auth::user()->isAdmin())
                    <li class="sidebar-item">
                        <a href="{{ url('/iot/schedules') }}" class="sidebar-link {{ Request::is('iot/schedules*') ? 'active' : '' }}" data-tooltip="Khung giờ gửi ảnh">
                            <i class="bi bi-clock-history"></i>
                            <span>Khung giờ gửi ảnh</span>
                        </a>
                    </li>
                @endif
                <li class="sidebar-item">
                    <a href="{{ url('/iot/locations') }}" class="sidebar-link {{ Request::is('iot/locations*') ? 'active' : '' }}" data-tooltip="Tọa độ ảnh chụp">
                        <i class="bi bi-camera-reels-fill"></i>
                        <span>Tọa độ chụp tự động</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- 4. Thư mục: Trí tuệ nhân tạo AI -->
        @php
            $isAiActive = Request::is('ai*') || Request::is('chatbot*');
        @endphp
        <div class="sidebar-folder {{ $isAiActive ? 'open active-group' : '' }}" data-folder="ai">
            <button type="button" class="sidebar-folder-toggle" data-tooltip="Trí tuệ nhân tạo AI">
                <div class="folder-left">
                    <i class="bi bi-robot"></i>
                    <span>Trí tuệ nhân tạo AI</span>
                </div>
                <i class="bi bi-chevron-down folder-chevron"></i>
            </button>
            <ul class="sidebar-submenu">
                <li class="sidebar-item">
                    <a href="{{ url('/ai/diagnosis') }}" class="sidebar-link {{ Request::is('ai/diagnosis*') ? 'active' : '' }}" data-tooltip="Chẩn đoán sương mai">
                        <i class="bi bi-virus"></i>
                        <span>Chẩn đoán sương mai</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ url('/ai/pest') }}" class="sidebar-link {{ Request::is('ai/pest*') ? 'active' : '' }}" data-tooltip="Kiểm tra sâu hại">
                        <i class="bi bi-bug-fill"></i>
                        <span>Kiểm tra sâu đục cuống</span>
                    </a>
                </li>
                @if(Auth::check() && Auth::user()->isAdmin())
                    <li class="sidebar-item">
                        <a href="{{ url('/ai/auto-downy-mildew') }}" class="sidebar-link {{ Request::is('ai/auto-downy*') ? 'active' : '' }}" data-tooltip="Cảnh báo sương mai tự động">
                            <i class="bi bi-shield-check"></i>
                            <span>Cảnh báo sương mai tự động</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ url('/ai/auto-pest-prediction') }}" class="sidebar-link {{ Request::is('ai/auto-pest*') ? 'active' : '' }}" data-tooltip="Dự báo sâu hại tự động">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Dự báo sâu hại tự động</span>
                        </a>
                    </li>
                @endif
                @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isManager()))
                    <li class="sidebar-item">
                        <a href="{{ url('/labeler/dashboard') }}" class="sidebar-link {{ Request::is('labeler*') ? 'active' : '' }}" data-tooltip="Phân hệ Gán Nhãn AI">
                            <i class="bi bi-tags-fill text-indigo-400"></i>
                            <span class="fw-bold text-indigo-400">Phân hệ Gán Nhãn AI</span>
                        </a>
                    </li>
                @endif
                <li class="sidebar-item">
                    <a href="{{ url('/chatbot') }}" class="sidebar-link {{ Request::is('chatbot*') && !Request::is('chatbot/knowledge-base*') ? 'active' : '' }}" data-tooltip="Trợ lý AI nông nghiệp">
                        <i class="bi bi-chat-heart-fill"></i>
                        <span>Trợ lý AI nông nghiệp</span>
                    </a>
                </li>
                @if(Auth::check() && Auth::user()->isAdmin())
                    <li class="sidebar-item">
                        <a href="{{ url('/chatbot/knowledge-base') }}" class="sidebar-link {{ Request::is('chatbot/knowledge-base*') ? 'active' : '' }}" data-tooltip="Tri thức Chatbot AI">
                            <i class="bi bi-database-fill-gear"></i>
                            <span>Tri thức Chatbot AI</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <!-- 5. Thư mục: Nội dung & Tương tác -->
        @php
            $isContentActive = Request::is('notifications*') || Request::is('content*') || Request::is('support*');
        @endphp
        <div class="sidebar-folder {{ $isContentActive ? 'open active-group' : '' }}" data-folder="content">
            <button type="button" class="sidebar-folder-toggle" data-tooltip="Nội dung & Tương tác">
                <div class="folder-left">
                    <i class="bi bi-chat-square-text-fill"></i>
                    <span>Nội dung & Tương tác</span>
                </div>
                <i class="bi bi-chevron-down folder-chevron"></i>
            </button>
            <ul class="sidebar-submenu">
                <li class="sidebar-item">
                    <a href="{{ url('/notifications') }}" class="sidebar-link {{ Request::is('notifications*') ? 'active' : '' }}" data-tooltip="Thông báo & Cảnh báo">
                        <i class="bi bi-bell-fill"></i>
                        <span>Thông báo & Cảnh báo</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ url('/content/news') }}" class="sidebar-link {{ Request::is('content/news*') ? 'active' : '' }}" data-tooltip="Tin tức nông nghiệp">
                        <i class="bi bi-newspaper"></i>
                        <span>Tin tức nông nghiệp</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ url('/content/knowledge') }}" class="sidebar-link {{ Request::is('content/knowledge*') ? 'active' : '' }}" data-tooltip="Cẩm nang kiến thức">
                        <i class="bi bi-book-fill"></i>
                        <span>Cẩm nang kiến thức</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="{{ url('/support') }}" class="sidebar-link {{ Request::is('support*') ? 'active' : '' }}" data-tooltip="Hòm thư hỗ trợ">
                        <i class="bi bi-envelope-paper-fill"></i>
                        <span>Hòm thư liên hệ hỗ trợ</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- 6. Thư mục: Quản trị & Giám sát hệ thống (Admin & Manager Only) -->
        @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isManager()))
            @php
                $isSystemActive = Request::is('system*');
            @endphp
            <div class="sidebar-folder {{ $isSystemActive ? 'open active-group' : '' }}" data-folder="system">
                <button type="button" class="sidebar-folder-toggle" data-tooltip="Quản trị & Giám sát">
                    <div class="folder-left">
                        <i class="bi bi-shield-shaded"></i>
                        <span>Quản trị & Giám sát</span>
                    </div>
                    <i class="bi bi-chevron-down folder-chevron"></i>
                </button>
                <ul class="sidebar-submenu">
                    @if(Auth::user()->isAdmin())
                        <li class="sidebar-item">
                            <a href="{{ url('/system/settings') }}" class="sidebar-link {{ Request::is('system/settings*') ? 'active' : '' }}" data-tooltip="Cài đặt hệ thống">
                                <i class="bi bi-gear-fill"></i>
                                <span>Cài đặt hệ thống</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="{{ url('/system/monitoring-config') }}" class="sidebar-link {{ Request::is('system/monitoring-config*') ? 'active' : '' }}" data-tooltip="Cấu hình quan trắc">
                                <i class="bi bi-sliders"></i>
                                <span>Cấu hình quan trắc</span>
                            </a>
                        </li>
                    @endif
                    <li class="sidebar-item">
                        <a href="{{ url('/system/error-logs') }}" class="sidebar-link {{ Request::is('system/error-logs*') ? 'active' : '' }}" data-tooltip="Log lỗi hệ thống">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Log lỗi hệ thống</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ url('/system/access-logs') }}" class="sidebar-link {{ Request::is('system/access-logs*') ? 'active' : '' }}" data-tooltip="Lịch sử đăng nhập">
                            <i class="bi bi-clock-history"></i>
                            <span>Lịch sử đăng nhập</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif
    </div>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" class="w-100">
            @csrf
            <button type="submit" class="sidebar-link w-100 border-0 bg-transparent text-start" style="color: #f87171; cursor: pointer;" data-tooltip="Đăng xuất">
                <i class="bi bi-box-arrow-left"></i>
                <span>Đăng xuất</span>
            </button>
        </form>
    </div>
</aside>
