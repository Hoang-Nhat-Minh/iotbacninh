@extends('layouts.app')

@section('title', 'Lịch Sử Chăm Sóc - Hệ Thống IoT Bắc Ninh')
@section('page_title', 'Lịch Sử Chăm Sóc Vùng Trồng')

@section('content')


<div class="row">
    <!-- Left Column: Filter and Care Records List (8 cols) -->
    <div class="col-lg-8 mb-4">
        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ url('/care') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="zone-filter" class="form-label fw-bold">Chọn Vùng Trồng</label>
                        <select class="form-select" id="zone-filter" name="zone">
                            <option value="">Tất cả các vùng</option>
                            <option value="1" {{ request('zone') == '1' ? 'selected' : '' }}>VT-01: Vùng Lúa Cao Sản</option>
                            <option value="2" {{ request('zone') == '2' ? 'selected' : '' }}>VT-02: Vùng Dưa Chuột</option>
                            <option value="3" {{ request('zone') == '3' ? 'selected' : '' }}>VT-03: Vùng Ngô Sinh Khối</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="date-filter" class="form-label fw-bold">Ngày chăm sóc</label>
                        <input type="date" class="form-control" id="date-filter" name="date" value="{{ request('date') }}">
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-2"></i>Lọc Kết Quả</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History Feed -->
        <div class="card">
            <div class="card-header bg-white">
                <i class="bi bi-clock-history text-success me-2"></i>Nhật Ký Chăm Sóc Gần Đây
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    
                    @forelse ($records as $record)
                        <div class="list-group-item p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold mb-1 text-dark">{{ $record['title'] }}</h5>
                                <span class="badge bg-{{ $record['zone_class'] }}">{{ $record['zone'] }}</span>
                            </div>
                            <p class="text-secondary mb-3">{{ $record['content'] }}</p>
                            <div class="d-flex justify-content-between align-items-center text-muted fs-7">
                                <span><i class="bi bi-calendar-event me-1"></i>Ngày làm: <strong>{{ \Carbon\Carbon::parse($record['date'])->format('d/m/Y') }}</strong></span>
                                <span><i class="bi bi-person me-1"></i>Người thực hiện: <strong>{{ $record['farmer'] }}</strong></span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                            Không tìm thấy nhật ký chăm sóc nào phù hợp.
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Record New Activity Form (4 cols) -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-success">
            <div class="card-header bg-success text-white">
                <i class="bi bi-plus-circle-fill me-2"></i>Ghi Chép Nhật Ký Mới
            </div>
            <div class="card-body">
                <form action="{{ url('/care') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="care-title" class="form-label fw-bold">Tiêu Đề Công Việc <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="care-title" name="title" placeholder="Ví dụ: Bón phân Kali đợt 3" required>
                    </div>

                    <div class="mb-3">
                        <label for="care-zone" class="form-label fw-bold">Chọn Vùng Trồng <span class="text-danger">*</span></label>
                        <select class="form-select" id="care-zone" name="zone" required>
                            <option value="">-- Chọn vùng --</option>
                            <option value="1">VT-01: Vùng Lúa Cao Sản</option>
                            <option value="2">VT-02: Vùng Dưa Chuột</option>
                            <option value="3">VT-03: Vùng Ngô Sinh Khối</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="care-date" class="form-label fw-bold">Ngày Thực Hiện <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="care-date" name="date" value="2026-07-29" required>
                    </div>

                    <div class="mb-3">
                        <label for="care-content" class="form-label fw-bold">Nội Dung Chi Tiết</label>
                        <textarea class="form-control" id="care-content" name="content" rows="5" placeholder="Mô tả kỹ thuật chăm sóc, liều lượng phân bón, thuốc trừ sâu sinh học hoặc tình trạng cây trồng sau xử lý..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-3"><i class="bi bi-save me-2"></i>Lưu Nhật Ký</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
