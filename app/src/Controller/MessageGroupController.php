<?php

namespace App\Controller;

use App\Entity\MessageGroup;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/message-groups')]
class MessageGroupController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'api_message_groups_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $groups = $this->entityManager->getRepository(MessageGroup::class)
            ->findBy(['owner' => $this->getUser()]);

        return $this->json([
            'groups' => array_map(fn (MessageGroup $group) => [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'memberCount' => $group->getMembers()->count(),
            ], $groups),
        ]);
    }

    #[Route('', name: 'api_message_groups_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $group = new MessageGroup();
        $group->setName($data['name']);
        $group->setOwner($this->getUser());

        if (!empty($data['memberIds'])) {
            $members = $this->entityManager->getRepository(User::class)
                ->findBy(['id' => $data['memberIds']]);
            foreach ($members as $member) {
                $group->addMember($member);
            }
        }

        $this->entityManager->persist($group);
        $this->entityManager->flush();

        return $this->json(['message' => 'Gruppe erstellt']);
    }

    #[Route('/{id}', name: 'api_message_groups_update', methods: ['PUT'])]
    public function update(MessageGroup $group, Request $request): JsonResponse
    {
        if ($group->getOwner() !== $this->getUser()) {
            return $this->json(['message' => 'Nicht berechtigt'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $group->setName($data['name']);
        }

        if (isset($data['memberIds'])) {
            $group->getMembers()->clear();
            $members = $this->entityManager->getRepository(User::class)
                ->findBy(['id' => $data['memberIds']]);
            foreach ($members as $member) {
                $group->addMember($member);
            }
        }

        $this->entityManager->flush();

        return $this->json(['message' => 'Gruppe aktualisiert']);
    }

    #[Route('/{id}', name: 'api_message_groups_delete', methods: ['DELETE'])]
    public function delete(MessageGroup $group): JsonResponse
    {
        if ($group->getOwner() !== $this->getUser()) {
            return $this->json(['message' => 'Nicht berechtigt'], 403);
        }

        $this->entityManager->remove($group);
        $this->entityManager->flush();

        return $this->json(['message' => 'Gruppe gelöscht']);
    }
}
