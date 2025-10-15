<?php

namespace App\Listeners;

use App\Events\PointsAwarded;
use App\Services\Gamification\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CheckBadgeCriteria implements ShouldQueue
{
    protected BadgeService $badgeService;

    /**
     * Create the event listener.
     */
    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Handle the event.
     */
    public function handle(PointsAwarded $event): void
    {
        try {
            $newBadges = $this->badgeService->checkBadgeCriteria($event->userPoint);

            if ($newBadges->isNotEmpty()) {
                Log::info("New badges unlocked", [
                    'user' => $event->userPoint->user_identifier,
                    'count' => $newBadges->count(),
                    'badges' => $newBadges->pluck('badge.key')->toArray()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to check badge criteria", [
                'user' => $event->userPoint->user_identifier,
                'error' => $e->getMessage()
            ]);
        }
    }
}

