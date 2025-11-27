<?php

namespace App\Event;

use App\Entity\Goal;
use App\Entity\User;

final class GoalScoredEvent
{
    public function __construct(private User $user, private Goal $goal)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getGoal(): Goal
    {
        return $this->goal;
    }
}
