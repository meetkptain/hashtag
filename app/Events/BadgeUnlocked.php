<?php

namespace App\Events;

use App\Models\UserBadge;
use App\Models\UserPoint;
use App\Models\Badge;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeUnlocked
{
    use Dispatchable, SerializesModels;

    public UserBadge $userBadge;
    public UserPoint $userPoint;
    public Badge $badge;

    /**
     * Create a new event instance.
     */
    public function __construct(UserBadge $userBadge, UserPoint $userPoint, Badge $badge)
    {
        $this->userBadge = $userBadge;
        $this->userPoint = $userPoint;
        $this->badge = $badge;
    }
}

