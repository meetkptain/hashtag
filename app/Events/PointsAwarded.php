<?php

namespace App\Events;

use App\Models\UserPoint;
use App\Models\Post;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PointsAwarded
{
    use Dispatchable, SerializesModels;

    public UserPoint $userPoint;
    public int $pointsAwarded;
    public Post $post;

    /**
     * Create a new event instance.
     */
    public function __construct(UserPoint $userPoint, int $pointsAwarded, Post $post)
    {
        $this->userPoint = $userPoint;
        $this->pointsAwarded = $pointsAwarded;
        $this->post = $post;
    }
}

