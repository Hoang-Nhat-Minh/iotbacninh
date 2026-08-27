<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelTextProject extends Model
{
    use HasFactory;

    protected $table = 'label_text_projects';

    protected $fillable = [
        'name',
        'task_type',
        'description',
        'status',
        'created_by',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(LabelTextTask::class, 'project_id');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(LabelTextLabel::class, 'project_id');
    }
}
