<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LabelTask extends Model
{
    use HasFactory;

    protected $table = 'label_tasks';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'status',
        'assignee_id',
        'created_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(LabelProject::class, 'project_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assignee_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(LabelImage::class, 'task_id');
    }

    public function job(): HasOne
    {
        return $this->hasOne(LabelJob::class, 'task_id');
    }
}
