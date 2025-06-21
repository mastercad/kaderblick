<?php

namespace App\Controller;

use App\Entity\GameEventType;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/game-event-types', name: 'api_game_event_types_')]
class GameEventTypesController extends ApiController
{
    protected string $entityName = 'GameEventType';
    protected string $entityNamePlural = 'GameEventTypes';
    protected string $entityClass = GameEventType::class;
    protected string $urlPart = 'game-event-types';
    protected array $relations = [];
}
