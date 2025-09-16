<?php

namespace App\Controller\Api;

use App\Entity\GameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/game-types', name: 'api_game_types_')]
class GameTypeController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'api_game_types_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $gameTypes = $this->em->getRepository(GameType::class)->findAll();

        return $this->json([
            'entries' => array_map(fn (GameType $gameType) => [
                'id' => $gameType->getId(),
                'name' => $gameType->getName(),
            ], $gameTypes),
        ]);
    }
}
