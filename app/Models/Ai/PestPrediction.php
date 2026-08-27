<?php

namespace App\Models\Ai;

use App\Models\Farm\Garden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PestPrediction extends Model
{
    use HasFactory;

    protected $table = 'pest_predictions';

    protected $fillable = [
        'garden_id',
        'current_stage',
        'outbreak_at',
        'result',
        'confidence',
    ];

    protected $casts = [
        'outbreak_at' => 'datetime',
        'confidence' => 'float',
    ];

    public function garden(): BelongsTo
    {
        return $this->belongsTo(Garden::class, 'garden_id');
    }
}
