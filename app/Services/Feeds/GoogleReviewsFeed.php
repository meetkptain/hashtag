<?php

namespace App\Services\Feeds;

use App\Contracts\FeedProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleReviewsFeed implements FeedProvider
{
    protected string $apiKey;
    protected string $apiUrl = 'https://maps.googleapis.com/maps/api';

    public function __construct()
    {
        $this->apiKey = config('feeds.credentials.google.api_key');
    }

    public function fetch(array $config): array
    {
        $placeId = $config['place_id'] ?? null;
        
        if (!$placeId) {
            return [];
        }

        try {
            return $this->fetchPlaceReviews($placeId);
        } catch (\Exception $e) {
            Log::error("Google Reviews fetch error: " . $e->getMessage());
            return [];
        }
    }

    protected function fetchPlaceReviews(string $placeId): array
    {
        $response = Http::get("{$this->apiUrl}/place/details/json", [
            'place_id' => $placeId,
            'fields' => 'name,rating,reviews',
            'key' => $this->apiKey,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Google API error: " . $response->body());
        }

        $data = $response->json();
        
        if ($data['status'] !== 'OK') {
            throw new \Exception("Google API status: " . $data['status']);
        }

        $reviews = $data['result']['reviews'] ?? [];
        
        // Filter only positive reviews (4+ stars)
        $reviews = array_filter($reviews, fn($review) => ($review['rating'] ?? 0) >= 4);

        return array_map(fn($item) => $this->normalize($item), $reviews);
    }

    public function normalize($data): array
    {
        $media = [];
        
        // Google reviews peuvent avoir des photos
        if (!empty($data['profile_photo_url'])) {
            $media[] = [
                'type' => 'image',
                'url' => $data['profile_photo_url'],
            ];
        }

        return [
            'external_id' => 'google_' . md5($data['author_name'] . $data['time']),
            'platform' => 'google',
            'content' => $data['text'] ?? '',
            'author_name' => $data['author_name'] ?? 'Anonymous',
            'author_username' => null,
            'author_avatar' => $data['profile_photo_url'] ?? null,
            'media' => $media,
            'likes_count' => 0,
            'comments_count' => 0,
            'shares_count' => 0,
            'rating' => $data['rating'] ?? null,
            'hashtags' => [],
            'url' => $data['author_url'] ?? null,
            'posted_at' => date('Y-m-d H:i:s', $data['time']),
        ];
    }

    public function validateConfig(array $config): bool
    {
        return isset($config['place_id']) && !empty($config['place_id']);
    }

    public function getPlatformName(): string
    {
        return 'Google Reviews';
    }
}

