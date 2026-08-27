<?php

namespace App\Models\TextAnnotation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TextAnnotationLabel extends Model
{
    use HasFactory;

    protected $table = 'text_annotation_labels';

    protected $fillable = [
        'project_id',
        'name',
        'type',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(TextAnnotationProject::class, 'project_id');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(TextAnnotation::class, 'label_id');
    }
}
