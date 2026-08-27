<?php

namespace App\Models\Iot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    use HasFactory;

    protected $table = 'sensor_readings';

    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'value',
        'recorded_at',
        'created_at',
    ];

    protected $casts = [
        'value' => 'double',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
