<?php

namespace App\Event;

use App\Entity\User;

final class ProfileCompletenessReachedEvent
{
    public function __construct(
        private User $user,
        private int $milestone
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getMilestone(): int
    {
        return $this->milestone;
    }
}
