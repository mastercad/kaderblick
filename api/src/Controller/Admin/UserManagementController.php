<?php

namespace App\Controller\Admin;

use App\Entity\Club;
use App\Entity\Coach;
use App\Entity\Permission;
use App\Entity\Player;
use App\Entity\RelationType;
use App\Entity\User;
use App\Entity\UserRelation;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users', name: 'admin_users_')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $users = $this->em->getRepository(User::class)->findBy([], ['lastName' => 'ASC', 'firstName' => 'ASC']);

        return $this->json(
            [
                'users' => array_map(
                    fn (User $user) => [
                        'id' => $user->getId(),
                        'fullName' => $user->getFullName(),
                        'email' => $user->getEmail(),
                        'roles' => $user->getRoles(),
                        'isVerified' => $user->isVerified(),
                        'isEnabled' => $user->isEnabled(),
                        'userRelations' => array_map(fn (UserRelation $relation) => [
                            'id' => $relation->getId(),
                            'type' => $relation->getRelationType()->getName(),
                            'entity' => $relation->getPlayer() ? $relation->getPlayer()->getFullName() : ($relation->getCoach() ? $relation->getCoach()->getFullName() : null),
                            'permissions' => $relation->getPermissions(),
                            'relationType' => [
                                'id' => $relation->getRelationType()->getId(),
                                'name' => $relation->getRelationType()->getName(),
                                'identifier' => $relation->getRelationType()->getIdentifier(),
                                'category' => $relation->getRelationType()->getCategory()
                            ]
                        ], $user->getUserRelations()->toArray())
                    ],
                    $users
                )
            ]
        );
    }

    #[Route('/{id}/roles', name: 'edit_roles', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editRoles(User $user): JsonResponse
    {
        // Verfügbare Rollen definieren
        $availableRoles = [
            'ROLE_GUEST' => 'Guest',
            'ROLE_USER' => 'Benutzer',
            'ROLE_SUPPORTER' => 'Supporter',
            'ROLE_ADMIN' => 'Administrator',
            'ROLE_SUPERADMIN' => 'Super Administrator'
        ];

        return $this->json([
            'user' => $user,
            'available_roles' => $availableRoles,
            'current_roles' => $user->getRoles()
        ]);
    }

    #[Route('/{id}/roles', name: 'update_roles', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateRoles(Request $request, User $user): JsonResponse
    {
        $currentUser = $this->getUser();
        $jsonContent = json_decode($request->getContent(), true);
        $selectedRoles = $jsonContent['roles'] ?? [];

        // Sicherstellen dass ROLE_USER immer gesetzt ist
        if (!in_array('ROLE_USER', $selectedRoles)) {
            $selectedRoles[] = 'ROLE_USER';
        }

        $answer = ['id' => $user->getId()];

        try {
            $user->setRoles($selectedRoles);
            $this->em->flush();
            $answer = ['success' => true, 'message' => 'Benutzerrollen wurden erfolgreich aktualisiert.'];
        } catch (Exception $e) {
            $answer = ['error' => 'Fehler beim Aktualisieren der Rollen: ' . $e->getMessage()];
        }

        return $this->json($answer);
    }

    #[Route('/{id}/assign', name: 'assign', methods: ['GET'])]
    public function assignForm(User $user): JsonResponse
    {
        $players = $this->em->getRepository(Player::class)->findAll();
        $coaches = $this->em->getRepository(Coach::class)->findAll();

        // Beziehungstypen aus der Datenbank laden
        $relationTypes = $this->em->getRepository(RelationType::class)->findBy([], ['name' => 'ASC']);
        $permissions = $this->em->getRepository(Permission::class)->findBy([], ['name' => 'ASC']);

        // Bestehende Zuordnungen laden
        $userRelations = $this->em->getRepository(UserRelation::class)->findBy(['user' => $user]);
        $currentAssignments = ['players' => [], 'coaches' => []];
        $entity = null;

        foreach ($userRelations as $relation) {
            $type = null;
            if ($relation->getPlayer()) {
                $type = 'players';
                $entity = $relation->getPlayer();
            } elseif ($relation->getCoach()) {
                $type = 'coaches';
                $entity = $relation->getCoach();
            }

            if (
                $entity instanceof Player
                || $entity instanceof Coach
            ) {
                $currentAssignments[$type][] = [
                    'entity' => [
                        'id' => $entity->getId(),
                        'fullName' => $entity->getFullName(),
                        'email' => $entity->getEmail()
                    ],
                    'relationType' => $relation->getRelationType(),
                    'permissions' => $relation->getPermissions()
                ];
            }
        }

        // RelationTypes nach Kategorie gruppieren
        $groupedRelationTypes = [];
        foreach ($relationTypes as $type) {
            $groupedRelationTypes[$type->getCategory()][] = $type;
        }

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'fullName' => $user->getFullName(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'isVerified' => $user->isVerified(),
                'isEnabled' => $user->isEnabled(),
                'userRelations' => array_map(fn (UserRelation $relation) => [
                    'id' => $relation->getId(),
                    'type' => $relation->getRelationType()->getName(),
                    'entity' => $relation->getPlayer() ? $relation->getPlayer()->getFullName() : ($relation->getCoach() ? $relation->getCoach()->getFullName() : null),
                    'permissions' => $relation->getPermissions(),
                    'relationType' => [
                        'id' => $relation->getRelationType()->getId(),
                        'name' => $relation->getRelationType()->getName(),
                        'identifier' => $relation->getRelationType()->getIdentifier(),
                        'category' => $relation->getRelationType()->getCategory()
                    ]
                ], $user->getUserRelations()->toArray())
            ],
            'players' => array_map(fn (Player $player) => [
                'id' => $player->getId(),
                'fullName' => $player->getFullName(),
                'email' => $player->getEmail()
            ], $players),
            'coaches' => array_map(fn (Coach $coach) => [
                'id' => $coach->getId(),
                'fullName' => $coach->getFullName(),
                'email' => $coach->getEmail()
            ], $coaches),
            'currentAssignments' => $currentAssignments,
            'relationTypes' => $groupedRelationTypes,
            'permissions' => $permissions
        ]);
    }

    #[Route('/{id}/assign', name: 'assign_post', methods: ['POST'])]
    public function handleAssign(Request $request, User $user): Response
    {
        $this->em->beginTransaction();
        try {
            $existingRelations = $this->em->getRepository(UserRelation::class)->findBy(['user' => $user]);
            foreach ($existingRelations as $relation) {
                $this->em->remove($relation);
            }

            $data = json_decode($request->getContent(), true);
            $relations = $data['relations'] ?? [];

            foreach ($relations as $index => $assignment) {
                if (empty($assignment['relationType']['id']) || empty($assignment['entity']['id'])) {
                    throw new InvalidArgumentException("Ungültige Daten für Zuordnung #{$index}");
                }

                $relationType = $this->em->getRepository(RelationType::class)->find($assignment['relationType']['id']);
                if (!$relationType) {
                    throw new InvalidArgumentException("Beziehungstyp mit ID {$assignment['relationType']['id']} nicht gefunden");
                }

                $category = $relationType->getCategory();
                $relation = new UserRelation();
                $relation->setUser($user);
                $relation->setRelationType($relationType);
                $relation->setPermissions($assignment['permissions'] ?? []);

                if ('player' === $category) {
                    $player = $this->em->getRepository(Player::class)->find($assignment['entity']['id']);
                    if (!$player) {
                        throw new InvalidArgumentException("Spieler mit ID {$assignment['entity']['id']} nicht gefunden");
                    }
                    $relation->setPlayer($player);
                } elseif ('coach' === $category) {
                    $coach = $this->em->getRepository(Coach::class)->find($assignment['entity']['id']);
                    if (!$coach) {
                        throw new InvalidArgumentException("Trainer mit ID {$assignment['entity']['id']} nicht gefunden");
                    }
                    $relation->setCoach($coach);
                } else {
                    throw new InvalidArgumentException("Unbekannte Kategorie: {$category}");
                }

                $this->em->persist($relation);
            }

            $this->em->flush();
            $this->em->commit();

            return $this->json([
                'status' => 'success',
                'message' => 'Zuordnungen erfolgreich aktualisiert.'
            ]);
        } catch (Exception $e) {
            $this->em->rollback();

            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/{id}/toggle-status', name: 'toggle_status', methods: ['GET'])]
    public function toggleStatus(User $user): Response
    {
        $user->setIsEnabled(!$user->isEnabled());
        $this->em->flush();

        return $this->json(['success' => true, 'message' => sprintf(
            'Benutzer %s wurde erfolgreich %s.',
            $user->getEmail(),
            $user->isEnabled() ? 'aktiviert' : 'deaktiviert'
        )]);
    }

    #[Route('/search/{type}', name: 'search', methods: ['GET'])]
    public function search(string $type, Request $request): JsonResponse
    {
        $term = $request->query->get('term');
        if (empty($term)) {
            return $this->json([]);
        }

        $qb = match ($type) {
            'player' => $this->em->getRepository(Player::class)->createQueryBuilder('p')
                ->where('p.firstName LIKE :term OR p.lastName LIKE :term OR p.email LIKE :term')
                ->setParameter('term', '%' . $term . '%')
                ->orderBy('p.lastName', 'ASC')
                ->setMaxResults(10),

            'coach' => $this->em->getRepository(Coach::class)->createQueryBuilder('c')
                ->where('c.firstName LIKE :term OR c.lastName LIKE :term OR c.email LIKE :term')
                ->setParameter('term', '%' . $term . '%')
                ->orderBy('c.lastName', 'ASC')
                ->setMaxResults(10),

            'club' => $this->em->getRepository(Club::class)->createQueryBuilder('c')
                ->where('c.name LIKE :term')
                ->setParameter('term', '%' . $term . '%')
                ->orderBy('c.name', 'ASC')
                ->setMaxResults(10),

            default => throw $this->createNotFoundException('Invalid search type'),
        };

        $results = $qb->getQuery()->getResult();

        $formatted = array_map(function ($item) use ($type) {
            return [
                'id' => $item->getId(),
                'text' => match ($type) {
                    'player', 'coach' => $item->getFullName() . ($item->getEmail() ? ' (' . $item->getEmail() . ')' : ''),
                    'club' => $item->getName(),
                    default => ''
                },
            ];
        }, $results);

        return $this->json(['results' => $formatted]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function deleteUser(User $user): Response
    {
        try {
            $this->em->remove($user);
            $this->em->flush();

            return $this->json(['success' => true, 'message' => 'Benutzer erfolgreich gelöscht.']);
        } catch (Exception $e) {
            return $this->json(['error' => 'Fehler beim Löschen des Benutzers: ' . $e->getMessage()], 400);
        }
    }
}
