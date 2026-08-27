<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LabelJob extends Model
{
    use HasFactory;

    protected $table = 'label_jobs';

    protected $fillable = [
        'task_id',
        'assignee_id',
        'status',
        'stage',
        'progress',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(LabelTask::class, 'task_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assignee_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(LabelReview::class, 'job_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(LabelReviewIssue::class, 'job_id');
    }
}
