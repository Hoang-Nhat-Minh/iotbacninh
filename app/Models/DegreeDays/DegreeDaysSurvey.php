<?php

namespace App\Models\DegreeDays;

use App\Models\Iot\MonitoringStation;
use App\Models\Iot\SensorReading;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DegreeDaysSurvey extends Model
{
    use HasFactory;

    protected $table = 'degree_days_surveys';

    protected $fillable = [
        'user_id',
        'user_name',
        'monitoring_station_id',
        'station_code',
        'station_name',
        'garden_name',
        'surveyed_at',
        'object_type',
        'development_stage',
        'quantity_range',
        'affected_part',
        'infection_rate_range',
        'severity',
        'image_path',
        'notes',
        'iot_sensor_reading_id',
        'iot_recorded_at',
        'iot_temperature',
        'iot_humidity',
        'iot_rainfall',
        'iot_light',
        'iot_wind_speed',
        'iot_soil_moisture',
        'iot_soil_temp',
        'iot_soil_ph',
        'iot_snapshot',
    ];

    protected $casts = [
        'surveyed_at' => 'datetime',
        'iot_recorded_at' => 'datetime',
        'iot_snapshot' => 'array',
        'iot_temperature' => 'float',
        'iot_humidity' => 'float',
        'iot_rainfall' => 'float',
        'iot_light' => 'integer',
        'iot_wind_speed' => 'float',
        'iot_soil_moisture' => 'float',
        'iot_soil_temp' => 'float',
        'iot_soil_ph' => 'float',
    ];

    /* Relationships */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(MonitoringStation::class, 'monitoring_station_id');
    }

    public function sensorReading(): BelongsTo
    {
        return $this->belongsTo(SensorReading::class, 'iot_sensor_reading_id');
    }

    /* Helper Accessors */

    public function getObjectTypeLabelAttribute(): string
    {
        return match ($this->object_type) {
            'pest' => 'Sâu đục cuống quả',
            'disease' => 'Bệnh hại cây trồng',
            default => ucfirst($this->object_type ?? 'Không xác định'),
        };
    }

    public function getDevelopmentStageLabelAttribute(): string
    {
        return match ($this->development_stage) {
            'none' => 'Không phát hiện',
            'egg' => 'Trứng',
            'larva' => 'Sâu non',
            'pupa' => 'Nhộng',
            'adult' => 'Trưởng thành (Vũ hóa)',
            'unknown' => 'Không xác định',
            default => $this->development_stage ?? '--',
        };
    }

    public function getQuantityRangeLabelAttribute(): string
    {
        return match ($this->quantity_range) {
            'unknown' => 'Không xác định',
            '1_5' => '1 – 5 con',
            '6_20' => '6 – 20 con',
            '21_50' => '21 – 50 con',
            'gt_50' => '> 50 con',
            default => $this->quantity_range ?? '--',
        };
    }

    public function getAffectedPartLabelAttribute(): string
    {
        return match ($this->affected_part) {
            'leaf' => 'Lá',
            'flower' => 'Hoa',
            'fruit' => 'Quả',
            'branch' => 'Cành / Thân',
            'other' => 'Khác',
            default => $this->affected_part ?? '--',
        };
    }

    public function getInfectionRateLabelAttribute(): string
    {
        return match ($this->infection_rate_range) {
            'lt_5' => '< 5%',
            '5_10' => '5 – 10%',
            '10_25' => '10 – 25%',
            '25_50' => '25 – 50%',
            'gt_50' => '> 50%',
            default => $this->infection_rate_range ?? '--',
        };
    }

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'none' => 'Không có',
            'low' => 'Ít',
            'medium' => 'Trung bình',
            'high' => 'Nhiều',
            'outbreak' => 'Bùng phát',
            default => ucfirst($this->severity ?? 'Không có'),
        };
    }

    public function getSeverityBadgeClassAttribute(): string
    {
        return match ($this->severity) {
            'none' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            'low' => 'bg-info-subtle text-info border border-info-subtle',
            'medium' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'high' => 'bg-danger-subtle text-danger border border-danger-subtle',
            'outbreak' => 'bg-danger text-white fw-bold',
            default => 'bg-light text-muted border',
        };
    }
}
