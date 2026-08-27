<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelAnnotation extends Model
{
    use HasFactory;

    protected $table = 'label_annotations';

    protected $fillable = [
        'image_id',
        'job_id',
        'label_id',
        'annotation_type',
        'coordinates',
        'description',
        'created_by',
    ];

    protected $casts = [
        'coordinates' => 'array',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(LabelImage::class, 'image_id');
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(LabelLabel::class, 'label_id');
    }
}
