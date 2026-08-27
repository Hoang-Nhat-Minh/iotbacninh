<?php

namespace App\Models\Farm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareHistory extends Model
{
    use HasFactory;

    protected $table = 'care_histories';

    protected $fillable = [
        'user_id',
        'care_category_id',
        'garden_id',
        'performed_at',
        'content',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CareCategory::class, 'care_category_id');
    }

    public function careCategory(): BelongsTo
    {
        return $this->belongsTo(CareCategory::class, 'care_category_id');
    }

    public function garden(): BelongsTo
    {
        return $this->belongsTo(Garden::class, 'garden_id');
    }
}
