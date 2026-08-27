<?php

namespace App\Models\Labeling;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelExport extends Model
{
    use HasFactory;

    protected $table = 'label_exports';

    protected $fillable = [
        'project_id',
        'task_id',
        'job_id',
        'export_type',
        'format',
        'file_name',
        'file_path',
        'file_size',
        'status',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(LabelProject::class, 'project_id');
    }

    public function textProject(): BelongsTo
    {
        return $this->belongsTo(LabelTextProject::class, 'project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
