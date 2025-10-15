<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;

class Tenant extends Model
{
    use HasFactory, SoftDeletes, Billable;

    protected $fillable = [
        'name',
        'domain',
        'database',
        'email',
        'api_key',
        'plan',
        'settings',
        'branding',
        'active',
        'trial_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'branding' => 'array',
        'active' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            if (empty($tenant->api_key)) {
                $tenant->api_key = 'hmt_' . Str::random(40);
            }
            
            if (empty($tenant->database)) {
                $tenant->database = 'tenant_' . Str::slug($tenant->domain, '_');
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getPlanFeatures()
    {
        $plans = config('plans.plans');
        return $plans[$this->plan]['features'] ?? [];
    }

    public function canAddFeed(): bool
    {
        $features = $this->getPlanFeatures();
        $feedsLimit = $features['feeds'] ?? 0;
        
        if ($feedsLimit === -1) {
            return true; // illimité
        }
        
        return $this->feeds()->count() < $feedsLimit;
    }

    public function canAddHashtags(int $count = 1): bool
    {
        $features = $this->getPlanFeatures();
        $hashtagsLimit = $features['hashtags'] ?? 0;
        
        if ($hashtagsLimit === -1) {
            return true; // illimité
        }
        
        $currentCount = $this->feeds()->get()->sum(function ($feed) {
            return count($feed->config['hashtags'] ?? []);
        });
        
        return ($currentCount + $count) <= $hashtagsLimit;
    }

    public function hasGamification(): bool
    {
        $features = $this->getPlanFeatures();
        return $features['gamification'] ?? false;
    }

    public function feeds()
    {
        // Cette relation sera accessible après la connexion au tenant
        return $this->hasMany(Feed::class);
    }

    public function switchDatabase()
    {
        config(['database.connections.tenant.database' => $this->database]);
        \DB::purge('tenant');
        \DB::reconnect('tenant');
    }
    
    /**
     * Vérifier si un addon est actif
     */
    public function hasAddon(string $addonKey): bool
    {
        $this->switchDatabase();
        
        $addon = \App\Models\TenantAddon::where('addon_key', $addonKey)
            ->where('active', true)
            ->first();
        
        return $addon ? $addon->isValid() : false;
    }

    /**
     * Activer un addon
     */
    public function activateAddon(string $addonKey, ?array $metadata = null): bool
    {
        $this->switchDatabase();
        
        \App\Models\TenantAddon::create([
            'addon_key' => $addonKey,
            'active' => true,
            'metadata' => $metadata,
            'activated_at' => now(),
        ]);
        
        return true;
    }

    /**
     * Vérifier si peut utiliser mode avancé
     */
    public function canUseAdvancedMode(string $provider): bool
    {
        // Plan Enterprise = inclus
        if ($this->plan === 'enterprise') {
            return true;
        }
        
        // Plans inférieurs = vérifier addon
        $addonKey = "{$provider}_connection";
        return $this->hasAddon($addonKey);
    }
}

