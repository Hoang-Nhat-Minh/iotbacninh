<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelTextAnnotation extends Model
{
    use HasFactory;

    protected $table = 'label_text_annotations';

    protected $fillable = [
        'document_id',
        'label_id',
        'start_position',
        'end_position',
        'start_offset',
        'end_offset',
        'content',
        'selected_text',
        'description',
        'created_by',
    ];

    protected $casts = [
        'start_position' => 'integer',
        'end_position' => 'integer',
        'start_offset' => 'integer',
        'end_offset' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(LabelTextDocument::class, 'document_id');
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(LabelTextLabel::class, 'label_id');
    }
}
