<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'type',
        'config',
        'credentials',
        'refresh_interval',
        'active',
        'last_fetched_at',
        'posts_count',
    ];

    protected $casts = [
        'config' => 'array',
        'credentials' => 'array',
        'active' => 'boolean',
        'last_fetched_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function needsRefresh(): bool
    {
        if (!$this->last_fetched_at) {
            return true;
        }

        return $this->last_fetched_at->addSeconds($this->refresh_interval)->isPast();
    }

    public function getHashtags(): array
    {
        return $this->config['hashtags'] ?? [];
    }

    public function getProviderClass(): string
    {
        $providers = config('feeds.providers');
        return $providers[$this->type] ?? null;
    }
}

