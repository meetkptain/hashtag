<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FeedService
{
    /**
     * Synchroniser tous les feeds actifs
     */
    public function syncAllFeeds(): void
    {
        $feeds = Feed::where('active', true)->get();

        foreach ($feeds as $feed) {
            if ($feed->needsRefresh()) {
                $this->syncFeed($feed);
            }
        }
    }

    /**
     * Synchroniser un feed spécifique
     */
    public function syncFeed(Feed $feed): int
    {
        try {
            // Passer les credentials du feed au provider (mode hybride)
            $provider = $this->getProvider($feed->type, $feed->credentials);
            
            if (!$provider) {
                Log::error("Provider not found for feed type: {$feed->type}");
                return 0;
            }

            // Fetch posts from provider
            $posts = $provider->fetch($feed->config);
            $newPostsCount = 0;

            foreach ($posts as $postData) {
                $created = $this->createOrUpdatePost($feed, $postData);
                if ($created) {
                    $newPostsCount++;
                }
            }

            // Update feed metadata
            $feed->update([
                'last_fetched_at' => now(),
                'posts_count' => $feed->posts()->count(),
            ]);

            // Clear cache
            $this->clearFeedCache($feed);

            Log::info("Feed {$feed->id} synced: {$newPostsCount} new posts");

            return $newPostsCount;

        } catch (\Exception $e) {
            Log::error("Error syncing feed {$feed->id}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Créer ou mettre à jour un post
     */
    protected function createOrUpdatePost(Feed $feed, array $data): bool
    {
        $existing = Post::where('external_id', $data['external_id'])->first();

        if ($existing) {
            // Mettre à jour les métriques
            $existing->update([
                'likes_count' => $data['likes_count'],
                'comments_count' => $data['comments_count'],
                'shares_count' => $data['shares_count'],
            ]);
            return false;
        }

        // Créer un nouveau post
        Post::create([
            'feed_id' => $feed->id,
            ...$data,
            'is_new' => true,
            'is_highlighted' => $this->shouldHighlight($data, $feed),
        ]);

        return true;
    }

    /**
     * Déterminer si un post doit être mis en surbrillance
     */
    protected function shouldHighlight(array $postData, Feed $feed): bool
    {
        $feedHashtags = array_map('strtolower', $feed->getHashtags());
        $postHashtags = array_map('strtolower', $postData['hashtags'] ?? []);

        return !empty(array_intersect($feedHashtags, $postHashtags));
    }

    /**
     * Obtenir le provider pour un type de feed
     */
    protected function getProvider(string $type, ?array $credentials = null): ?\App\Contracts\FeedProvider
    {
        $providers = config('feeds.providers');
        $providerClass = $providers[$type] ?? null;

        if (!$providerClass || !class_exists($providerClass)) {
            return null;
        }

        // Instancier avec credentials si fournis (mode avancé)
        if ($credentials) {
            return new $providerClass($credentials);
        }

        return app($providerClass);
    }
    
    /**
     * Obtenir le type de connexion d'un feed
     */
    public function getConnectionType(Feed $feed): string
    {
        return !empty($feed->credentials) ? 'advanced' : 'simple';
    }

    /**
     * Vérifier si un feed utilise des credentials personnalisés
     */
    public function hasCustomCredentials(Feed $feed): bool
    {
        return !empty($feed->credentials) && isset($feed->credentials['access_token']);
    }

    /**
     * Obtenir les posts pour le widget
     */
    public function getWidgetPosts(int $limit = 50, array $filters = []): array
    {
        $cacheKey = 'widget_posts_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, config('feeds.cache.ttl'), function () use ($limit, $filters) {
            $query = Post::with('feed')
                ->orderBy('posted_at', 'desc')
                ->limit($limit);

            // Apply filters
            if (!empty($filters['platform'])) {
                $query->where('platform', $filters['platform']);
            }

            if (!empty($filters['hashtags'])) {
                $query->where(function ($q) use ($filters) {
                    foreach ($filters['hashtags'] as $hashtag) {
                        $q->orWhereJsonContains('hashtags', $hashtag);
                    }
                });
            }

            if (isset($filters['highlighted']) && $filters['highlighted']) {
                $query->where('is_highlighted', true);
            }

            if (isset($filters['new']) && $filters['new']) {
                $query->where('is_new', true);
            }

            return $query->get()->map(function ($post) {
                return [
                    'id' => $post->id,
                    'platform' => $post->platform,
                    'content' => $post->content,
                    'author' => [
                        'name' => $post->author_name,
                        'username' => $post->author_username,
                        'avatar' => $post->author_avatar,
                    ],
                    'media' => $post->media,
                    'stats' => [
                        'likes' => $post->likes_count,
                        'comments' => $post->comments_count,
                        'shares' => $post->shares_count,
                    ],
                    'rating' => $post->rating,
                    'hashtags' => $post->hashtags,
                    'url' => $post->url,
                    'posted_at' => $post->posted_at->toIso8601String(),
                    'is_new' => $post->is_new,
                    'is_highlighted' => $post->is_highlighted,
                    'engagement_score' => $post->getEngagementScore(),
                ];
            })->toArray();
        });
    }

    /**
     * Effacer le cache d'un feed
     */
    protected function clearFeedCache(Feed $feed): void
    {
        Cache::tags(['feed_' . $feed->id])->flush();
        Cache::forget('widget_posts_*');
    }

    /**
     * Obtenir les statistiques d'un feed
     */
    public function getFeedStats(Feed $feed): array
    {
        return [
            'total_posts' => $feed->posts()->count(),
            'new_posts' => $feed->posts()->where('is_new', true)->count(),
            'highlighted_posts' => $feed->posts()->where('is_highlighted', true)->count(),
            'total_engagement' => $feed->posts()->sum(\DB::raw('likes_count + comments_count + shares_count')),
            'average_rating' => $feed->posts()->whereNotNull('rating')->avg('rating'),
            'last_post_at' => $feed->posts()->max('posted_at'),
        ];
    }
}

