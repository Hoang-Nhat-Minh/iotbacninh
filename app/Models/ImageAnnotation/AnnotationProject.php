<?php

namespace App\Models\ImageAnnotation;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnotationProject extends Model
{
    use HasFactory;

    protected $table = 'annotation_projects';

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(AnnotationTask::class, 'project_id');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(AnnotationLabel::class, 'project_id');
    }
}
