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
        $map = [
            'all' => 'Tất cả Camera (1 - 4)',
            'cam_1' => 'Camera 01',
            'cam_2' => 'Camera 02',
            'cam_3' => 'Camera 03',
            'cam_4' => 'Camera 04',
        ];

        return $map[$this->camera_id ?? 'all'] ?? ($this->camera_id ?: 'Tất cả Camera');
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
