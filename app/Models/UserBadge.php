<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_point_id',
        'badge_id',
        'unlocked_at',
        'notified_at',
        'viewed_at'
    ];

    protected $casts = [
        'unlocked_at' => 'datetime',
        'notified_at' => 'datetime',
        'viewed_at' => 'datetime'
    ];

    /**
     * User point associé
     */
    public function userPoint(): BelongsTo
    {
        return $this->belongsTo(UserPoint::class);
    }

    /**
     * Badge associé
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Badge est-il nouveau (< 7 jours)
     */
    public function getIsNewAttribute(): bool
    {
        return $this->unlocked_at->diffInDays(now()) < 7;
    }

    /**
     * Badge a-t-il été vu par l'utilisateur
     */
    public function getHasBeenViewedAttribute(): bool
    {
        return !is_null($this->viewed_at);
    }

    /**
     * Scope badges non vus
     */
    public function scopeUnviewed($query)
    {
        return $query->whereNull('viewed_at');
    }

    /**
     * Scope badges récents
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('unlocked_at', '>=', now()->subDays($days));
    }
}

