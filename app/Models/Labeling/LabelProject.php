<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelProject extends Model
{
    use HasFactory;

    protected $table = 'label_projects';

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(LabelTask::class, 'project_id');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(LabelLabel::class, 'project_id');
    }
}
