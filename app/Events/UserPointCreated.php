<?php

namespace App\Events;

use App\Models\UserPoint;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPointCreated
{
    use Dispatchable, SerializesModels;

    public UserPoint $userPoint;

    /**
     * Create a new event instance.
     */
    public function __construct(UserPoint $userPoint)
    {
        $this->userPoint = $userPoint;
    }
}

