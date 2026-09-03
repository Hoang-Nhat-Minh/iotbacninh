<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Lấy giá trị cấu hình theo key
     */
    public static function getValue(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }
}
