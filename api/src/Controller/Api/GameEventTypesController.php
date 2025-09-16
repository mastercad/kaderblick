<?php

namespace App\Controller\Api;

use App\Entity\GameEventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/game-event-types', name: 'api_game_event_types_')]
class GameEventTypesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('', methods: ['GET'], name: 'index')]
    public function index(): JsonResponse
    {
        $gameEventTypes = $this->entityManager->getRepository(GameEventType::class)->findAll();

        return $this->json([
            'gameEventTypes' => array_map(fn ($gameEventType) => [
                'id' => $gameEventType->getId(),
                'name' => $gameEventType->getName(),
                'code' => $gameEventType->getCode(),
                'color' => $gameEventType->getColor(),
                'icon' => $gameEventType->getIcon(),
                'isSystem' => $gameEventType->isSystem(),
                'permissions' => [
                    'canView' => $this->isGranted('GAME_EVENT_TYPE_VIEW', $gameEventType),
                    'canCreate' => $this->isGranted('GAME_EVENT_TYPE_CREATE', $gameEventType),
                    'canEdit' => $this->isGranted('GAME_EVENT_TYPE_EDIT', $gameEventType),
                    'canDelete' => $this->isGranted('GAME_EVENT_TYPE_DELETE', $gameEventType)
                ]
            ], $gameEventTypes)
        ]);
    }

    #[Route('/{id}', methods: ['GET'], name: 'show')]
    public function show(int $id): JsonResponse
    {
        $gameEventType = $this->entityManager->getRepository(GameEventType::class)->find($id);

        if (!$gameEventType) {
            return $this->json(['error' => 'GameEventType not found'], 404);
        }

        return $this->json([
            'gameEventType' => [
                'id' => $gameEventType->getId(),
                'name' => $gameEventType->getName(),
                'code' => $gameEventType->getCode(),
                'color' => $gameEventType->getColor(),
                'icon' => $gameEventType->getIcon(),
                'isSystem' => $gameEventType->isSystem(),
                'permissions' => [
                    'canView' => $this->isGranted('GAME_EVENT_TYPE_VIEW', $gameEventType),
                    'canCreate' => $this->isGranted('GAME_EVENT_TYPE_CREATE', $gameEventType),
                    'canEdit' => $this->isGranted('GAME_EVENT_TYPE_EDIT', $gameEventType),
                    'canDelete' => $this->isGranted('GAME_EVENT_TYPE_DELETE', $gameEventType)
                ]
            ]
        ]);
    }

    #[Route('/{id}', methods: ['PUT'], name: 'update')]
    public function update(Request $request, int $id): JsonResponse
    {
        $gameEventType = $this->entityManager->getRepository(GameEventType::class)->find($id);

        if (!$gameEventType) {
            return $this->json(['error' => 'GameEventType not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $gameEventType->setName($data['name']);
        $gameEventType->setColor($data['color']);
        $gameEventType->setIcon($data['icon']);
        $gameEventType->setCode($data['code']);
        $gameEventType->setSystem($data['isSystem'] ? true : false);

        $this->entityManager->flush();

        return $this->json(['success' => 'GameEventType updated']);
    }

    #[Route('', methods: ['POST'], name: 'create')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $gameEventType = new GameEventType();
        $gameEventType->setName($data['name']);
        $gameEventType->setColor($data['color']);
        $gameEventType->setIcon($data['icon']);
        $gameEventType->setCode($data['code']);
        $gameEventType->setSystem($data['isSystem'] ? true : false);

        $this->entityManager->persist($gameEventType);
        $this->entityManager->flush();

        return $this->json(['success' => 'GameEventType created', 'id' => $gameEventType->getId()]);
    }

    #[Route('/{id}', methods: ['DELETE'], name: 'delete')]
    public function delete(int $id): JsonResponse
    {
        $gameEventType = $this->entityManager->getRepository(GameEventType::class)->find($id);

        if (!$gameEventType) {
            return $this->json(['error' => 'GameEventType not found'], 404);
        }

        $this->entityManager->remove($gameEventType);
        $this->entityManager->flush();

        return $this->json(['success' => 'GameEventType deleted']);
    }
}
