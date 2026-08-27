<?php

namespace App\Models\Iot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraMedia extends Model
{
    use HasFactory;

    protected $table = 'camera_media';

    protected $fillable = [
        'device_id',
        'type',
        'name',
        'file_path',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
