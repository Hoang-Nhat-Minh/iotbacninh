<?php

namespace App\Models\Ai;

use App\Models\Farm\Garden;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiseaseDiagnosis extends Model
{
    use HasFactory;

    protected $table = 'disease_diagnoses';

    protected $fillable = [
        'user_id',
        'garden_id',
        'image_path',
        'disease',
        'confidence',
        'result',
    ];

    protected $casts = [
        'confidence' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function garden(): BelongsTo
    {
        return $this->belongsTo(Garden::class, 'garden_id');
    }

    public function diagnosisFeedback(): HasMany
    {
        return $this->hasMany(DiagnosisFeedback::class, 'diagnosis_id');
    }
}
