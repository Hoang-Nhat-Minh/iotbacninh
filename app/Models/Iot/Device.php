<?php

namespace App\Models\Iot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';

    protected $fillable = [
        'monitoring_station_id',
        'name',
        'code',
        'type',
        'sensor_type',
        'status',
    ];

    protected static function booted(): void
    {
        static::deleting(function ($device) {
            $device->sensorReadings()->delete();
            $device->cameraMedia()->delete();
        });
    }

    public function monitoringStation(): BelongsTo
    {
        return $this->belongsTo(MonitoringStation::class, 'monitoring_station_id');
    }

    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class, 'device_id');
    }

    public function cameraMedia(): HasMany
    {
        return $this->hasMany(CameraMedia::class, 'device_id');
    }
}
