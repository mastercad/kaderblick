<?php

namespace App\Event;

use App\Entity\GameEvent;
use App\Entity\User;

final class GoalAssistedEvent
{
    public function __construct(private User $user, private GameEvent $gameEvent)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getGameEvent(): GameEvent
    {
        return $this->gameEvent;
    }
}
