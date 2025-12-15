<?php

namespace App\Event;

use App\Entity\Game;

final class GameCreatedEvent
{
    public function __construct(private readonly Game $game)
    {
    }

    public function getGame(): Game
    {
        return $this->game;
    }
}
