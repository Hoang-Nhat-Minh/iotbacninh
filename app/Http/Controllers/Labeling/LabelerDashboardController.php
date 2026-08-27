<?php

namespace App\Http\Controllers\Labeling;

use App\Http\Controllers\Controller;
use App\Models\Labeling\LabelJob;

class LabelerDashboardController extends Controller
{
    public function index()
    {
        $imageProjectsCount = \App\Models\Labeling\LabelProject::count();
        $imageTasksCount = \App\Models\Labeling\LabelTask::count();
        $imageJobsCount = LabelJob::count();
        $textProjectsCount = \App\Models\Labeling\LabelTextProject::count();
        $knowledgeBasesCount = \App\Models\Labeling\LabelKnowledgeBase::count();

        $recentJobs = LabelJob::with('task')->latest()->take(5)->get();

        return view('labeling.dashboard', compact(
            'imageProjectsCount',
            'imageTasksCount',
            'imageJobsCount',
            'textProjectsCount',
            'knowledgeBasesCount',
            'recentJobs'
        ));
    }
}
