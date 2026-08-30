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
        'monitoring_station_id',
        'device_id',
        'value',
        'data',
        'recorded_at',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
        'value' => 'double',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(MonitoringStation::class, 'monitoring_station_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

}
