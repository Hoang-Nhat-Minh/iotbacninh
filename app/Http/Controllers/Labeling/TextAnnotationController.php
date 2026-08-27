<?php

namespace App\Http\Controllers\Labeling;

use App\Http\Controllers\Controller;
use App\Models\Labeling\LabelTextAnnotation;
use App\Models\Labeling\LabelTextDocument;
use App\Models\Labeling\LabelTextLabel;
use App\Models\Labeling\LabelTextProject;
use App\Models\Labeling\LabelTextTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TextAnnotationController extends Controller
{
    public function index(Request $request)
    {
        $query = LabelTextDocument::with(['task.project', 'annotations']);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Task Filter
        if ($request->filled('task_id')) {
            $query->where('task_id', $request->input('task_id'));
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $documents = $query->latest()->get();

        $documents->transform(function ($doc) {
            $doc->annotations_count = $doc->annotations->count();
            return $doc;
        });

        $projects = LabelTextProject::all();
        $tasks = LabelTextTask::all();
        $assignees = User::whereHas('role', function ($q) {
            $q->whereIn('slug', ['admin', 'manager']);
        })->get();

        return view('labeling.text.index', compact('documents', 'projects', 'tasks', 'assignees'));
    }

    public function workspace($documentId)
    {
        $document = LabelTextDocument::with(['task.project', 'annotations.label'])->findOrFail($documentId);
        $task = $document->task;
        $project = $task->project;

        $labels = LabelTextLabel::where('project_id', $project->id)->get();
        $annotations = $document->annotations;

        $otherDocuments = LabelTextDocument::where('task_id', $task->id)->get();

        return view('labeling.text.workspace', compact(
            'document',
            'task',
            'project',
            'labels',
            'annotations',
            'otherDocuments'
        ));
    }

    public function storeTask(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|integer',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|integer',
        ]);

        $task = LabelTextTask::create([
            'project_id' => $request->input('project_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => 'in_progress',
            'assignee_id' => $request->input('assignee_id'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', "Đã tạo Task văn bản '{$task->name}' thành công!");
    }

    public function storeDocument(Request $request)
    {
        $request->validate([
            'task_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $doc = LabelTextDocument::create([
            'task_id' => $request->input('task_id'),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'status' => 'unlabeled',
        ]);

        return redirect()->back()->with('success', "Đã thêm tài liệu văn bản '{$doc->title}' thành công!");
    }

    public function saveAnnotations(Request $request)
    {
        $request->validate([
            'document_id' => 'required|integer',
            'annotations' => 'present|array',
        ]);

        $documentId = $request->input('document_id');
        $document = LabelTextDocument::findOrFail($documentId);

        // Clear existing annotations for this document
        LabelTextAnnotation::where('document_id', $documentId)->delete();

        $inputAnnotations = $request->input('annotations');
        foreach ($inputAnnotations as $ann) {
            if (isset($ann['label_id']) && isset($ann['selected_text'])) {
                LabelTextAnnotation::create([
                    'document_id' => $documentId,
                    'label_id' => $ann['label_id'],
                    'start_offset' => $ann['start_offset'] ?? 0,
                    'end_offset' => $ann['end_offset'] ?? 0,
                    'start_position' => $ann['start_offset'] ?? 0,
                    'end_position' => $ann['end_offset'] ?? 0,
                    'selected_text' => $ann['selected_text'],
                    'content' => $ann['selected_text'],
                    'created_by' => Auth::id(),
                ]);
            }
        }

        // Update document status
        $document->status = count($inputAnnotations) > 0 ? 'labeled' : 'unlabeled';
        $document->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu gán nhãn thực thể văn bản thành công!',
            'status' => $document->status,
        ]);
    }

    public function deleteDocuments(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'integer',
        ]);

        $docIds = $request->input('document_ids');
        $count = count($docIds);

        foreach ($docIds as $id) {
            LabelTextAnnotation::where('document_id', $id)->delete();
            LabelTextDocument::where('id', $id)->delete();
        }

        return redirect()->back()->with('success', "Đã xóa {$count} tài liệu văn bản!");
    }

    public function deleteAnnotations(Request $request)
    {
        $request->validate([
            'annotation_ids' => 'required|array',
            'annotation_ids.*' => 'integer',
        ]);

        $annIds = $request->input('annotation_ids');
        $count = count($annIds);

        LabelTextAnnotation::whereIn('id', $annIds)->delete();

        return redirect()->back()->with('success', "Đã xóa {$count} nhãn thực thể!");
    }
}
