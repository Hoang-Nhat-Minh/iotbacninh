<?php

namespace App\Http\Controllers\Labeling;

use App\Http\Controllers\Controller;
use App\Models\Labeling\LabelAnnotation;
use App\Models\Labeling\LabelImage;
use App\Models\Labeling\LabelJob;
use App\Models\Labeling\LabelProject;
use App\Models\Labeling\LabelTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImageTaskManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = LabelTask::with(['project', 'assignee', 'job', 'images']);

        // Search Keyword
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by Project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by Assignee
        if ($request->filled('assignee_id')) {
            $query->where('assignee_id', $request->input('assignee_id'));
        }

        $tasks = $query->latest()->get();

        // Map extra metrics to each task
        $tasks->transform(function ($task) {
            $task->images_count = $task->images->count();
            $task->labeled_count = $task->images->where('status', 'labeled')->count();
            return $task;
        });

        $projects = LabelProject::where('status', 'active')->get();

        // Fetch Admin & Manager users for assignment
        $assignees = User::whereHas('role', function ($q) {
            $q->whereIn('slug', ['admin', 'manager']);
        })->get();

        return view('labeling.tasks.index', compact('tasks', 'projects', 'assignees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|integer',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|integer',
            'image_files.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $task = LabelTask::create([
            'project_id' => $request->input('project_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => 'pending',
            'assignee_id' => $request->input('assignee_id'),
            'created_by' => Auth::id(),
        ]);

        // Create associated LabelJob
        LabelJob::create([
            'task_id' => $task->id,
            'assignee_id' => $request->input('assignee_id'),
            'status' => 'pending',
            'stage' => 'annotation',
            'progress' => 0,
            'started_at' => now(),
        ]);

        // Handle uploaded image files
        if ($request->hasFile('image_files')) {
            $destinationPath = public_path('uploads/labeling/images');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            foreach ($request->file('image_files') as $file) {
                if ($file->isValid()) {
                    $fileSize = $file->getSize();
                    $mimeType = $file->getClientMimeType();
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension() ?: 'jpg';

                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $file->move($destinationPath, $filename);
                    $filePath = 'uploads/labeling/images/' . $filename;

                    LabelImage::create([
                        'task_id' => $task->id,
                        'file_name' => $originalName,
                        'file_path' => asset($filePath),
                        'file_size' => $fileSize,
                        'mime_type' => $mimeType,
                        'status' => 'unlabeled',
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', "Đã tạo Task '{$task->name}' thành công!");
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|integer',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'assignee_id' => 'nullable|integer',
        ]);

        $task = LabelTask::findOrFail($id);
        $task->update([
            'name' => $request->input('name'),
            'project_id' => $request->input('project_id'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'assignee_id' => $request->input('assignee_id'),
        ]);

        // Update Job assignee
        LabelJob::where('task_id', $task->id)->update([
            'assignee_id' => $request->input('assignee_id'),
            'status' => $request->input('status') === 'completed' ? 'completed' : 'in_progress',
        ]);

        return redirect()->back()->with('success', "Đã cập nhật thông tin Task '{$task->name}' thành công!");
    }

    public function destroy($id)
    {
        $task = LabelTask::findOrFail($id);
        $taskName = $task->name;

        // Cleanup related images, jobs, annotations
        $images = LabelImage::where('task_id', $id)->get();
        foreach ($images as $img) {
            LabelAnnotation::where('image_id', $img->id)->delete();
            $img->delete();
        }

        LabelJob::where('task_id', $id)->delete();
        $task->delete();

        return redirect()->back()->with('success', "Đã xóa Task '{$taskName}' và dữ liệu liên quan!");
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'integer',
        ]);

        $taskIds = $request->input('task_ids');
        $count = count($taskIds);

        foreach ($taskIds as $id) {
            $images = LabelImage::where('task_id', $id)->get();
            foreach ($images as $img) {
                LabelAnnotation::where('image_id', $img->id)->delete();
                $img->delete();
            }
            LabelJob::where('task_id', $id)->delete();
            LabelTask::where('id', $id)->delete();
        }

        return redirect()->back()->with('success', "Đã xóa vĩnh viễn {$count} Task được chọn!");
    }
}
