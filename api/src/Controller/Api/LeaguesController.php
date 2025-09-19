<?php

namespace App\Controller\Api;

use App\Entity\League;
use App\Security\Voter\LeagueVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/leagues', name: 'api_leagues_')]
class LeaguesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('', methods: ['GET'], name: 'index')]
    public function index(): JsonResponse
    {
        $leagues = $this->entityManager->getRepository(League::class)->findAll();

        return $this->json(
            [
                'leagues' => array_map(fn ($league) => [
                    'id' => $league->getId(),
                    'name' => $league->getName(),
                    'permissions' => [
                        'canView' => $this->isGranted(LeagueVoter::VIEW, $league),
                        'canCreate' => $this->isGranted(LeagueVoter::CREATE, $league),
                        'canEdit' => $this->isGranted(LeagueVoter::EDIT, $league),
                        'canDelete' => $this->isGranted(LeagueVoter::DELETE, $league)
                    ]
                ], $leagues)
            ]
        );
    }

    #[Route('/{id}', methods: ['GET'], name: 'show')]
    public function show(League $league): JsonResponse
    {
        return $this->json(
            [
                'league' => [
                    'id' => $league->getId(),
                    'name' => $league->getName(),
                    'permissions' => [
                        'canView' => $this->isGranted(LeagueVoter::VIEW, $league),
                        'canCreate' => $this->isGranted(LeagueVoter::CREATE, $league),
                        'canEdit' => $this->isGranted(LeagueVoter::EDIT, $league),
                        'canDelete' => $this->isGranted(LeagueVoter::DELETE, $league)
                    ]
                ]
            ]
        );
    }

    #[Route('', methods: ['POST'], name: 'create')]
    public function create(Request $request): JsonResponse
    {
        $league = new League();

        if (!$this->isGranted(LeagueVoter::CREATE, $league)) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $leagueData = json_decode($request->getContent(), true);
        $league->setName($leagueData['name']);

        $this->entityManager->persist($league);
        $this->entityManager->flush();

        return $this->json(['message' => 'League created successfully'], 201);
    }

    #[Route('/{id}', methods: ['PUT'], name: 'update')]
    public function update(League $league, Request $request): JsonResponse
    {
        if (!$this->isGranted(LeagueVoter::EDIT, $league)) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $leagueData = json_decode($request->getContent(), true);
        $league->setName($leagueData['name']);

        $this->entityManager->flush();

        return $this->json(['message' => 'League updated successfully']);
    }

    #[Route('/{id}', methods: ['DELETE'], name: 'delete')]
    public function delete(League $league): JsonResponse
    {
        $this->entityManager->remove($league);
        $this->entityManager->flush();

        return $this->json(['message' => 'League deleted successfully']);
    }
}
