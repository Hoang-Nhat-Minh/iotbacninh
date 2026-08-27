<?php

namespace App\Http\Controllers\Labeling;

use App\Http\Controllers\Controller;
use App\Models\Labeling\LabelAnnotation;
use App\Models\Labeling\LabelImage;
use App\Models\Labeling\LabelJob;
use App\Models\Labeling\LabelLabel;
use App\Models\Labeling\LabelProject;
use App\Models\Labeling\LabelReview;
use App\Models\Labeling\LabelReviewIssue;
use App\Models\Labeling\LabelTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImageReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = LabelJob::with(['task.project', 'assignee', 'review.issues']);

        // Stage Filter
        if ($request->filled('stage')) {
            $query->where('stage', $request->input('stage'));
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $jobs = $query->latest()->get();

        $jobs->transform(function ($job) {
            $job->images_count = $job->task ? LabelImage::where('task_id', $job->task_id)->count() : 0;
            $job->labeled_images_count = $job->task ? LabelImage::where('task_id', $job->task_id)->where('status', 'labeled')->count() : 0;
            $job->issues_count = $job->review ? $job->review->issues->where('status', 'open')->count() : 0;
            return $job;
        });

        return view('labeling.review.index', compact('jobs'));
    }

    public function workspace($jobId, Request $request)
    {
        $job = LabelJob::with(['task.project', 'review.issues'])->findOrFail($jobId);
        $task = $job->task;
        $project = $task->project;
        $labels = LabelLabel::where('project_id', $project->id)->get();

        $images = LabelImage::where('task_id', $task->id)->get();
        $selectedImageId = $request->get('image_id', $images->first()?->id);
        $selectedImage = $images->where('id', $selectedImageId)->first() ?? $images->first();

        $annotations = [];
        if ($selectedImage) {
            $annotations = LabelAnnotation::where('image_id', $selectedImage->id)->get();
        }

        // Get or Create Review Record
        $review = LabelReview::firstOrCreate(
            ['job_id' => $job->id],
            [
                'reviewer_id' => Auth::id(),
                'status' => 'pending',
                'started_at' => now(),
            ]
        );

        // Get Open Issues for selected image
        $imageIssues = [];
        if ($selectedImage) {
            $imageIssues = LabelReviewIssue::where('review_id', $review->id)
                ->where('image_id', $selectedImage->id)
                ->get();
        }

        $allJobIssues = LabelReviewIssue::where('review_id', $review->id)->get();

        return view('labeling.review.workspace', compact(
            'job',
            'task',
            'project',
            'labels',
            'images',
            'selectedImage',
            'annotations',
            'review',
            'imageIssues',
            'allJobIssues'
        ));
    }

    public function storeIssue(Request $request)
    {
        $request->validate([
            'job_id' => 'required|integer',
            'image_id' => 'required|integer',
            'issue_type' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $jobId = $request->input('job_id');
        $imageId = $request->input('image_id');

        $review = LabelReview::firstOrCreate(
            ['job_id' => $jobId],
            [
                'reviewer_id' => Auth::id(),
                'status' => 'pending',
                'started_at' => now(),
            ]
        );

        $coords = $request->input('coordinates');
        if (!$coords) {
            $coords = [
                'x' => (float)$request->input('coord_x', 500),
                'y' => (float)$request->input('coord_y', 500),
            ];
        }

        $issue = LabelReviewIssue::create([
            'review_id' => $review->id,
            'image_id' => $imageId,
            'annotation_id' => $request->input('annotation_id'),
            'issue_type' => $request->input('issue_type'),
            'description' => $request->input('description'),
            'coordinates' => $coords,
            'status' => 'open',
            'created_by' => Auth::id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã ghi nhận báo lỗi (Issue) thành công!',
                'issue' => $issue,
            ]);
        }

        return redirect()->back()->with('success', "Đã ghi nhận báo lỗi Issue #{$issue->id} thành công!");
    }

    public function deleteIssue($id)
    {
        $issue = LabelReviewIssue::findOrFail($id);
        $issue->delete();

        return redirect()->back()->with('success', 'Đã xóa báo lỗi Issue thành công!');
    }

    public function finishReview(Request $request, $jobId)
    {
        $request->validate([
            'decision' => 'required|string|in:approved,rejected',
            'target_stage' => 'required|string|in:acceptance,annotation',
            'comment' => 'nullable|string',
        ]);

        $job = LabelJob::findOrFail($jobId);
        $decision = $request->input('decision');
        $targetStage = $request->input('target_stage');
        $comment = $request->input('comment');

        $review = LabelReview::firstOrCreate(
            ['job_id' => $job->id],
            [
                'reviewer_id' => Auth::id(),
                'started_at' => now(),
            ]
        );

        $review->update([
            'status' => 'completed',
            'decision' => $decision,
            'comment' => $comment,
            'reviewer_id' => Auth::id(),
            'completed_at' => now(),
        ]);

        // Update Job Stage and Status
        if ($decision === 'approved') {
            $job->stage = 'acceptance';
            $job->status = 'completed';
            $job->progress = 100;
        } else {
            $job->stage = 'annotation'; // Trả về cho annotator sửa lại
            $job->status = 'in_progress';
        }
        $job->save();

        // Also update Task status
        $task = LabelTask::find($job->task_id);
        if ($task) {
            $task->status = $job->status;
            $task->save();
        }

        return response()->json([
            'success' => true,
            'message' => $decision === 'approved'
                ? 'Đã duyệt ĐẠT công việc! Stage chuyển sang Acceptance.'
                : 'Đã từ chối công việc! Stage trả về Annotation để gán nhãn lại.',
            'job_stage' => $job->stage,
            'job_status' => $job->status,
        ]);
    }

    public function updateStage(Request $request)
    {
        $request->validate([
            'job_id' => 'required|integer',
            'target_stage' => 'required|string|in:annotation,validation,acceptance',
            'reviewer_note' => 'nullable|string',
        ]);

        $job = LabelJob::findOrFail($request->input('job_id'));
        $targetStage = $request->input('target_stage');
        $note = $request->input('reviewer_note');

        $job->stage = $targetStage;
        if ($targetStage === 'acceptance') {
            $job->status = 'completed';
            $job->progress = 100;
        } else {
            $job->status = 'in_progress';
        }
        $job->save();

        $task = LabelTask::find($job->task_id);
        if ($task) {
            $task->status = $job->status;
            $task->save();
        }

        $review = LabelReview::firstOrCreate(
            ['job_id' => $job->id],
            ['reviewer_id' => Auth::id(), 'started_at' => now()]
        );

        $review->update([
            'status' => 'completed',
            'decision' => $targetStage === 'acceptance' ? 'approved' : 'rejected',
            'comment' => $note,
            'completed_at' => now(),
        ]);

        return redirect()->back()->with('success', "Đã chuyển Stage Job #{$job->id} sang {$targetStage} thành công!");
    }
}
