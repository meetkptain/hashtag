<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leaderboard extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'period',
        'user_point_id',
        'rank',
        'points'
    ];

    protected $casts = [
        'rank' => 'integer',
        'points' => 'integer',
        'created_at' => 'datetime'
    ];

    /**
     * User point associé
     */
    public function userPoint(): BelongsTo
    {
        return $this->belongsTo(UserPoint::class);
    }

    /**
     * Scope par type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope par période
     */
    public function scopeOfPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope top X
     */
    public function scopeTopRanks($query, int $limit = 10)
    {
        return $query->orderBy('rank', 'asc')->limit($limit);
    }
}

