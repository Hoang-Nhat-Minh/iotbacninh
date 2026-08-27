@extends('layouts.labeler')

@section('title', 'Gán Nhãn Văn Bản - ' . $document->title)

@push('styles')
    <style>
        .text-workspace {
            display: grid;
            grid-template-columns: 260px 1fr 340px;
            gap: 20px;
            height: calc(100vh - 140px);
            min-height: 650px;
        }

        .doc-sidebar,
        .entities-sidebar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .sidebar-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .doc-list {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .doc-item {
            padding: 10px 12px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .doc-item:hover,
        .doc-item.active {
            background: rgba(79, 70, 229, 0.05);
            border-color: #4f46e5;
        }

        .text-stage-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .text-toolbar {
            padding: 12px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .label-pill {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #ffffff;
        }

        .label-pill.active {
            box-shadow: 0 0 12px rgba(79, 70, 229, 0.3);
            transform: scale(1.05);
            border-color: #4f46e5;
        }

        .text-content-box {
            flex: 1;
            padding: 32px;
            font-size: 18px;
            line-height: 2.2;
            color: #1e293b;
            overflow-y: auto;
            white-space: pre-wrap;
            user-select: text;
        }

        .ner-mark {
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin: 0 2px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            color: #ffffff;
        }

        .ner-mark-tag {
            font-size: 10px;
            text-transform: uppercase;
            background: rgba(0, 0, 0, 0.3);
            padding: 1px 4px;
            border-radius: 4px;
        }

        .ner-mark-remove {
            cursor: pointer;
            opacity: 0.7;
            margin-left: 4px;
        }

        .ner-mark-remove:hover {
            opacity: 1;
        }
    </style>
@endpush

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="page-title"><i class="bi bi-highlighter text-primary me-2"></i> Gán Nhãn Văn Bản:
                {{ $document->title }}</h4>
            <span class="text-muted-labeler small">Task: {{ $task->name }} | Dự án: {{ $project->name }}</span>
        </div>

        <button type="button" class="btn btn-success-gradient fw-bold px-4" onclick="saveTextAnnotations()">
            <i class="bi bi-check-circle-fill me-1"></i> Lưu & Cập Nhật Gán Nhãn
        </button>
    </div>

    <div class="text-workspace">
        <!-- Cột Trái: Danh Sách Tài Liệu Trong Task -->
        <div class="doc-sidebar">
            <div class="sidebar-header">
                <div class="fw-semibold mb-1" style="font-size: 14px;"><i
                        class="bi bi-file-earmark-text me-1 text-primary"></i> Văn Bản Trong Task</div>
                <div class="small text-muted-labeler">Click để chuyển đổi tài liệu làm việc</div>
            </div>

            <div class="doc-list">
                @foreach ($otherDocuments as $d)
                    <div class="doc-item {{ $d->id == $document->id ? 'active' : '' }}"
                        onclick="location.href='{{ route('labeler.text.workspace', $d->id) }}'">
                        <div class="fw-semibold text-truncate small">{{ $d->title }}</div>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <span class="badge {{ $d->status === 'labeled' ? 'badge-soft-success' : 'badge-soft-warning' }}"
                                style="font-size: 10px;">
                                {{ $d->status === 'labeled' ? 'Labeled' : 'Unlabeled' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Không Gian Làm Việc Bôi Chọn Gán Nhãn (Text Stage) -->
        <div class="text-stage-card">
            <!-- Toolbar Palette Selectors -->
            <div class="text-toolbar">
                <span class="small text-muted-labeler me-2 fw-semibold"><i class="bi bi-palette me-1"></i> Chọn Nhãn Thực
                    Thể:</span>
                @foreach ($labels as $idx => $lbl)
                    <div class="label-pill {{ $idx === 0 ? 'active' : '' }}" data-label-id="{{ $lbl->id }}"
                        data-color="{{ $lbl->color }}" data-name="{{ $lbl->name }}" onclick="setActiveLabel(this)"
                        style="background-color: {{ $lbl->color }};">
                        <span>{{ $lbl->name }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Interactive Text Area -->
            <div class="text-content-box" id="text-display-stage">
                <!-- Rendered with NER Marks via JS -->
            </div>
        </div>

        <!-- Cột Phải: Danh Sách Thực Thể (Extracted Entities) -->
        <div class="entities-sidebar">
            <div class="sidebar-header">
                <div class="fw-semibold mb-1" style="font-size: 14px;"><i class="bi bi-tags-fill me-1 text-primary"></i>
                    Thực Thể Trích Xuất (<span id="entity-count">0</span>)</div>
                <div class="small text-muted-labeler">Danh sách cụm từ bôi bôi gán nhãn</div>
            </div>

            <div class="p-3 flex-grow-1 overflow-y-auto" style="max-height: calc(100vh - 260px);">
                <div id="entities-list">
                    <!-- Entity cards dynamically rendered -->
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    $formattedAnnotations = $annotations->map(function ($a) {
        return [
            'label_id' => $a->label_id,
            'label_name' => $a->label->name ?? 'N/A',
            'color' => $a->label->color ?? '#ef4444',
            'selected_text' => $a->selected_text ?: $a->content,
            'start_offset' => $a->start_offset ?? $a->start_position,
            'end_offset' => $a->end_offset ?? $a->end_position,
        ];
    });
@endphp

@push('scripts')
    <script>
        const rawTextContent = @json($document->content);
        const labelsList = @json($labels);
        let activeLabel = labelsList.length > 0 ? labelsList[0] : null;

        // Current annotations array: [{label_id, label_name, color, selected_text, start_offset, end_offset}]
        let annotationsList = @json($formattedAnnotations);

        document.addEventListener('DOMContentLoaded', function() {
            renderTextStage();
            renderEntitiesSidebar();

            // Text selection handler
            const textStage = document.getElementById('text-display-stage');
            textStage.addEventListener('mouseup', handleTextSelection);
        });

        function setActiveLabel(el) {
            document.querySelectorAll('.label-pill').forEach(p => p.classList.remove('active'));
            el.classList.add('active');

            const id = el.getAttribute('data-label-id');
            activeLabel = labelsList.find(l => l.id == id);
        }

        function handleTextSelection() {
            const selection = window.getSelection();
            const selectedText = selection.toString().trim();

            if (!selectedText || selectedText.length === 0 || !activeLabel) return;

            // Find start offset of selected text in raw content
            const textStage = document.getElementById('text-display-stage');
            const startOffset = rawTextContent.indexOf(selectedText);
            const endOffset = startOffset !== -1 ? startOffset + selectedText.length : 0;

            // Check if already added
            const exists = annotationsList.some(a => a.selected_text === selectedText && a.label_id == activeLabel.id);
            if (!exists) {
                annotationsList.push({
                    label_id: activeLabel.id,
                    label_name: activeLabel.name,
                    color: activeLabel.color,
                    selected_text: selectedText,
                    start_offset: startOffset,
                    end_offset: endOffset
                });

                renderTextStage();
                renderEntitiesSidebar();
            }

            selection.removeAllRanges();
        }

        function removeAnnotation(idx) {
            annotationsList.splice(idx, 1);
            renderTextStage();
            renderEntitiesSidebar();
        }

        function renderTextStage() {
            const stage = document.getElementById('text-display-stage');
            if (!stage) return;

            if (annotationsList.length === 0) {
                stage.textContent = rawTextContent;
                return;
            }

            // Sort annotations by start_offset
            let sorted = [...annotationsList].sort((a, b) => a.start_offset - b.start_offset);

            let html = '';
            let lastIdx = 0;

            sorted.forEach((ann, idx) => {
                const textIdx = rawTextContent.indexOf(ann.selected_text, lastIdx);
                if (textIdx !== -1) {
                    html += escapeHtml(rawTextContent.substring(lastIdx, textIdx));
                    html += `<span class="ner-mark" style="background-color: ${ann.color};">
                        <span>${escapeHtml(ann.selected_text)}</span>
                        <span class="ner-mark-tag">${escapeHtml(ann.label_name)}</span>
                        <span class="ner-mark-remove" onclick="removeAnnotation(${annotationsList.indexOf(ann)})">&times;</span>
                    </span>`;
                    lastIdx = textIdx + ann.selected_text.length;
                }
            });

            html += escapeHtml(rawTextContent.substring(lastIdx));
            stage.innerHTML = html;
        }

        function renderEntitiesSidebar() {
            const container = document.getElementById('entities-list');
            const countSpan = document.getElementById('entity-count');
            if (!container) return;

            countSpan.textContent = annotationsList.length;
            container.innerHTML = '';

            if (annotationsList.length === 0) {
                container.innerHTML = `
            <div class="text-center py-4 text-muted-labeler small">
                <i class="bi bi-highlighter fs-3 text-secondary d-block mb-1"></i>
                Bôi bôi đoạn văn bản bất kỳ để gán nhãn thực thể.
            </div>
        `;
                return;
            }

            annotationsList.forEach((ann, idx) => {
                const item = document.createElement('div');
                item.className = 'p-2.5 rounded-3 mb-2 d-flex align-items-center justify-content-between';
                item.style.background = '#f8fafc';
                item.style.border = `1px solid ${ann.color}44`;

                item.innerHTML = `
            <div>
                <span class="badge mb-1" style="background-color: ${ann.color}; color: #ffffff;">${escapeHtml(ann.label_name)}</span>
                <div class="fw-semibold small">"${escapeHtml(ann.selected_text)}"</div>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm border-0 p-1" onclick="removeAnnotation(${idx})" title="Xóa nhãn">
                <i class="bi bi-x-circle-fill fs-6"></i>
            </button>
        `;
                container.appendChild(item);
            });
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g, String.fromCharCode(38) + "amp;")
                .replace(/</g, String.fromCharCode(38) + "lt;")
                .replace(/>/g, String.fromCharCode(38) + "gt;")
                .replace(/"/g, String.fromCharCode(38) + "quot;")
                .replace(/'/g, String.fromCharCode(38) + "#039;");
        }

        function saveTextAnnotations() {
            fetch('{{ route('labeler.text.annotations.save') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        document_id: {{ $document->id }},
                        annotations: annotationsList
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không thể lưu gán nhãn.'));
                    }
                })
                .catch(err => {
                    alert('Lỗi kết nối máy chủ!');
                    console.error(err);
                });
        }
    </script>
@endpush
