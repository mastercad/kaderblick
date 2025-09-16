<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\UserRelation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TeamOutfitController extends AbstractController
{
    #[Route('/api/teams/outfit-overview', name: 'api_teams_outfit_overview', methods: ['GET'])]
    public function outfitOverview(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $result = [];
        foreach ($user->getUserRelations() as $relation) {
            if ($relation->getCoach()) {
                foreach ($relation->getCoach()->getCoachTeamAssignments() as $assignment) {
                    $players = [];
                    foreach ($assignment->getTeam()->getPlayerTeamAssignments() as $teamAssignment) {
                        $player = $teamAssignment->getPlayer();
                        foreach ($player->getUserRelations() as $playerRelation) {
                            /** @var UserRelation $playerRelation */
                            if ('self_player' !== $playerRelation->getRelationType()->getIdentifier()) {
                                continue 2;
                            }
                            $players[] = [
                                'id' => $player->getId(),
                                'name' => $player->getFullname(),
                                'shorts_size' => $playerRelation->getUser()->getPantsSize(),
                                'shirt_size' => $playerRelation->getUser()->getShirtSize(),
                                'shoe_size' => $playerRelation->getUser()->getShoeSize(),
                            ];
                        }
                    }
                    $result[] = [
                        'team_id' => $assignment->getTeam()->getId(),
                        'team_name' => $assignment->getTeam()->getName(),
                        'players' => $players,
                    ];
                }
            }
        }

        return $this->json($result);
    }
}
