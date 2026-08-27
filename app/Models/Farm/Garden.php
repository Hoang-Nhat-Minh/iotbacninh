<?php

namespace App\Models\Farm;

use App\Models\Ai\DiseaseDiagnosis;
use App\Models\Ai\PestPrediction;
use App\Models\Iot\MonitoringStation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Garden extends Model
{
    use HasFactory;

    protected $table = 'gardens';

    protected $fillable = [
        'user_id',
        'name',
        'code',
        'crop_type',
        'area_m2',
        'location',
        'latitude',
        'longitude',
        'boundary',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'boundary' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function monitoringStations(): HasMany
    {
        return $this->hasMany(MonitoringStation::class, 'garden_id');
    }

    public function stations(): HasMany
    {
        return $this->hasMany(MonitoringStation::class, 'garden_id');
    }

    public function careHistories(): HasMany
    {
        return $this->hasMany(CareHistory::class, 'garden_id');
    }

    public function diseaseDiagnoses(): HasMany
    {
        return $this->hasMany(DiseaseDiagnosis::class, 'garden_id');
    }

    public function pestPredictions(): HasMany
    {
        return $this->hasMany(PestPrediction::class, 'garden_id');
    }
}
