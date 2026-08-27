<?php

namespace App\Models\ImageAnnotation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnotationLabel extends Model
{
    use HasFactory;

    protected $table = 'annotation_labels';

    protected $fillable = [
        'project_id',
        'name',
        'description',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(AnnotationProject::class, 'project_id');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(ImageAnnotation::class, 'label_id');
    }
}
