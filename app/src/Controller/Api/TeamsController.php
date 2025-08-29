<?php

namespace App\Controller\Api;

use App\Entity\Team;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/{id}/players', name: 'api_team_players', methods: ['GET'])]
    public function players(Team $team): JsonResponse
    {
        $players = $team->getCurrentPlayers();
        $result = [];
        foreach ($players as $player) {
            $shirtNumber = '';
            foreach ($player->getPlayerTeamAssignments() as $pta) {
                if ($pta->getTeam()->getId() === $team->getId()) {
                    $shirtNumber = $pta->getShirtNumber();
                    break;
                }
            }
            $result[] = [
                'id' => $player->getId(),
                'fullName' => $player->getFirstName() . ' ' . $player->getLastName(),
                'shirtNumber' => $shirtNumber,
            ];
        }

        return $this->json($result);
    }
}
