<?php

namespace App\Controller\Api;

use App\Entity\Game;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/games', name: 'api_games_')]
class GamesController extends ApiController
{
    protected string $entityName = 'Game';
    protected string $entityNamePlural = 'Games';
    protected string $entityClass = Game::class;
    protected array $relations = [
        'homeTeam' => [
            'entityName' => 'Team',
            'fieldName' => 'homeTeam',
            'methodName' => 'homeTeam',
            'type' => 2
        ],
        'awayTeam' => [
            'entityName' => 'Team',
            'fieldName' => 'awayTeam',
            'methodName' => 'awayTeam',
            'type' => 2
        ],
        'location' => [
            'entityName' => 'Location',
            'type' => 2,
        ],
        'gameType' => [
            'entityName' => 'GameType',
            'type' => 2
        ],
        'gameEvents' => [
            'entityName' => 'GameEvent',
            'type' => 4,
            'label_fields' => ['gameEventType.name']
        ],
        'substitutions' => [
            'entityName' => 'Substitution',
            'type' => 4
        ],
        'calendarEvents' => [
            'entityName' => 'CalendarEvent',
            'type' => 1,
        ]
    ];
    protected array $relationEntries = [];
    protected string $urlPart = 'games';
    protected bool $createAndEditAllowed = false;
}
