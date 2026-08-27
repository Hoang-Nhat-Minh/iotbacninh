<?php

namespace App\Models\Iot;

use App\Models\Farm\Garden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MonitoringStation extends Model
{
    use HasFactory;

    protected $table = 'monitoring_stations';

    protected $fillable = [
        'garden_id',
        'name',
        'code',
        'latitude',
        'longitude',
        'status',
        'data_interval',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'data_interval' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function ($station) {
            foreach ($station->devices as $device) {
                $device->delete();
            }
            $station->imageCaptureLocations()->delete();
            ImageCollectionSchedule::where('monitoring_station_id', $station->id)->delete();
        });
    }

    public function garden(): BelongsTo
    {
        return $this->belongsTo(Garden::class, 'garden_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'monitoring_station_id');
    }

    public function cameraMedia(): HasManyThrough
    {
        return $this->hasManyThrough(CameraMedia::class, Device::class, 'monitoring_station_id', 'device_id');
    }

    public function imageCaptureLocations(): HasMany
    {
        return $this->hasMany(ImageCaptureLocation::class, 'monitoring_station_id');
    }
}
