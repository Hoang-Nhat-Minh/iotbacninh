<?php

namespace App\Models\Ai;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosisFeedback extends Model
{
    use HasFactory;

    protected $table = 'diagnosis_feedback';

    protected $fillable = [
        'diagnosis_id',
        'user_id',
        'rating',
        'content',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(DiseaseDiagnosis::class, 'diagnosis_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
