<?php

namespace App\Models\ImageAnnotation;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnotationJob extends Model
{
    use HasFactory;

    protected $table = 'annotation_jobs';

    protected $fillable = [
        'task_id',
        'assignee_id',
        'status',
        'stage',
        'progress',
    ];

    protected $casts = [
        'progress' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AnnotationTask::class, 'task_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(AnnotationIssue::class, 'job_id');
    }
}
