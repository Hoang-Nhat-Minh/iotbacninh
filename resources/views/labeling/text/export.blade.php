@extends('layouts.labeler')

@section('title', 'Xuất Tập Dữ Liệu Văn Bản Gán Nhãn')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="page-title"><i class="bi bi-file-earmark-arrow-down-fill text-primary me-2"></i> Xuất Tập Dữ
                Liệu Văn Bản Gán Nhãn</h4>
            <p class="page-subtitle">Đóng gói và tạo tập tin nén .zip chứa nhãn thực thể NER sang các chuẩn định dạng JSON,
                JSONL, CSV, BIO/CoNLL</p>
        </div>
    </div>

    <!-- Project Selection Cards -->
    <h6 class="fw-bold mb-3"><i class="bi bi-folder-fill text-warning me-2"></i> Chọn Dự Án Văn Bản Cần Export Dữ Liệu</h6>
    <div class="row g-4 mb-5">
        @forelse($projects as $p)
            <div class="col-xl-4 col-md-6">
                <div class="dash-card dash-card-hover h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge badge-soft-primary mono">#TEXT-PROJ-{{ $p->id }}</span>
                            <span class="badge badge-soft-success">Active</span>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $p->name }}</h5>
                        <p class="text-muted-labeler small mb-3" style="font-size: 13px;">
                            {{ $p->description ?: 'Không có mô tả dự án' }}</p>

                        <div class="row g-2 mb-3 text-center">
                            <div class="col-4">
                                <div class="p-2 rounded-3" style="background: #f8fafc;">
                                    <div class="small text-muted-labeler" style="font-size: 11px;">Số Task</div>
                                    <div class="fw-bold fs-6">{{ $p->tasks_count }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3" style="background: #f8fafc;">
                                    <div class="small text-muted-labeler" style="font-size: 11px;">Tài Liệu Labeled</div>
                                    <div class="fw-bold text-success fs-6">{{ $p->labeled_documents }} /
                                        {{ $p->total_documents }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3" style="background: #f8fafc;">
                                    <div class="small text-muted-labeler" style="font-size: 11px;">Thực Thể</div>
                                    <div class="fw-bold text-primary fs-6">{{ $p->labels_count }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary-gradient w-100 fw-bold" data-bs-toggle="modal"
                        data-bs-target="#exportTextModal{{ $p->id }}">
                        <i class="bi bi-download me-1"></i> Xuất Dữ Liệu Text
                    </button>
                </div>
            </div>

            <!-- Export Modal for Text Project -->
            <div class="modal fade text-start" id="exportTextModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('labeler.text.export.generate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $p->id }}">

                            <div class="modal-header">
                                <h5 class="modal-title fw-bold"><i class="bi bi-sliders text-primary me-2"></i> Cấu Hình
                                    Xuất Dữ Liệu #PROJ-{{ $p->id }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Chọn Định Dạng NLP / Chatbot (Format) <span
                                            class="text-danger">*</span></label>
                                    <select name="format" class="form-select form-select-lg" required
                                        style="font-size: 14px;">
                                        <option value="json">JSON Format (SpaCy / HuggingFace Dataset)</option>
                                        <option value="jsonl">JSONL Format (OpenAI Fine-Tuning / Intent)</option>
                                        <option value="csv">CSV Format (Excel / Data Frame)</option>
                                        <option value="conll">BIO / CoNLL Format (NER Word-level Tagging)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tên Tập Tin Nén Zip Xuất Ra</label>
                                    <input type="text" name="archive_name" class="form-control"
                                        value="text_dataset_{{ strtolower(preg_replace('/[^A-Za-z0-9]/', '_', $p->name)) }}_{{ date('Ymd') }}">
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-success-gradient fw-bold px-4">
                                    <i class="bi bi-file-earmark-zip-fill me-1"></i> Xác Nhận Xuất & Tải Về (.zip)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dash-card text-center py-5 text-muted-labeler">
                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                    Chưa có dự án văn bản nào trong hệ thống.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Export History Table -->
    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i> Lịch Sử Các Lần Export Dataset Văn Bản
    </h6>
    <div class="dash-card">
        <div class="table-responsive">
            <table class="table table-labeler table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Tên Tập Tin (.zip)</th>
                        <th>Dự Án NLP</th>
                        <th>Định Dạng</th>
                        <th>Dung Lượng</th>
                        <th>Người Xuất</th>
                        <th>Thời Gian</th>
                        <th class="text-end" style="width: 140px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exports as $exp)
                        <tr>
                            <td class="mono text-primary fw-bold">#EXP-{{ $exp->id }}</td>
                            <td>
                                <div class="fw-semibold mb-0" style="font-size: 14px;"><i
                                        class="bi bi-file-earmark-zip text-warning me-1"></i> {{ $exp->file_name }}</div>
                            </td>
                            <td>
                                <span class="badge badge-soft-secondary">{{ $exp->textProject->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-soft-primary uppercase mono">{{ strtoupper($exp->format) }}</span>
                            </td>
                            <td class="mono text-muted-labeler small">{{ number_format($exp->file_size / 1024, 1) }} KB
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-person text-muted-labeler"></i>
                                    <span class="small">{{ $exp->user->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="small text-muted-labeler">
                                {{ $exp->created_at ? $exp->created_at->format('H:i d/m/Y') : '' }}</td>
                            <td class="text-end">
                                <a href="{{ route('labeler.export.download', $exp->id) }}"
                                    class="btn btn-outline-success btn-sm fw-bold">
                                    <i class="bi bi-download me-1"></i> Tải Về
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted-labeler">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Chưa có lịch sử xuất tập dữ liệu văn bản nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
