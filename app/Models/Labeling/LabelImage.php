<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelImage extends Model
{
    use HasFactory;

    protected $table = 'label_images';

    protected $fillable = [
        'task_id',
        'file_name',
        'file_path',
        'width',
        'height',
        'file_size',
        'mime_type',
        'status',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(LabelTask::class, 'task_id');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(LabelAnnotation::class, 'image_id');
    }
}
