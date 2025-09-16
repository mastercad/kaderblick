<?php

namespace App\Controller\Api;

use App\Entity\PlayerTeamAssignmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/player-team-assignment-types', name: 'api_player_team_assignment_types_')]
class PlayerTeamAssignmentTypesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('', methods: ['GET'], name: 'index')]
    public function index(): JsonResponse
    {
        $playerTeamAssignmentTypes = $this->entityManager->getRepository(PlayerTeamAssignmentType::class)->findAll();

        return $this->json(
            [
                'playerTeamAssignmentTypes' => array_map(fn ($playerTeamAssignmentType) => [
                    'id' => $playerTeamAssignmentType->getId(),
                    'name' => $playerTeamAssignmentType->getName(),
                    'description' => $playerTeamAssignmentType->getDescription(),
                ], $playerTeamAssignmentTypes)
            ]
        );
    }
}
