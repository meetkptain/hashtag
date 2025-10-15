<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            // ═══════════════════════════════════════════════════════════
            // PROGRESSION BADGES (volume de posts)
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'beginner',
                'name' => '🥉 Débutant',
                'description' => 'Postez votre premier post avec notre hashtag',
                'category' => 'progression',
                'rarity' => 'common',
                'criteria' => ['type' => 'posts_count', 'min_posts' => 1],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L9.19 8.63L2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24l-7.19-.61L12 2z"/></svg>',
                'active' => true,
                'display_order' => 1
            ],
            [
                'key' => 'active',
                'name' => '🌟 Actif',
                'description' => 'Postez 5 fois avec notre hashtag',
                'category' => 'progression',
                'rarity' => 'common',
                'criteria' => ['type' => 'posts_count', 'min_posts' => 5],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>',
                'active' => true,
                'display_order' => 2
            ],
            [
                'key' => 'contributor',
                'name' => '🥈 Contributeur',
                'description' => 'Postez 10 fois avec notre hashtag',
                'category' => 'progression',
                'rarity' => 'common',
                'criteria' => ['type' => 'posts_count', 'min_posts' => 10],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>',
                'active' => true,
                'display_order' => 3
            ],
            [
                'key' => 'regular',
                'name' => '⭐ Régulier',
                'description' => 'Postez 25 fois avec notre hashtag',
                'category' => 'progression',
                'rarity' => 'rare',
                'criteria' => ['type' => 'posts_count', 'min_posts' => 25],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
                'active' => true,
                'display_order' => 4
            ],
            [
                'key' => 'expert',
                'name' => '🥇 Expert',
                'description' => 'Postez 50 fois avec notre hashtag',
                'category' => 'progression',
                'rarity' => 'rare',
                'criteria' => ['type' => 'posts_count', 'min_posts' => 50],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4h7.6l-6 4.6 2.3 7.4L12 16.8l-6.3 4.6 2.3-7.4-6-4.6h7.6z"/></svg>',
                'active' => true,
                'display_order' => 5
            ],
            [
                'key' => 'legend',
                'name' => '💎 Légende',
                'description' => 'Postez 200 fois avec notre hashtag',
                'category' => 'progression',
                'rarity' => 'epic',
                'criteria' => ['type' => 'posts_count', 'min_posts' => 200],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>',
                'active' => true,
                'display_order' => 6
            ],
            [
                'key' => 'master',
                'name' => '👑 Maître',
                'description' => 'Postez 500 fois avec notre hashtag',
                'category' => 'progression',
                'rarity' => 'legendary',
                'criteria' => ['type' => 'posts_count', 'min_posts' => 500],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>',
                'active' => true,
                'display_order' => 7
            ],

            // ═══════════════════════════════════════════════════════════
            // SOCIAL BADGES (engagement/popularité)
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'star_rising',
                'name' => '⭐ Star Rising',
                'description' => 'Obtenez 50+ likes sur un post',
                'category' => 'social',
                'rarity' => 'rare',
                'criteria' => ['type' => 'post_likes', 'min_likes' => 50, 'min_posts' => 1],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>',
                'active' => true,
                'display_order' => 10
            ],
            [
                'key' => 'influencer',
                'name' => '🌟 Influenceur',
                'description' => 'Obtenez 100+ likes sur 5 posts',
                'category' => 'social',
                'rarity' => 'epic',
                'criteria' => ['type' => 'post_likes', 'min_likes' => 100, 'min_posts' => 5],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>',
                'active' => true,
                'display_order' => 11
            ],
            [
                'key' => 'celebrity',
                'name' => '💫 Célébrité',
                'description' => 'Obtenez 500+ likes sur un post',
                'category' => 'social',
                'rarity' => 'legendary',
                'criteria' => ['type' => 'post_likes', 'min_likes' => 500, 'min_posts' => 1],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>',
                'active' => true,
                'display_order' => 12
            ],

            // ═══════════════════════════════════════════════════════════
            // CHALLENGE BADGES (objectifs spécifiques)
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'streak_7',
                'name' => '🔥 Streak 7',
                'description' => 'Postez 7 jours consécutifs',
                'category' => 'challenge',
                'rarity' => 'rare',
                'criteria' => ['type' => 'streak', 'min_days' => 7],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM11.71 19c-1.78 0-3.22-1.4-3.22-3.14 0-1.62 1.05-2.76 2.81-3.12 1.77-.36 3.6-1.21 4.62-2.58.39 1.29.59 2.65.59 4.04 0 2.65-2.15 4.8-4.8 4.8z"/></svg>',
                'active' => true,
                'display_order' => 20
            ],
            [
                'key' => 'streak_30',
                'name' => '🔥 Streak Master',
                'description' => 'Postez 30 jours consécutifs',
                'category' => 'challenge',
                'rarity' => 'epic',
                'criteria' => ['type' => 'streak', 'min_days' => 30],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.66 11.2C17.43 10.9 17.15 10.64 16.89 10.38C16.22 9.78 15.46 9.35 14.82 8.72C13.33 7.26 13 4.85 13.95 3C13 3.23 12.17 3.75 11.46 4.32C8.87 6.4 7.85 10.07 9.07 13.22C9.11 13.32 9.15 13.42 9.15 13.55C9.15 13.77 9 13.97 8.8 14.05C8.57 14.15 8.33 14.09 8.14 13.93C8.08 13.88 8.04 13.83 8 13.76C6.87 12.33 6.69 10.28 7.45 8.64C5.78 10 4.87 12.3 5 14.47C5.06 14.97 5.12 15.47 5.29 15.97C5.43 16.57 5.7 17.17 6 17.7C7.08 19.43 8.95 20.67 10.96 20.92C13.1 21.19 15.39 20.8 17.03 19.32C18.86 17.66 19.5 15 18.56 12.72L18.43 12.46C18.22 12 17.66 11.2 17.66 11.2Z"/></svg>',
                'active' => true,
                'display_order' => 21
            ],
            [
                'key' => 'speed_demon',
                'name' => '⚡ Speed Demon',
                'description' => 'Postez 10 fois en 1 heure',
                'category' => 'challenge',
                'rarity' => 'rare',
                'criteria' => ['type' => 'posts_speed', 'min_posts' => 10, 'timeframe_hours' => 1],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L13.09 8.26L19 9.27L14 14.14L15.18 20.02L12 17.77L8.82 20.02L10 14.14L5 9.27L10.91 8.26L12 2Z"/></svg>',
                'active' => true,
                'display_order' => 22
            ],
            [
                'key' => 'night_owl',
                'name' => '🌙 Night Owl',
                'description' => 'Postez entre minuit et 4h du matin',
                'category' => 'challenge',
                'rarity' => 'rare',
                'criteria' => ['type' => 'post_time', 'target_time' => '00:00'],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M2 12h3v9H2v-9zm16-6h3v15h-3V6zM9 1h3v20H9V1zm7 7h3v13h-3V8z"/></svg>',
                'active' => true,
                'display_order' => 23
            ],
            [
                'key' => 'early_bird',
                'name' => '☀️ Early Bird',
                'description' => 'Postez entre 5h et 7h du matin',
                'category' => 'challenge',
                'rarity' => 'rare',
                'criteria' => ['type' => 'post_time', 'target_time' => '06:00'],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.76 4.84l-1.8-1.79-1.41 1.41 1.79 1.79 1.42-1.41zM4 10.5H1v2h3v-2zm9-9.95h-2V3.5h2V.55zm7.45 3.91l-1.41-1.41-1.79 1.79 1.41 1.41 1.79-1.79zm-3.21 13.7l1.79 1.8 1.41-1.41-1.8-1.79-1.4 1.4zM20 10.5v2h3v-2h-3zm-8-5c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm-1 16.95h2V19.5h-2v2.95zm-7.45-3.91l1.41 1.41 1.79-1.8-1.41-1.41-1.79 1.8z"/></svg>',
                'active' => true,
                'display_order' => 24
            ],

            // ═══════════════════════════════════════════════════════════
            // EXCLUSIVE BADGES (top performers)
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'champion',
                'name' => '👑 Champion',
                'description' => 'Soyez #1 au classement mensuel',
                'category' => 'exclusive',
                'rarity' => 'legendary',
                'criteria' => ['type' => 'leaderboard', 'rank' => 1, 'period' => 'monthly'],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>',
                'active' => true,
                'display_order' => 30
            ],
            [
                'key' => 'podium',
                'name' => '🏆 Podium',
                'description' => 'Soyez dans le top 3 mensuel',
                'category' => 'exclusive',
                'rarity' => 'epic',
                'criteria' => ['type' => 'leaderboard', 'rank' => 3, 'period' => 'monthly'],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 8h-6V4H10v4H4v12h16V8m-4 10h-2v-6h2v6m-4 0h-2V12h2v6m-4 0H6v-4h2v4z"/></svg>',
                'active' => true,
                'display_order' => 31
            ],
            [
                'key' => 'top10',
                'name' => '💪 Top 10',
                'description' => 'Soyez dans le top 10 mensuel',
                'category' => 'exclusive',
                'rarity' => 'rare',
                'criteria' => ['type' => 'leaderboard', 'rank' => 10, 'period' => 'monthly'],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 7a8 8 0 1 1 0 16 8 8 0 0 1 0-16m0-5v2a10 10 0 1 0 0 20v2c6.63 0 12-5.37 12-12S18.63 2 12 2Z"/></svg>',
                'active' => true,
                'display_order' => 32
            ],

            // ═══════════════════════════════════════════════════════════
            // SECRET BADGES (découverte)
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'lucky_number',
                'name' => '🎰 Lucky Number',
                'description' => '???',
                'category' => 'secret',
                'rarity' => 'legendary',
                'criteria' => ['type' => 'post_number', 'target_number' => 7777],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM7.5 18c-.83 0-1.5-.67-1.5-1.5S6.67 15 7.5 15s1.5.67 1.5 1.5S8.33 18 7.5 18zm0-9C6.67 9 6 8.33 6 7.5S6.67 6 7.5 6 9 6.67 9 7.5 8.33 9 7.5 9zm4.5 4.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5 4.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm0-9c-.83 0-1.5-.67-1.5-1.5S15.67 6 16.5 6s1.5.67 1.5 1.5S17.33 9 16.5 9z"/></svg>',
                'active' => true,
                'display_order' => 40
            ],
            [
                'key' => 'unicorn',
                'name' => '🦄 Unicorn',
                'description' => '???',
                'category' => 'secret',
                'rarity' => 'legendary',
                'criteria' => ['type' => 'post_time', 'target_time' => '11:11'],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>',
                'active' => true,
                'display_order' => 41
            ],

            // ═══════════════════════════════════════════════════════════
            // EVENT BADGES (temporaires - à activer manuellement)
            // ═══════════════════════════════════════════════════════════
            [
                'key' => 'halloween_2025',
                'name' => '🎃 Halloween 2025',
                'description' => 'Postez pendant Halloween 2025',
                'category' => 'event',
                'rarity' => 'epic',
                'criteria' => [
                    'type' => 'posts_count', 
                    'min_posts' => 1,
                    'date_range' => ['start' => '2025-10-20', 'end' => '2025-10-31']
                ],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2m-2 15l-5-5 1.4-1.4L10 14.2l7.6-7.6L19 8l-9 9Z"/></svg>',
                'active' => false, // À activer manuellement
                'display_order' => 50
            ],
            [
                'key' => 'christmas_2025',
                'name' => '🎅 Noël 2025',
                'description' => 'Postez pendant Noël 2025',
                'category' => 'event',
                'rarity' => 'epic',
                'criteria' => [
                    'type' => 'posts_count', 
                    'min_posts' => 1,
                    'date_range' => ['start' => '2025-12-20', 'end' => '2025-12-31']
                ],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>',
                'active' => false, // À activer manuellement
                'display_order' => 51
            ],
            [
                'key' => 'new_year_2026',
                'name' => '🎉 Nouvel An 2026',
                'description' => 'Postez le jour de l\'an 2026',
                'category' => 'event',
                'rarity' => 'rare',
                'criteria' => [
                    'type' => 'posts_count', 
                    'min_posts' => 1,
                    'date_range' => ['start' => '2026-01-01', 'end' => '2026-01-01']
                ],
                'icon_svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M15 4V3H9v1H4v2h1v13c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V6h1V4h-5z"/></svg>',
                'active' => false,
                'display_order' => 52
            ],
        ];

        foreach ($badges as $badgeData) {
            Badge::updateOrCreate(
                ['key' => $badgeData['key']],
                $badgeData
            );
        }

        $this->command->info('✅ ' . count($badges) . ' badges créés/mis à jour');
    }
}

