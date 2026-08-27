<?php

namespace App\Models\Content;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsUserBookmark extends Model
{
    use HasFactory;

    protected $table = 'news_user_bookmarks';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'news_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class, 'news_id');
    }
}
