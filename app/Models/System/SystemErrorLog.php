<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemErrorLog extends Model
{
    use HasFactory;

    protected $table = 'system_error_logs';

    protected $fillable = [
        'level',
        'message',
        'context',
        'source',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
