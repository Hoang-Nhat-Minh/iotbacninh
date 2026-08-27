<?php

namespace App\Models\ImageAnnotation;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnotationIssue extends Model
{
    use HasFactory;

    protected $table = 'annotation_issues';

    protected $fillable = [
        'job_id',
        'image_id',
        'annotation_id',
        'created_by',
        'description',
        'data',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(AnnotationJob::class, 'job_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(AnnotationImage::class, 'image_id');
    }

    public function annotation(): BelongsTo
    {
        return $this->belongsTo(ImageAnnotation::class, 'annotation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
