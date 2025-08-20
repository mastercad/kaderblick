<?php

namespace App\Controller\Api;

use App\Entity\Team;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/teams', name: 'api_teams_')]
class TeamsController extends ApiController
{
    protected string $entityName = 'Team';
    protected string $entityNamePlural = 'Teams';
    protected string $entityClass = Team::class;

    protected array $relations = [
        'ageGroup' => ['type' => 2, 'entityName' => 'AgeGroup'],
        'clubs' => ['type' => 4, 'entityName' => 'Club'],
        'league' => ['type' => 2, 'entityName' => 'League'],
        'playerTeamAssignments' => [
            'type' => 4,
            'entityName' => 'PlayerTeamAssignment',
            'label_fields' => ['player.firstName', 'player.lastName']
        ],
        'coachTeamAssignments' => [
            'type' => 4,
            'entityName' => 'CoachTeamAssignment',
            'label_fields' => ['coach.firstName', 'coach.lastName']
        ]
    ];
    protected string $urlPart = 'teams';
}
