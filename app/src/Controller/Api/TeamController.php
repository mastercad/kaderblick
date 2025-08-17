<?php

namespace App\Controller\Api;

use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    #[Route('/api/team/{id}/players', name: 'api_team_players', methods: ['GET'])]
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
