<?php

namespace App\Models;

use App\Models\Account\Role;
use App\Models\Account\UserSetting;
use App\Models\Ai\DiagnosisFeedback;
use App\Models\Ai\DiseaseDiagnosis;
use App\Models\Chatbot\ChatbotConversation;
use App\Models\Content\News;
use App\Models\Farm\CareCategory;
use App\Models\Farm\CareHistory;
use App\Models\Farm\CareProcess;
use App\Models\Farm\Garden;
use App\Models\Farm\UsedProduct;
use App\Models\Notification\Notification;
use App\Models\System\AccessLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'role_id',
        'username',
        'name',
        'phone',
        'email',
        'password',
        'avatar',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hasRole($roles): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }

        if (!$this->role) {
            return false;
        }

        $slug = $this->role->slug;

        if (is_array($roles)) {
            return in_array($slug, $roles);
        }

        return $slug === $roles;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    /* Relationships */

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class, 'user_id');
    }

    public function gardens(): HasMany
    {
        return $this->hasMany(Garden::class, 'user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function careCategories(): HasMany
    {
        return $this->hasMany(CareCategory::class, 'user_id');
    }

    public function careHistories(): HasMany
    {
        return $this->hasMany(CareHistory::class, 'user_id');
    }

    public function usedProducts(): HasMany
    {
        return $this->hasMany(UsedProduct::class, 'user_id');
    }

    public function careProcesses(): HasMany
    {
        return $this->hasMany(CareProcess::class, 'user_id');
    }

    public function diseaseDiagnoses(): HasMany
    {
        return $this->hasMany(DiseaseDiagnosis::class, 'user_id');
    }

    public function diagnosisFeedback(): HasMany
    {
        return $this->hasMany(DiagnosisFeedback::class, 'user_id');
    }

    public function chatbotConversations(): HasMany
    {
        return $this->hasMany(ChatbotConversation::class, 'user_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class, 'user_id');
    }

    public function bookmarkedNews(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_user_bookmarks', 'user_id', 'news_id');
    }
}
