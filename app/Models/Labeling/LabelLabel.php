<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelLabel extends Model
{
    use HasFactory;

    protected $table = 'label_labels';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'color',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(LabelProject::class, 'project_id');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(LabelAnnotation::class, 'label_id');
    }
}
