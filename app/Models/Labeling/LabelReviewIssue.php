<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelReviewIssue extends Model
{
    use HasFactory;

    protected $table = 'label_review_issues';

    protected $fillable = [
        'review_id',
        'image_id',
        'annotation_id',
        'issue_type',
        'description',
        'coordinates',
        'status',
        'created_by',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(LabelReview::class, 'review_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(LabelImage::class, 'image_id');
    }
}
