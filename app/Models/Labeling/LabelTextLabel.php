<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelTextLabel extends Model
{
    use HasFactory;

    protected $table = 'label_text_labels';

    protected $fillable = [
        'project_id',
        'name',
        'color',
        'type',
        'description',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(LabelTextProject::class, 'project_id');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(LabelTextAnnotation::class, 'label_id');
    }
}
