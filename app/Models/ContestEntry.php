<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestEntry extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = 'entry_date';

    protected $fillable = [
        'contest_id',
        'user_point_id',
        'post_id',
        'entry_date',
        'is_valid'
    ];

    protected $casts = [
        'entry_date' => 'datetime',
        'is_valid' => 'boolean'
    ];

    /**
     * Concours associé
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

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
     * Scope entrées valides
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }
}

