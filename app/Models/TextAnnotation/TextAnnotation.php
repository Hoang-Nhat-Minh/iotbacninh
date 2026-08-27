<?php

namespace App\Models\TextAnnotation;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TextAnnotation extends Model
{
    use HasFactory;

    protected $table = 'text_annotations';

    protected $fillable = [
        'task_id',
        'label_id',
        'start',
        'end',
        'content',
        'created_by',
    ];

    protected $casts = [
        'start' => 'integer',
        'end' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TextAnnotationTask::class, 'task_id');
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(TextAnnotationLabel::class, 'label_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
