<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feed Providers
    |--------------------------------------------------------------------------
    |
    | Liste des providers de flux disponibles. Chaque provider doit implémenter
    | l'interface FeedProvider pour garantir la compatibilité.
    |
    */
    'providers' => [
        'instagram' => \App\Services\Feeds\InstagramFeed::class,
        'facebook' => \App\Services\Feeds\FacebookFeed::class,
        'twitter' => \App\Services\Feeds\TwitterFeed::class,
        'google_reviews' => \App\Services\Feeds\GoogleReviewsFeed::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'ttl' => env('WIDGET_CACHE_TTL', 300), // 5 minutes
        'prefix' => 'feed_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'widget' => env('WIDGET_RATE_LIMIT', 100), // requêtes par minute
        'api' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Configuration
    |--------------------------------------------------------------------------
    */
    'widget' => [
        'max_posts' => env('WIDGET_MAX_POSTS', 50),
        'default_speed' => 'medium',
        'default_direction' => 'vertical',
        'default_theme' => 'light',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    */
    'credentials' => [
        'instagram' => [
            'app_id' => env('INSTAGRAM_APP_ID'),
            'app_secret' => env('INSTAGRAM_APP_SECRET'),
            'access_token' => env('INSTAGRAM_ACCESS_TOKEN'),
        ],
        'facebook' => [
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'access_token' => env('FACEBOOK_ACCESS_TOKEN'),
        ],
        'twitter' => [
            'api_key' => env('TWITTER_API_KEY'),
            'api_secret' => env('TWITTER_API_SECRET'),
            'bearer_token' => env('TWITTER_BEARER_TOKEN'),
        ],
        'google' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'api_key' => env('GOOGLE_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Gamification
    |--------------------------------------------------------------------------
    */
    'gamification' => [
        'badge_threshold' => 10, // posts pour débloquer un badge
        'highlight_hashtag' => true,
        'show_counter' => true,
        'animation_duration' => 500, // ms
    ],
];

