<?php

namespace App\Services\Feeds;

use App\Contracts\FeedProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookFeed implements FeedProvider
{
    protected string $accessToken;
    protected string $apiUrl = 'https://graph.facebook.com/v18.0';

    public function __construct(?array $credentials = null)
    {
        // Priorité : credentials fournis > config globale
        if ($credentials && isset($credentials['access_token'])) {
            $this->accessToken = $credentials['access_token'];
        } else {
            $this->accessToken = config('feeds.credentials.facebook.access_token');
        }
    }
    
    /**
     * Définir des credentials personnalisés
     */
    public function setCredentials(array $credentials): self
    {
        $this->accessToken = $credentials['access_token'] ?? $this->accessToken;
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
        $pageId = $config['page_id'] ?? null;
        $hashtags = $config['hashtags'] ?? [];
        
        if (!$pageId) {
            return [];
        }

        try {
            return $this->fetchPagePosts($pageId, $hashtags);
        } catch (\Exception $e) {
            Log::error("Facebook fetch error: " . $e->getMessage());
            return [];
        }
    }

    protected function fetchPagePosts(string $pageId, array $hashtags = []): array
    {
        $response = Http::get("{$this->apiUrl}/{$pageId}/posts", [
            'fields' => 'id,message,created_time,permalink_url,full_picture,from,likes.summary(true),comments.summary(true),shares',
            'limit' => 50,
            'access_token' => $this->accessToken,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Facebook API error: " . $response->body());
        }

        $posts = $response->json()['data'] ?? [];
        
        // Filter by hashtags if specified
        if (!empty($hashtags)) {
            $posts = array_filter($posts, function($post) use ($hashtags) {
                $message = strtolower($post['message'] ?? '');
                foreach ($hashtags as $hashtag) {
                    $hashtag = strtolower(ltrim($hashtag, '#'));
                    if (strpos($message, "#{$hashtag}") !== false) {
                        return true;
                    }
                }
                return false;
            });
        }

        return array_map(fn($item) => $this->normalize($item), $posts);
    }

    public function normalize($data): array
    {
        $media = [];
        
        if (!empty($data['full_picture'])) {
            $media[] = [
                'type' => 'image',
                'url' => $data['full_picture'],
            ];
        }

        // Extract hashtags
        preg_match_all('/#(\w+)/', $data['message'] ?? '', $matches);
        $hashtags = $matches[1] ?? [];

        return [
            'external_id' => $data['id'],
            'platform' => 'facebook',
            'content' => $data['message'] ?? '',
            'author_name' => $data['from']['name'] ?? 'Unknown',
            'author_username' => null,
            'author_avatar' => null,
            'media' => $media,
            'likes_count' => $data['likes']['summary']['total_count'] ?? 0,
            'comments_count' => $data['comments']['summary']['total_count'] ?? 0,
            'shares_count' => $data['shares']['count'] ?? 0,
            'hashtags' => $hashtags,
            'url' => $data['permalink_url'] ?? null,
            'posted_at' => $data['created_time'],
        ];
    }

    public function validateConfig(array $config): bool
    {
        return isset($config['page_id']) && !empty($config['page_id']);
    }

    public function getPlatformName(): string
    {
        return 'Facebook';
    }
}

