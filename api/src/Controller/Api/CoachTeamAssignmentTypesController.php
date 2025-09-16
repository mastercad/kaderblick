<?php

namespace App\Controller\Api;

use App\Entity\CoachTeamAssignmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/coach-team-assignment-types', name: 'api_coach_team_assignment_types_')]
class CoachTeamAssignmentTypesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('', methods: ['GET'], name: 'index')]
    public function index(): JsonResponse
    {
        $coachTeamAssignmentTypes = $this->entityManager->getRepository(CoachTeamAssignmentType::class)->findAll();

        return $this->json(
            [
                'coachTeamAssignmentTypes' => array_map(fn ($coachTeamAssignmentType) => [
                    'id' => $coachTeamAssignmentType->getId(),
                    'name' => $coachTeamAssignmentType->getName(),
                    'description' => $coachTeamAssignmentType->getDescription(),
                ], $coachTeamAssignmentTypes)
            ]
        );
    }
}
