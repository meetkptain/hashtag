<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Draw extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = 'drawn_at';

    protected $fillable = [
        'contest_id',
        'winner_user_point_id',
        'winner_post_id',
        'rank',
        'drawn_at',
        'random_seed'
    ];

    protected $casts = [
        'drawn_at' => 'datetime',
        'rank' => 'integer'
    ];

    /**
     * Concours associé
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * User point gagnant
     */
    public function winnerUserPoint(): BelongsTo
    {
        return $this->belongsTo(UserPoint::class, 'winner_user_point_id');
    }

    /**
     * Post gagnant
     */
    public function winnerPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'winner_post_id');
    }

    /**
     * Label de rang (1er, 2ème, 3ème)
     */
    public function getRankLabelAttribute(): string
    {
        return match($this->rank) {
            1 => '1er',
            2 => '2ème',
            3 => '3ème',
            default => $this->rank . 'ème'
        };
    }
}

