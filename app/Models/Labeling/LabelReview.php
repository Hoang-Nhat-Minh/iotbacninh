<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelReview extends Model
{
    use HasFactory;

    protected $table = 'label_reviews';

    protected $fillable = [
        'job_id',
        'reviewer_id',
        'status',
        'decision',
        'comment',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(LabelJob::class, 'job_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewer_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(LabelReviewIssue::class, 'review_id');
    }
}
