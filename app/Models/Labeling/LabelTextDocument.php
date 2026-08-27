<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelTextDocument extends Model
{
    use HasFactory;

    protected $table = 'label_text_documents';

    protected $fillable = [
        'task_id',
        'title',
        'content',
        'file_path',
        'status',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(LabelTextTask::class, 'task_id');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(LabelTextAnnotation::class, 'document_id');
    }
}
