<?php

namespace App\Models\Iot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageCollectionSchedule extends Model
{
    use HasFactory;

    protected $table = 'image_collection_schedules';

    protected $fillable = [
        'monitoring_station_id',
        'name',
        'start_time',
        'end_time',
        'interval',
        'camera_id',
        'status',
    ];

    protected $casts = [
        'interval' => 'integer',
    ];

    public function getCameraLabelAttribute(): string
    {
        $camLabels = [
            'all' => 'Tất cả Camera',
            'cam_1' => 'Camera 01 (Toàn cảnh)',
            'cam_2' => 'Camera 02 (Cận cảnh)',
            'cam_3' => 'Camera 03 (Khu vực đất)',
            'cam_4' => 'Camera 04 (Lối vào vườn)',
        ];

        return $camLabels[$this->camera_id ?? 'all'] ?? ($this->camera_id ?: 'Tất cả Camera');
    }

    public function monitoringStation()
    {
        return $this->belongsTo(MonitoringStation::class, 'monitoring_station_id');
    }

    public function getIntervalMinutesAttribute()
    {
        return $this->interval;
    }
}
