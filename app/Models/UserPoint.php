<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPoint extends Model
{
    protected $fillable = [
        'user_identifier',
        'platform',
        'total_points',
        'weekly_points',
        'monthly_points',
        'last_post_at',
        'streak_days'
    ];

    protected $casts = [
        'last_post_at' => 'datetime',
        'total_points' => 'integer',
        'weekly_points' => 'integer',
        'monthly_points' => 'integer',
        'streak_days' => 'integer'
    ];

    /**
     * Transactions de points
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * Badges obtenus
     */
    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Entrées concours
     */
    public function contestEntries(): HasMany
    {
        return $this->hasMany(ContestEntry::class);
    }

    /**
     * Rang au leaderboard global
     */
    public function getRankAttribute(): int
    {
        return static::where('total_points', '>', $this->total_points)->count() + 1;
    }

    /**
     * Rang hebdomadaire
     */
    public function getWeeklyRankAttribute(): int
    {
        return static::where('weekly_points', '>', $this->weekly_points)->count() + 1;
    }

    /**
     * Rang mensuel
     */
    public function getMonthlyRankAttribute(): int
    {
        return static::where('monthly_points', '>', $this->monthly_points)->count() + 1;
    }

    /**
     * Nombre de badges
     */
    public function getBadgeCountAttribute(): int
    {
        return $this->badges()->count();
    }
}

