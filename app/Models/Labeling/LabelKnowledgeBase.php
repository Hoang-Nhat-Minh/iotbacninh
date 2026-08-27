<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelKnowledgeBase extends Model
{
    use HasFactory;

    protected $table = 'label_knowledge_bases';

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(LabelKnowledgeDocument::class, 'knowledge_base_id');
    }
}
