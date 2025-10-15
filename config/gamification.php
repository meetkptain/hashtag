<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gamification Enabled
    |--------------------------------------------------------------------------
    |
    | Activer ou désactiver globalement le système de gamification
    |
    */

    'enabled' => env('GAMIFICATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Points Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration des points attribués pour chaque action
    |
    */

    'points' => [
        'per_post' => env('POINTS_PER_POST', 50),
        'like_bonus' => 10,
        'like_threshold' => 10, // Minimum likes pour bonus
        'first_post_day' => 30,
        'streak_7days' => 100,
        'contest_bonus' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    |
    | Limites pour éviter spam et abus
    |
    */

    'rate_limits' => [
        'max_posts_per_day' => env('MAX_POSTS_PER_DAY', 10),
        'max_posts_per_hour' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | TTL (Time To Live) pour les différents caches
    |
    */

    'cache' => [
        'leaderboard_ttl' => 60,        // 1 minute
        'config_ttl' => 3600,           // 1 heure
        'badges_ttl' => 86400,          // 24 heures
        'user_stats_ttl' => 300,        // 5 minutes
        'active_contests_ttl' => 300,   // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Leaderboard Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration des classements
    |
    */

    'leaderboard' => [
        'default_limit' => 100,
        'max_limit' => 1000,
        'cache_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Badges Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration des badges
    |
    */

    'badges' => [
        'show_secrets_in_progress' => false, // Cacher progression badges secrets
        'notification_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Contests Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration des concours
    |
    */

    'contests' => [
        'auto_start' => true,  // Démarre automatiquement à start_at
        'auto_draw' => false,  // Tirage automatique à end_at (false = manuel)
    ],

];

