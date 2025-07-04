<?php

namespace App\Controller;

use App\Entity\Player;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/players', name: 'api_players_')]
class PlayersController extends ApiController
{
    protected string $entityName = 'Player';
    protected string $entityNamePlural = 'Players';
    protected string $entityClass = Player::class;
    protected string $urlPart = 'players';
    protected array $relations = [
        'strongFoot' => ['type' => 2, 'entityName' => 'StrongFoot'],
        'mainPosition' => ['type' => 2, 'entityName' => 'Position', 'fieldName' => 'MainPosition', 'methodName' => 'MainPosition'],
        'alternativePositions' => ['type' => 4, 'entityName' => 'Position', 'methodName' => 'AlternativePosition'],
        'playerTeamAssignments' => [
            'type' => 4,
            'entityName' => 'PlayerTeamAssignment',
            'labelFields' => [
                ['team.name']
            ]
        ],
        'playerClubAssignments' => [
            'type' => 4,
            'entityName' => 'PlayerClubAssignment',
            'labelFields' => [
                ['club.name']
            ]
        ],
        'playerNationalityAssignments' => ['type' => 4, 'entityName' => 'PlayerNationalityAssignment']
    ];
}
