<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantAddon extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'addon_key',
        'active',
        'metadata',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'metadata' => 'array',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Vérifier si l'addon est actif et valide
     */
    public function isValid(): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}

