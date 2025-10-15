<?php

namespace App\Services\Feeds;

use App\Contracts\FeedProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramFeed implements FeedProvider
{
    protected string $accessToken;
    protected ?string $userId;
    protected string $apiUrl = 'https://graph.instagram.com/v18.0';

    public function __construct(?array $credentials = null)
    {
        // Priorité : credentials fournis > config globale
        if ($credentials && isset($credentials['access_token'])) {
            $this->accessToken = $credentials['access_token'];
            $this->userId = $credentials['user_id'] ?? config('feeds.credentials.instagram.user_id');
        } else {
            $this->accessToken = config('feeds.credentials.instagram.access_token');
            $this->userId = config('feeds.credentials.instagram.user_id');
        }
    }
    
    /**
     * Définir des credentials personnalisés
     */
    public function setCredentials(array $credentials): self
    {
        $this->accessToken = $credentials['access_token'] ?? $this->accessToken;
        $this->userId = $credentials['user_id'] ?? $this->userId;
        return $this;
    }
    
    /**
     * Obtenir le token actuel
     */
    public function getToken(): string
    {
        return $this->accessToken;
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
                Log::error("Instagram fetch error for #{$hashtag}: " . $e->getMessage());
            }
        }

        return $posts;
    }

    protected function fetchByHashtag(string $hashtag): array
    {
        $hashtag = ltrim($hashtag, '#');
        
        // Instagram Graph API - Hashtag Search
        $response = Http::get("{$this->apiUrl}/ig_hashtag_search", [
            'user_id' => config('feeds.credentials.instagram.user_id'),
            'q' => $hashtag,
            'access_token' => $this->accessToken,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Instagram API error: " . $response->body());
        }

        $data = $response->json();
        $hashtagId = $data['data'][0]['id'] ?? null;

        if (!$hashtagId) {
            return [];
        }

        // Get recent media
        $mediaResponse = Http::get("{$this->apiUrl}/{$hashtagId}/recent_media", [
            'user_id' => config('feeds.credentials.instagram.user_id'),
            'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username,like_count,comments_count',
            'access_token' => $this->accessToken,
        ]);

        if (!$mediaResponse->successful()) {
            return [];
        }

        $media = $mediaResponse->json()['data'] ?? [];
        
        return array_map(fn($item) => $this->normalize($item), $media);
    }

    public function normalize($data): array
    {
        $media = [];
        
        if ($data['media_type'] === 'IMAGE' || $data['media_type'] === 'CAROUSEL_ALBUM') {
            $media[] = [
                'type' => 'image',
                'url' => $data['media_url'],
            ];
        } elseif ($data['media_type'] === 'VIDEO') {
            $media[] = [
                'type' => 'video',
                'url' => $data['media_url'],
                'thumbnail' => $data['thumbnail_url'] ?? null,
            ];
        }

        // Extract hashtags from caption
        preg_match_all('/#(\w+)/', $data['caption'] ?? '', $matches);
        $hashtags = $matches[1] ?? [];

        return [
            'external_id' => $data['id'],
            'platform' => 'instagram',
            'content' => $data['caption'] ?? '',
            'author_name' => $data['username'],
            'author_username' => $data['username'],
            'author_avatar' => null, // Nécessite une requête supplémentaire
            'media' => $media,
            'likes_count' => $data['like_count'] ?? 0,
            'comments_count' => $data['comments_count'] ?? 0,
            'shares_count' => 0,
            'hashtags' => $hashtags,
            'url' => $data['permalink'],
            'posted_at' => $data['timestamp'],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return isset($config['hashtags']) && is_array($config['hashtags']) && !empty($config['hashtags']);
    }

    public function getPlatformName(): string
    {
        return 'Instagram';
    }
}

