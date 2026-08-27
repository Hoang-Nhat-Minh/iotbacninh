<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelTextTask extends Model
{
    use HasFactory;

    protected $table = 'label_text_tasks';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'task_type',
        'status',
        'assignee_id',
        'created_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(LabelTextProject::class, 'project_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assignee_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LabelTextDocument::class, 'task_id');
    }
}
