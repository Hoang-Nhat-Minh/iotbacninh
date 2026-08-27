<?php

namespace App\Models\TextAnnotation;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TextAnnotationProject extends Model
{
    use HasFactory;

    protected $table = 'text_annotation_projects';

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
        return $this->hasMany(TextAnnotationTask::class, 'project_id');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(TextAnnotationLabel::class, 'project_id');
    }
}
