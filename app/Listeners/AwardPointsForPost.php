<?php

namespace App\Listeners;

use App\Events\PostCreated;
use App\Services\Gamification\PointsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class AwardPointsForPost implements ShouldQueue
{
    protected PointsService $pointsService;

    /**
     * Create the event listener.
     */
    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * Handle the event.
     */
    public function handle(PostCreated $event): void
    {
        try {
            $pointsAwarded = $this->pointsService->awardPointsForPost($event->post);
            
            Log::info("Points awarded for post", [
                'post_id' => $event->post->id,
                'user' => $event->post->author_username,
                'points' => $pointsAwarded
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to award points for post", [
                'post_id' => $event->post->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

