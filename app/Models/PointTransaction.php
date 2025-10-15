<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends Model
{
    const UPDATED_AT = null; // Pas de updated_at pour historique

    protected $fillable = [
        'user_point_id',
        'post_id',
        'points_awarded',
        'transaction_type',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'points_awarded' => 'integer',
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
     * Post associé
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }
}

