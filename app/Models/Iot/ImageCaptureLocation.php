<?php

namespace App\Models\Iot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageCaptureLocation extends Model
{
    use HasFactory;

    protected $table = 'image_capture_locations';

    protected $fillable = [
        'monitoring_station_id',
        'name',
        'pan_angle',
        'tilt_angle',
        'zoom_level',
        'status',
    ];

    protected $casts = [
        'pan_angle' => 'float',
        'tilt_angle' => 'float',
        'zoom_level' => 'float',
    ];

    public function monitoringStation(): BelongsTo
    {
        return $this->belongsTo(MonitoringStation::class, 'monitoring_station_id');
    }
}
