<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Configuration des plans d'abonnement Stripe
    |
    */
    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'price_monthly' => 29,
            'price_yearly' => 290,
            'stripe_price_monthly' => env('STRIPE_PRICE_STARTER_MONTHLY'),
            'stripe_price_yearly' => env('STRIPE_PRICE_STARTER_YEARLY'),
            'features' => [
                'feeds' => 1,
                'hashtags' => 3,
                'posts_limit' => 50,
                'gamification' => false,
                'custom_branding' => false,
                'analytics' => 'basic',
                'support' => 'email',
            ],
            'description' => 'Parfait pour commencer',
        ],
        
        'business' => [
            'name' => 'Business',
            'price_monthly' => 79,
            'price_yearly' => 790,
            'stripe_price_monthly' => env('STRIPE_PRICE_BUSINESS_MONTHLY'),
            'stripe_price_yearly' => env('STRIPE_PRICE_BUSINESS_YEARLY'),
            'features' => [
                'feeds' => 3,
                'hashtags' => 10,
                'posts_limit' => 200,
                'gamification' => true,
                'custom_branding' => true,
                'analytics' => 'advanced',
                'support' => 'priority',
            ],
            'description' => 'Pour les entreprises en croissance',
            'popular' => true,
        ],
        
        'enterprise' => [
            'name' => 'Enterprise',
            'price_monthly' => 199,
            'price_yearly' => 1990,
            'stripe_price_monthly' => env('STRIPE_PRICE_ENTERPRISE_MONTHLY'),
            'stripe_price_yearly' => env('STRIPE_PRICE_ENTERPRISE_YEARLY'),
            'features' => [
                'feeds' => -1, // illimité
                'hashtags' => -1, // illimité
                'posts_limit' => -1, // illimité
                'gamification' => true,
                'custom_branding' => true,
                'analytics' => 'premium',
                'support' => 'dedicated',
                'api_access' => true,
                'white_label' => true,
            ],
            'description' => 'Solution complète pour grandes organisations',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Add-ons
    |--------------------------------------------------------------------------
    */
    'addons' => [
        'extra_feed' => [
            'name' => 'Flux supplémentaire',
            'price' => 15,
            'stripe_price' => env('STRIPE_PRICE_EXTRA_FEED'),
        ],
        'extra_hashtags' => [
            'name' => '5 hashtags supplémentaires',
            'price' => 10,
            'stripe_price' => env('STRIPE_PRICE_EXTRA_HASHTAGS'),
        ],
        'instagram_connection' => [
            'name' => 'Connexion compte Instagram (Mode Avancé)',
            'price' => 20,
            'stripe_price' => env('STRIPE_PRICE_INSTAGRAM_CONNECTION'),
            'description' => 'Connectez votre compte Instagram pour un accès complet',
            'features' => [
                'Tous vos posts (privés inclus)',
                'Stories, mentions, tags',
                'Limites API dédiées (200/h)',
                'Aucune limite partagée',
            ]
        ],
        'facebook_connection' => [
            'name' => 'Connexion compte Facebook (Mode Avancé)',
            'price' => 20,
            'stripe_price' => env('STRIPE_PRICE_FACEBOOK_CONNECTION'),
            'description' => 'Connectez votre compte Facebook pour un accès complet',
        ],
        'twitter_connection' => [
            'name' => 'Connexion compte Twitter (Mode Avancé)',
            'price' => 20,
            'stripe_price' => env('STRIPE_PRICE_TWITTER_CONNECTION'),
            'description' => 'Connectez votre compte Twitter pour un accès complet',
        ],
        'premium_support' => [
            'name' => 'Support prioritaire',
            'price' => 50,
            'stripe_price' => env('STRIPE_PRICE_PREMIUM_SUPPORT'),
        ],
    ],
];

