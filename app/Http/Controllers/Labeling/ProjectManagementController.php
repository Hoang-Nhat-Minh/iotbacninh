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

class ProjectManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = LabelProject::with(['tasks', 'labels']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $projects = $query->latest()->get();

        // Calculate counts
        $projects->transform(function ($p) {
            $p->tasks_count = $p->tasks->count();
            $p->labels_count = $p->labels->count();
            $taskIds = $p->tasks->pluck('id');
            $p->images_count = LabelImage::whereIn('task_id', $taskIds)->count();
            return $p;
        });

        return view('labeling.projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $project = LabelProject::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'created_by' => Auth::id(),
        ]);

        // Default CVAT-style labels if requested
        if ($request->has('create_default_labels')) {
            $defaultLabels = [
                ['name' => 'Sâu đục cuống quả', 'color' => '#ef4444', 'description' => 'Vết sâu đục cuống'],
                ['name' => 'Bệnh sương mai', 'color' => '#f59e0b', 'description' => 'Vết đốm mốc sương mai'],
                ['name' => 'Đốm đen / Mốc lá', 'color' => '#8b5cf6', 'description' => 'Vết mốc lá'],
                ['name' => 'Vùng lá khỏe mạnh', 'color' => '#10b981', 'description' => 'Mô lá xanh bình thường'],
            ];

            foreach ($defaultLabels as $lbl) {
                LabelLabel::create([
                    'project_id' => $project->id,
                    'name' => $lbl['name'],
                    'color' => $lbl['color'],
                    'description' => $lbl['description'],
                ]);
            }
        }

        return redirect()->back()->with('success', "Đã tạo Dự án AI '{$project->name}' thành công!");
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $project = LabelProject::findOrFail($id);
        $project->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
        ]);

        return redirect()->back()->with('success', "Đã cập nhật Dự án '{$project->name}' thành công!");
    }

    public function destroy($id)
    {
        $project = LabelProject::findOrFail($id);
        $name = $project->name;

        // Cleanup related tasks, jobs, images, labels
        $tasks = LabelTask::where('project_id', $id)->get();
        foreach ($tasks as $t) {
            $images = LabelImage::where('task_id', $t->id)->get();
            foreach ($images as $img) {
                LabelAnnotation::where('image_id', $img->id)->delete();
                $img->delete();
            }
            LabelJob::where('task_id', $t->id)->delete();
            $t->delete();
        }

        LabelLabel::where('project_id', $id)->delete();
        $project->delete();

        return redirect()->back()->with('success', "Đã xóa vĩnh viễn Dự án '{$name}' và dữ liệu liên quan!");
    }
}
