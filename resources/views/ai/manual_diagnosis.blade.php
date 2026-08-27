@extends('layouts.app')

@section('title', 'Chẩn Đoán Bệnh Sương Mai Qua Hình Ảnh')

@section('content')
<x-page-header title="Chẩn Đoán Bệnh Sương Mai Bằng AI">
    <x-slot:breadcrumbs>
        <a href="{{ url('/dashboard') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span class="text-primary fw-bold">Chẩn đoán ảnh</span>
    </x-slot:breadcrumbs>
</x-page-header>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-cloud-arrow-up text-primary"></i> Tải Ảnh Lá Cây Cần Phân Tích</h5>
            </div>
            <div class="card-body">
                <form id="form-ai-diagnosis" action="{{ url('/ai/diagnosis/analyze') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-4 border-2 border-dashed rounded text-center mb-3" style="border: 2px dashed var(--border-color); background: var(--bg-body); cursor: pointer;" id="drop-zone">
                        <i class="bi bi-image text-primary" style="font-size: 48px;"></i>
                        <h6 class="fw-bold mt-2 mb-1">Kéo thả ảnh chụp lá cây vào đây</h6>
                        <p class="text-muted small mb-3">Hỗ trợ JPG, PNG, WEBP (Tối đa 10MB)</p>
                        <label for="ai-image-upload" class="btn btn-secondary btn-sm px-3">
                            <i class="bi bi-folder2-open"></i> Chọn Ảnh Từ Thiết Bị
                        </label>
                        <input type="file" name="image" id="ai-image-upload" class="d-none" accept="image/*" required>
                    </div>

                    <div id="image-preview-box" class="d-none text-center mb-3">
                        <img id="selected-image-preview" src="" alt="preview" class="rounded border img-fluid" style="max-height: 240px;">
                    </div>

                    <button type="button" class="btn btn-primary w-100 py-2" id="btn-start-ai" disabled>
                        <i class="bi bi-cpu-fill"></i> Gửi Đến Core AI Phân Tích
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title"><i class="bi bi-file-earmark-medical text-primary"></i> Kết Quả Chẩn Đoán AI</h5>
                <span class="badge bg-light text-dark border" id="ai-status-badge">Chờ nạp ảnh</span>
            </div>
            <div class="card-body" id="ai-result-panel">
                <div class="p-3 mb-3 rounded" style="background: #fef2f2; border: 1px solid #fecaca;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Phát hiện: Bệnh Sương Mai (Downy Mildew)</h5>
                        <span class="badge bg-danger fs-6">Độ chính xác: 96.8%</span>
                    </div>
                    <p class="text-secondary small mb-0">
                        Vết bệnh màu vàng nhạt không định hình ở mặt trên lá, mặt dưới có lớp nấm trắng xám tơ mịn.
                    </p>
                </div>

                <h6 class="fw-bold text-dark mb-2">Phác Đồ Xử Lý & Khuyến Nghị Phòng Trị:</h6>
                <ul class="text-secondary small mb-3 ps-3">
                    <li class="mb-1"><strong>Ngừng tưới phun sương</strong> trên bề mặt tán lá vào chiều tối để giảm ẩm độ lá.</li>
                    <li class="mb-1">Tỉa bỏ các lá già, lá bị nhiễm bệnh nặng dưới gốc và tiêu hủy cách ly.</li>
                    <li class="mb-1">Phun thuốc sinh học hoạt chất <em>Mancozeb + Metalaxyl</em> hoặc <em>Nano Bạc</em> theo đúng nồng độ hướng dẫn.</li>
                </ul>

                <div class="p-3 border rounded bg-light">
                    <h6 class="fw-bold small mb-2 text-dark"><i class="bi bi-star-fill text-warning"></i> Đánh Giá Kết Quả Chẩn Đoán</h6>
                    <div class="d-flex gap-2 mb-2">
                        <button class="btn btn-sm btn-secondary" onclick="rateAi(5)"><i class="bi bi-hand-thumbs-up-fill text-success"></i> Rất chính xác</button>
                        <button class="btn btn-sm btn-secondary" onclick="rateAi(1)"><i class="bi bi-hand-thumbs-down-fill text-danger"></i> Không chính xác</button>
                    </div>
                    <textarea class="form-control form-control-sm" rows="2" placeholder="Gửi phản hồi đóng góp ý kiến để hoàn thiện Core AI..."></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title"><i class="bi bi-clock-history text-primary"></i> Lịch Sử Chẩn Đoán Gần Đây</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="custom-table mb-0">
                <thead>
                    <tr>
                        <th>Mã ca</th>
                        <th>Hình ảnh tải lên</th>
                        <th>Bệnh được chẩn đoán</th>
                        <th>Độ tin cậy</th>
                        <th>Đánh giá người dùng</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>#AI-20260814-01</code></td>
                        <td>
                            <img src="https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=60&h=60" alt="leaf" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                        </td>
                        <td><span class="badge bg-danger"><i class="bi bi-virus"></i> Bệnh Sương mai</span></td>
                        <td><strong class="text-danger">96.8%</strong></td>
                        <td><span class="text-warning"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></span></td>
                        <td class="text-muted small">14/08/2026 08:30</td>
                    </tr>
                    <tr>
                        <td><code>#AI-20260813-05</code></td>
                        <td>
                            <img src="https://images.unsplash.com/photo-1592417817098-8f3d6eb22d57?auto=format&fit=crop&q=80&w=60&h=60" alt="leaf" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                        </td>
                        <td><span class="badge bg-success"><i class="bi bi-check-circle"></i> Khỏe mạnh</span></td>
                        <td><strong class="text-success">98.2%</strong></td>
                        <td><span class="text-warning"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></span></td>
                        <td class="text-muted small">13/08/2026 15:10</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('ai-image-upload').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(evt) {
            document.getElementById('selected-image-preview').src = evt.target.result;
            document.getElementById('image-preview-box').classList.remove('d-none');
            document.getElementById('btn-start-ai').disabled = false;
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});

document.getElementById('btn-start-ai').addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Core AI đang xử lý (Tiền xử lý & Phân tích)...';
    
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cpu-fill"></i> Phân Tích Lại';
        showToast('Core AI đã hoàn tất chẩn đoán kết quả!', 'success');
    }, 1500);
});

function rateAi(stars) {
    showToast('Cảm ơn bạn đã gửi phản hồi đánh giá cho Core AI!', 'success');
}
</script>
@endpush
