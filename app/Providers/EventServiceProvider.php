<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // ═══════════════════════════════════════════════════════════
        // GAMIFICATION EVENTS (v1.2.0)
        // ═══════════════════════════════════════════════════════════
        
        // Quand un post est créé → Attribuer points automatiquement
        \App\Events\PostCreated::class => [
            \App\Listeners\AwardPointsForPost::class,
        ],
        
        // Quand points attribués → Vérifier badges
        \App\Events\PointsAwarded::class => [
            \App\Listeners\CheckBadgeCriteria::class,
        ],
        
        // Quand badge débloqué → Notifications (à implémenter)
        \App\Events\BadgeUnlocked::class => [
            // \App\Listeners\SendBadgeNotification::class,
        ],
        
        // Quand user créé → Analytics (optionnel)
        \App\Events\UserPointCreated::class => [
            // \App\Listeners\TrackUserCreation::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

