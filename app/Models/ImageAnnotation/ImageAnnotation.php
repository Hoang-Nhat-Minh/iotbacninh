<?php

namespace App\Models\ImageAnnotation;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImageAnnotation extends Model
{
    use HasFactory;

    protected $table = 'image_annotations';

    protected $fillable = [
        'image_id',
        'label_id',
        'type',
        'data',
        'description',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(AnnotationImage::class, 'image_id');
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(AnnotationLabel::class, 'label_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(AnnotationIssue::class, 'annotation_id');
    }
}
