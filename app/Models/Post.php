<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'feed_id',
        'external_id',
        'platform',
        'content',
        'author_name',
        'author_username',
        'author_avatar',
        'media',
        'likes_count',
        'comments_count',
        'shares_count',
        'rating',
        'hashtags',
        'url',
        'posted_at',
        'is_new',
        'is_highlighted',
        'display_count',
    ];

    protected $casts = [
        'media' => 'array',
        'hashtags' => 'array',
        'posted_at' => 'datetime',
        'is_new' => 'boolean',
        'is_highlighted' => 'boolean',
    ];

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }

    public function analytics()
    {
        return $this->hasMany(Analytic::class);
    }

    public function markAsViewed()
    {
        $this->increment('display_count');
        
        // Après 10 affichages, le post n'est plus "nouveau"
        if ($this->display_count >= 10) {
            $this->update(['is_new' => false]);
        }
    }

    public function shouldHighlight(array $tenantHashtags): bool
    {
        if (empty($this->hashtags) || empty($tenantHashtags)) {
            return false;
        }

        $postHashtags = array_map('strtolower', $this->hashtags);
        $tenantHashtags = array_map('strtolower', $tenantHashtags);

        return !empty(array_intersect($postHashtags, $tenantHashtags));
    }

    public function getEngagementScore(): int
    {
        return $this->likes_count + ($this->comments_count * 2) + ($this->shares_count * 3);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('posted_at', '>=', now()->subDays($days));
    }

    public function scopeHighlighted($query)
    {
        return $query->where('is_highlighted', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }
}

