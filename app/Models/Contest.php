<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contest extends Model
{
    protected $fillable = [
        'title',
        'description',
        'hashtag',
        'prize',
        'start_at',
        'end_at',
        'status',
        'winners_count',
        'criteria'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'criteria' => 'array',
        'winners_count' => 'integer'
    ];

    /**
     * Entrées du concours
     */
    public function entries(): HasMany
    {
        return $this->hasMany(ContestEntry::class);
    }

    /**
     * Gagnants (draws)
     */
    public function draws(): HasMany
    {
        return $this->hasMany(Draw::class);
    }

    /**
     * Concours est-il actif maintenant
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' 
            && $this->start_at <= now() 
            && $this->end_at >= now();
    }

    /**
     * Temps restant (en secondes)
     */
    public function getTimeRemainingAttribute(): ?int
    {
        if (!$this->is_active) {
            return null;
        }
        return now()->diffInSeconds($this->end_at, false);
    }

    /**
     * Nombre de participants
     */
    public function getParticipantsCountAttribute(): int
    {
        return $this->entries()->distinct('user_point_id')->count();
    }

    /**
     * Scope concours actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now());
    }

    /**
     * Scope concours à venir
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'draft')
            ->where('start_at', '>', now());
    }

    /**
     * Scope concours terminés
     */
    public function scopeEnded($query)
    {
        return $query->whereIn('status', ['ended', 'drawn'])
            ->orWhere('end_at', '<', now());
    }
}

