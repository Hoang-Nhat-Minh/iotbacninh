<?php

namespace App\Models\Account;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $table = 'user_settings';

    protected $fillable = [
        'user_id',
        'notifications_enabled',
        'weather_alert_enabled',
        'disease_alert_enabled',
        'language',
        'theme',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'weather_alert_enabled' => 'boolean',
        'disease_alert_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
