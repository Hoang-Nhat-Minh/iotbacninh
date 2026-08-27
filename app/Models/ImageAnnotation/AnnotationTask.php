<?php

namespace App\Models\ImageAnnotation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnotationTask extends Model
{
    use HasFactory;

    protected $table = 'annotation_tasks';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(AnnotationProject::class, 'project_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(AnnotationJob::class, 'task_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(AnnotationImage::class, 'task_id');
    }
}
