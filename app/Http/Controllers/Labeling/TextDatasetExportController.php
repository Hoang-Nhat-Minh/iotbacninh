<?php

namespace App\Http\Controllers\Labeling;

use App\Http\Controllers\Controller;
use App\Models\Labeling\LabelExport;
use App\Models\Labeling\LabelTextAnnotation;
use App\Models\Labeling\LabelTextDocument;
use App\Models\Labeling\LabelTextLabel;
use App\Models\Labeling\LabelTextProject;
use App\Models\Labeling\LabelTextTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

class TextDatasetExportController extends Controller
{
    public function index()
    {
        $projects = LabelTextProject::with(['tasks', 'labels'])->get();

        $projects->transform(function ($project) {
            $taskIds = $project->tasks->pluck('id');
            $project->tasks_count = count($taskIds);
            $project->total_documents = LabelTextDocument::whereIn('task_id', $taskIds)->count();
            $project->labeled_documents = LabelTextDocument::whereIn('task_id', $taskIds)->where('status', 'labeled')->count();
            $project->labels_count = $project->labels->count();
            return $project;
        });

        $exports = LabelExport::with(['textProject', 'user'])->where('export_type', 'text')->latest()->get();

        return view('labeling.text.export', compact('projects', 'exports'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer',
            'format' => 'required|string|in:json,jsonl,csv,conll',
            'archive_name' => 'nullable|string|max:255',
        ]);

        $projectId = $request->input('project_id');
        $format = $request->input('format');

        $project = LabelTextProject::findOrFail($projectId);
        $taskIds = LabelTextTask::where('project_id', $project->id)->pluck('id');
        $documents = LabelTextDocument::whereIn('task_id', $taskIds)->get();

        $archiveName = $request->input('archive_name')
            ? str_replace(' ', '_', preg_replace('/[^A-Za-z0-9_\-]/', '', $request->input('archive_name')))
            : "text_dataset_{$project->id}_" . strtolower($format) . '_' . date('Ymd_His');

        $exportDir = public_path('exports/labeling');
        if (!file_exists($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $zipFileName = $archiveName . '.zip';
        $zipFilePath = $exportDir . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Không thể tạo file nén .zip!');
        }

        // Build dataset content based on format
        if ($format === 'json') {
            $this->buildJsonExport($zip, $project, $documents);
        } elseif ($format === 'jsonl') {
            $this->buildJsonlExport($zip, $project, $documents);
        } elseif ($format === 'csv') {
            $this->buildCsvExport($zip, $project, $documents);
        } else {
            $this->buildConllExport($zip, $project, $documents);
        }

        $zip->close();

        $fileSize = file_exists($zipFilePath) ? filesize($zipFilePath) : 0;
        $fileUrl = asset('exports/labeling/' . $zipFileName);

        // Record in database
        LabelExport::create([
            'project_id' => $project->id,
            'export_type' => 'text',
            'format' => $format,
            'file_name' => $zipFileName,
            'file_path' => $fileUrl,
            'file_size' => $fileSize,
            'status' => 'completed',
            'created_by' => Auth::id(),
        ]);

        return response()->download($zipFilePath, $zipFileName);
    }

    private function buildJsonExport($zip, $project, $documents)
    {
        $jsonArray = [];

        foreach ($documents as $doc) {
            $annotations = LabelTextAnnotation::where('document_id', $doc->id)->get();
            $entities = [];

            foreach ($annotations as $ann) {
                $label = LabelTextLabel::find($ann->label_id);
                $entities[] = [
                    'label' => $label ? $label->name : 'Unassigned',
                    'text' => $ann->selected_text ?: $ann->content,
                    'start_offset' => $ann->start_offset ?? $ann->start_position,
                    'end_offset' => $ann->end_offset ?? $ann->end_position,
                ];
            }

            $jsonArray[] = [
                'id' => $doc->id,
                'title' => $doc->title,
                'content' => $doc->content,
                'entities' => $entities,
            ];
        }

        $content = json_encode($jsonArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $zip->addFromString('dataset.json', $content);
    }

    private function buildJsonlExport($zip, $project, $documents)
    {
        $lines = "";

        foreach ($documents as $doc) {
            $annotations = LabelTextAnnotation::where('document_id', $doc->id)->get();
            $entities = [];

            foreach ($annotations as $ann) {
                $label = LabelTextLabel::find($ann->label_id);
                $entities[] = [
                    'label' => $label ? $label->name : 'Unassigned',
                    'text' => $ann->selected_text ?: $ann->content,
                    'start_offset' => $ann->start_offset ?? $ann->start_position,
                    'end_offset' => $ann->end_offset ?? $ann->end_position,
                ];
            }

            $item = [
                'id' => $doc->id,
                'title' => $doc->title,
                'text' => $doc->content,
                'entities' => $entities,
            ];

            $lines .= json_encode($item, JSON_UNESCAPED_UNICODE) . "\n";
        }

        $zip->addFromString('dataset.jsonl', $lines);
    }

    private function buildCsvExport($zip, $project, $documents)
    {
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, ['Document_ID', 'Document_Title', 'Entity_Label', 'Selected_Text', 'Start_Offset', 'End_Offset', 'Full_Content']);

        foreach ($documents as $doc) {
            $annotations = LabelTextAnnotation::where('document_id', $doc->id)->get();

            if ($annotations->isEmpty()) {
                fputcsv($fp, [$doc->id, $doc->title, 'None', '', 0, 0, $doc->content]);
            } else {
                foreach ($annotations as $ann) {
                    $label = LabelTextLabel::find($ann->label_id);
                    fputcsv($fp, [
                        $doc->id,
                        $doc->title,
                        $label ? $label->name : 'Unassigned',
                        $ann->selected_text ?: $ann->content,
                        $ann->start_offset ?? $ann->start_position,
                        $ann->end_offset ?? $ann->end_position,
                        $doc->content
                    ]);
                }
            }
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        $zip->addFromString('dataset.csv', $csvContent);
    }

    private function buildConllExport($zip, $project, $documents)
    {
        $conllContent = "";

        foreach ($documents as $doc) {
            $conllContent .= "# doc_id = {$doc->id}\n# title = {$doc->title}\n";
            $words = preg_split('/\s+/', $doc->content);

            $annotations = LabelTextAnnotation::where('document_id', $doc->id)->get();

            foreach ($words as $word) {
                $matchedLabel = 'O';
                foreach ($annotations as $ann) {
                    $selectedText = $ann->selected_text ?: $ann->content;
                    if (mb_strpos($selectedText, $word) !== false) {
                        $label = LabelTextLabel::find($ann->label_id);
                        $matchedLabel = 'B-' . str_replace(' ', '_', $label ? $label->name : 'ENTITY');
                        break;
                    }
                }
                $conllContent .= "{$word}\t{$matchedLabel}\n";
            }
            $conllContent .= "\n";
        }

        $zip->addFromString('dataset.conll', $conllContent);
    }
}
