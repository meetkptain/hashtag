<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'icon_url',
        'icon_svg',
        'rarity',
        'criteria',
        'active',
        'display_order'
    ];

    protected $casts = [
        'criteria' => 'array',
        'active' => 'boolean',
        'display_order' => 'integer'
    ];

    /**
     * User badges (qui a obtenu ce badge)
     */
    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Nombre de fois débloqué
     */
    public function getUnlocksCountAttribute(): int
    {
        return $this->userBadges()->count();
    }

    /**
     * Label rareté traduit
     */
    public function getRarityLabelAttribute(): string
    {
        return match($this->rarity) {
            'common' => 'Commun',
            'rare' => 'Rare',
            'epic' => 'Épique',
            'legendary' => 'Légendaire',
            default => 'Inconnu'
        };
    }

    /**
     * Couleur selon rareté
     */
    public function getRarityColorAttribute(): string
    {
        return match($this->rarity) {
            'common' => '#6B7280',      // gray
            'rare' => '#3B82F6',        // blue
            'epic' => '#A855F7',        // purple
            'legendary' => '#F59E0B',   // amber
            default => '#9CA3AF'
        };
    }

    /**
     * Scope badges actifs
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope par catégorie
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope hors secrets (pour affichage public)
     */
    public function scopeNotSecret($query)
    {
        return $query->where('category', '!=', 'secret');
    }
}

