<?php

namespace App\Http\Controllers\Labeling;

use App\Http\Controllers\Controller;
use App\Models\Labeling\LabelAnnotation;
use App\Models\Labeling\LabelImage;
use App\Models\Labeling\LabelJob;
use App\Models\Labeling\LabelLabel;
use App\Models\Labeling\LabelProject;
use App\Models\Labeling\LabelTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImageAnnotationController extends Controller
{
    public function index(Request $request)
    {
        $projects = LabelProject::latest()->get();
        $allTasks = LabelTask::with('project')->latest()->get();

        $selectedTaskId = $request->get('task_id');
        $selectedProjectId = null;

        if ($selectedTaskId) {
            $task = LabelTask::find($selectedTaskId);
            if ($task) {
                $selectedProjectId = $task->project_id;
            }
        }

        if (!$selectedProjectId) {
            $selectedProjectId = $request->get('project_id', $projects->first()?->id);
        }

        $tasks = LabelTask::where('project_id', $selectedProjectId)->get();
        if (!$selectedTaskId || !$tasks->contains('id', $selectedTaskId)) {
            $selectedTaskId = $tasks->first()?->id;
        }

        $labels = LabelLabel::where('project_id', $selectedProjectId)->get();

        $images = LabelImage::where('task_id', $selectedTaskId)->get();
        $selectedImageId = $request->get('image_id', $images->first()?->id);

        $selectedImage = $images->where('id', $selectedImageId)->first() ?? $images->first();

        $currentAnnotations = [];
        if ($selectedImage) {
            $currentAnnotations = LabelAnnotation::where('image_id', $selectedImage->id)->get();
        }

        $activeJob = LabelJob::where('task_id', $selectedTaskId)->first();

        return view('labeling.annotation.index', compact(
            'projects',
            'selectedProjectId',
            'tasks',
            'allTasks',
            'selectedTaskId',
            'labels',
            'images',
            'selectedImage',
            'currentAnnotations',
            'activeJob'
        ));
    }

    public function getAnnotations($imageId)
    {
        $annotations = LabelAnnotation::where('image_id', $imageId)->get();
        return response()->json([
            'success' => true,
            'annotations' => $annotations,
        ]);
    }

    public function saveAnnotations(Request $request)
    {
        $request->validate([
            'image_id' => 'required|integer',
            'annotations' => 'present|array',
        ]);

        $imageId = $request->input('image_id');
        $annotationsData = $request->input('annotations', []);
        $userId = Auth::id();

        $image = LabelImage::findOrFail($imageId);

        // Remove old annotations for this image
        LabelAnnotation::where('image_id', $imageId)->delete();

        // Insert new annotations
        foreach ($annotationsData as $item) {
            LabelAnnotation::create([
                'image_id' => $imageId,
                'job_id' => $request->input('job_id'),
                'label_id' => $item['label_id'],
                'annotation_type' => $item['annotation_type'] ?? 'bbox',
                'coordinates' => $item['coordinates'] ?? [],
                'description' => $item['description'] ?? null,
                'created_by' => $userId,
            ]);
        }

        // Update image status
        $image->status = count($annotationsData) > 0 ? 'labeled' : 'unlabeled';
        $image->save();

        // Calculate and update Job progress
        $taskImagesCount = LabelImage::where('task_id', $image->task_id)->count();
        $labeledImagesCount = LabelImage::where('task_id', $image->task_id)->where('status', 'labeled')->count();

        $progressPercentage = $taskImagesCount > 0 ? round(($labeledImagesCount / $taskImagesCount) * 100) : 0;

        $job = LabelJob::where('task_id', $image->task_id)->first();
        if ($job) {
            $job->progress = $progressPercentage;
            if ($progressPercentage >= 100) {
                $job->status = 'completed';
                $job->completed_at = now();
            } else {
                $job->status = 'in_progress';
            }
            $job->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Lưu gán nhãn dữ liệu hình ảnh thành công!',
            'image_status' => $image->status,
            'job_progress' => $progressPercentage,
        ]);
    }

    public function storeLabel(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        $label = LabelLabel::create([
            'project_id' => $request->input('project_id'),
            'name' => $request->input('name'),
            'color' => $request->input('color', '#ef4444'),
            'description' => $request->input('description'),
        ]);

        return redirect()->back()->with('success', "Đã thêm nhãn mới '{$label->name}' thành công!");
    }

    public function updateLabel(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        $label = LabelLabel::findOrFail($id);
        $label->update([
            'name' => $request->input('name'),
            'color' => $request->input('color'),
            'description' => $request->input('description'),
        ]);

        return redirect()->back()->with('success', "Đã cập nhật nhãn '{$label->name}' thành công!");
    }

    public function deleteLabel($id)
    {
        $label = LabelLabel::findOrFail($id);
        $name = $label->name;
        $label->delete();

        return redirect()->back()->with('success', "Đã xóa nhãn '{$name}' thành công!");
    }
}
