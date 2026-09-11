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
        'camera_id',
        'schedule_id',
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

    public function getCameraLabelAttribute(): string
    {
        $camLabels = [
            'cam_1' => 'Camera 01 (Toàn cảnh)',
            'cam_2' => 'Camera 02 (Cận cảnh)',
            'cam_3' => 'Camera 03 (Khu vực đất)',
            'cam_4' => 'Camera 04 (Lối vào vườn)',
        ];

        return $camLabels[$this->camera_id ?? 'cam_1'] ?? ($this->camera_id ?: 'Camera 01');
    }

    public function monitoringStation(): BelongsTo
    {
        return $this->belongsTo(MonitoringStation::class, 'monitoring_station_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ImageCollectionSchedule::class, 'schedule_id');
    }
}
