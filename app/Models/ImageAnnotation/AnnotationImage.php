<?php

namespace App\Models\ImageAnnotation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnotationImage extends Model
{
    use HasFactory;

    protected $table = 'annotation_images';

    protected $fillable = [
        'task_id',
        'file_path',
        'file_name',
        'status',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(AnnotationTask::class, 'task_id');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(ImageAnnotation::class, 'image_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(AnnotationIssue::class, 'image_id');
    }
}
