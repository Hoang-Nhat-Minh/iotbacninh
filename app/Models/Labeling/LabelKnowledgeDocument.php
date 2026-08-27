<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelKnowledgeDocument extends Model
{
    use HasFactory;

    protected $table = 'label_knowledge_documents';

    protected $fillable = [
        'knowledge_base_id',
        'title',
        'content',
        'source',
        'source_type',
        'file_path',
        'status',
        'created_by',
    ];

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(LabelKnowledgeBase::class, 'knowledge_base_id');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(LabelKnowledgeChunk::class, 'document_id');
    }
}
