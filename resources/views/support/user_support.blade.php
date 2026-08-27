@extends('layouts.app')

@section('title', 'Liên Hệ Hỗ Trợ Kỹ Thuật Nông Nghiệp')

@section('content')
    <x-page-header title="Liên Hệ Hỗ Trợ Kỹ Thuật">
        <x-slot:breadcrumbs>
            <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
            <span>/</span>
            <span>Hỗ trợ</span>
            <span>/</span>
            <span class="text-primary fw-bold">Liên hệ hỗ trợ</span>
        </x-slot:breadcrumbs>

        @if (Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isManager()))
            <x-slot:actions>
                <a href="{{ route('support.manage') }}" class="btn btn-secondary">
                    <i class="bi bi-inbox-fill"></i> Hòm Thư Quản Lý (QTV)
                </a>
            </x-slot:actions>
        @endif
    </x-page-header>

    <div class="row g-4">
        <!-- KHỐI 1: FORM GỬI YÊU CẦU HỖ TRỢ -->
        <div class="col-lg-5 col-md-12">
            <div class="card h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 text-dark fw-bold" style="font-size: 1.05rem;">
                        <i class="bi bi-envelope-paper-fill text-primary me-2"></i> Gửi Yêu Cầu Hỗ Trợ Mới
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('support.submit') }}" method="POST" id="form-user-support">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Họ và tên người gửi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0" style="border-color: var(--border-color);"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="name" id="name" class="form-control border-start-0 ps-0"
                                    value="{{ old('name', $user->name ?? '') }}" placeholder="Nguyễn Văn An" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0" style="border-color: var(--border-color);"><i class="bi bi-telephone text-muted"></i></span>
                                    <input type="tel" name="phone" id="phone" class="form-control border-start-0 ps-0"
                                        value="{{ old('phone', $user->phone ?? '') }}" placeholder="0987654321" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Địa chỉ Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0" style="border-color: var(--border-color);"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" name="email" id="email" class="form-control border-start-0 ps-0"
                                        value="{{ old('email', $user->email ?? '') }}" placeholder="nongdan@bacninh.gov.vn">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label fw-semibold">Nội dung thắc mắc / Cần hỗ trợ <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" class="form-control" rows="5"
                                placeholder="Mô tả cụ thể vấn đề kỹ thuật cây trồng, sâu bệnh hại hoặc thắc mắc thiết bị IoT..." required>{{ old('content') }}</textarea>
                            <div class="form-text small text-muted mt-1"><i class="bi bi-shield-check"></i> Thông tin sẽ được chuyển trực tiếp tới Ban Quản lý Nông nghiệp tỉnh.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold" style="font-size: 15px;">
                            <i class="bi bi-send-fill me-1"></i> Gửi Yêu Cầu Hỗ Trợ
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- KHỐI 2: LỊCH SỬ YÊU CẦU & PHẢN HỒI CỦA TÔI -->
        <div class="col-lg-7 col-md-12">
            <div class="card h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-dark fw-bold" style="font-size: 1.05rem;">
                        <i class="bi bi-clock-history text-primary me-2"></i> Lịch Sử Yêu Cầu Của Tôi
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                        {{ $myRequests->total() }} Yêu cầu
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="custom-table w-100">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>Nội dung yêu cầu</th>
                                    <th>Trạng thái</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myRequests as $idx => $req)
                                    <tr>
                                        <td class="text-secondary fw-semibold">{{ $myRequests->firstItem() + $idx }}</td>
                                        <td>
                                            <div class="fw-bold text-dark" style="font-size: 14px; line-height: 1.4;">
                                                {{ $req->content }}
                                            </div>
                                            @if ($req->reply_content)
                                                <div class="mt-2 p-2.5 rounded border border-success-subtle bg-success-subtle text-dark" style="font-size: 13px;">
                                                    <div class="fw-bold text-success mb-1">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Phản hồi từ Cán bộ Quản lý:
                                                    </div>
                                                    <div style="line-height: 1.5;">{{ $req->reply_content }}</div>
                                                    <div class="text-muted small mt-1" style="font-size: 11px;">
                                                        <i class="bi bi-clock me-1"></i> {{ $req->replied_at ? $req->replied_at->format('d/m/Y H:i') : '' }}
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($req->status === 'pending')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                                    <i class="bi bi-hourglass-split"></i> Đang chờ phản hồi
                                                </span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i class="bi bi-check-all"></i> Đã phản hồi
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            {{ $req->created_at ? $req->created_at->format('d/m/Y H:i') : '' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state py-4">
                                                <div class="empty-state-icon"><i class="bi bi-chat-square-dots"></i></div>
                                                <h6 class="fw-bold mb-1">Bạn chưa gửi yêu cầu hỗ trợ nào</h6>
                                                <p class="text-muted small mb-0">Điền biểu mẫu bên trái để đặt câu hỏi cho cán bộ chuyên môn.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top" style="border-color: var(--border-color) !important;">
                        <span class="text-muted" style="font-size: 13.5px;">
                            Hiển thị {{ $myRequests->firstItem() ?? 0 }} - {{ $myRequests->lastItem() ?? 0 }} trên {{ $myRequests->total() }} yêu cầu
                        </span>
                        <div>
                            {{ $myRequests->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
