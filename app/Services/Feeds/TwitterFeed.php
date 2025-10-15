<?php

namespace App\Services\Feeds;

use App\Contracts\FeedProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterFeed implements FeedProvider
{
    protected string $bearerToken;
    protected string $apiUrl = 'https://api.twitter.com/2';

    public function __construct(?array $credentials = null)
    {
        // Priorité : credentials fournis > config globale
        if ($credentials && isset($credentials['bearer_token'])) {
            $this->bearerToken = $credentials['bearer_token'];
        } else {
            $this->bearerToken = config('feeds.credentials.twitter.bearer_token');
        }
    }
    
    /**
     * Définir des credentials personnalisés
     */
    public function setCredentials(array $credentials): self
    {
        $this->bearerToken = $credentials['bearer_token'] ?? $this->bearerToken;
        return $this;
    }
    
    /**
     * Obtenir le token actuel
     */
    public function getToken(): string
    {
        return $this->bearerToken;
    }

    public function fetch(array $config): array
    {
        $hashtags = $config['hashtags'] ?? [];
        $posts = [];

        foreach ($hashtags as $hashtag) {
            try {
                $response = $this->fetchByHashtag($hashtag);
                $posts = array_merge($posts, $response);
            } catch (\Exception $e) {
                Log::error("Twitter fetch error for #{$hashtag}: " . $e->getMessage());
            }
        }

        return $posts;
    }

    protected function fetchByHashtag(string $hashtag): array
    {
        $hashtag = ltrim($hashtag, '#');
        
        // Twitter API v2 - Recent Search
        $response = Http::withToken($this->bearerToken)
            ->get("{$this->apiUrl}/tweets/search/recent", [
                'query' => "#{$hashtag} -is:retweet",
                'max_results' => 50,
                'tweet.fields' => 'created_at,public_metrics,entities,author_id',
                'expansions' => 'author_id,attachments.media_keys',
                'media.fields' => 'url,preview_image_url,type',
                'user.fields' => 'name,username,profile_image_url',
            ]);

        if (!$response->successful()) {
            throw new \Exception("Twitter API error: " . $response->body());
        }

        $data = $response->json();
        $tweets = $data['data'] ?? [];
        $includes = $data['includes'] ?? [];
        
        // Map users and media
        $users = collect($includes['users'] ?? [])->keyBy('id');
        $media = collect($includes['media'] ?? [])->keyBy('media_key');

        return array_map(function($tweet) use ($users, $media) {
            $tweet['author'] = $users[$tweet['author_id']] ?? null;
            $tweet['media'] = [];
            
            if (!empty($tweet['attachments']['media_keys'])) {
                foreach ($tweet['attachments']['media_keys'] as $mediaKey) {
                    if ($media->has($mediaKey)) {
                        $tweet['media'][] = $media[$mediaKey];
                    }
                }
            }
            
            return $this->normalize($tweet);
        }, $tweets);
    }

    public function normalize($data): array
    {
        $media = [];
        
        foreach ($data['media'] ?? [] as $mediaItem) {
            if ($mediaItem['type'] === 'photo') {
                $media[] = [
                    'type' => 'image',
                    'url' => $mediaItem['url'],
                ];
            } elseif ($mediaItem['type'] === 'video' || $mediaItem['type'] === 'animated_gif') {
                $media[] = [
                    'type' => 'video',
                    'url' => $mediaItem['url'] ?? null,
                    'thumbnail' => $mediaItem['preview_image_url'] ?? null,
                ];
            }
        }

        // Extract hashtags
        $hashtags = array_map(
            fn($tag) => $tag['tag'], 
            $data['entities']['hashtags'] ?? []
        );

        $author = $data['author'] ?? [];
        $metrics = $data['public_metrics'] ?? [];

        return [
            'external_id' => $data['id'],
            'platform' => 'twitter',
            'content' => $data['text'] ?? '',
            'author_name' => $author['name'] ?? 'Unknown',
            'author_username' => $author['username'] ?? null,
            'author_avatar' => $author['profile_image_url'] ?? null,
            'media' => $media,
            'likes_count' => $metrics['like_count'] ?? 0,
            'comments_count' => $metrics['reply_count'] ?? 0,
            'shares_count' => $metrics['retweet_count'] ?? 0,
            'hashtags' => $hashtags,
            'url' => "https://twitter.com/{$author['username']}/status/{$data['id']}",
            'posted_at' => $data['created_at'],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return isset($config['hashtags']) && is_array($config['hashtags']) && !empty($config['hashtags']);
    }

    public function getPlatformName(): string
    {
        return 'Twitter';
    }
}

