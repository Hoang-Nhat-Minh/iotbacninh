<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelKnowledgeChunk extends Model
{
    use HasFactory;

    protected $table = 'label_knowledge_chunks';

    protected $fillable = [
        'document_id',
        'content',
        'chunk_text',
        'token_count',
        'vector_id',
        'status',
        'chunk_index',
        'embedding',
        'metadata',
    ];

    protected $casts = [
        'chunk_index' => 'integer',
        'token_count' => 'integer',
        'embedding' => 'array',
        'metadata' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(LabelKnowledgeDocument::class, 'document_id');
    }
}
