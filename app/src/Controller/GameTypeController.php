<?php

namespace App\Controller;

use App\Entity\GameType;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/game_types', name: 'api_game_types_')]
class GameTypeController extends ApiController
{
    protected string $entityName = 'GameType';
    protected string $entityNamePlural = 'GameTypes';
    protected string $entityClass = GameType::class;
    protected array $relations = [];
    protected array $relationEntries = [];
    protected string $urlPart = 'game_types';
}
