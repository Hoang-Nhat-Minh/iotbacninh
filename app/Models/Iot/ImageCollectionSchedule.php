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
        'status',
    ];

    protected $casts = [
        'interval' => 'integer',
    ];

    public function monitoringStation()
    {
        return $this->belongsTo(MonitoringStation::class, 'monitoring_station_id');
    }

    public function getIntervalMinutesAttribute()
    {
        return $this->interval;
    }
}
