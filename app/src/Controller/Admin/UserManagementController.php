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
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users', name: 'admin_users_')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private RoleHierarchyInterface $roleHierarchy
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->em->getRepository(User::class)->findBy([], ['lastName' => 'ASC', 'firstName' => 'ASC']);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/{id}/roles', name: 'edit_roles', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editRoles(User $user): Response
    {
        // Verfügbare Rollen definieren
        $availableRoles = [
            'ROLE_USER' => 'Benutzer',
            'ROLE_ADMIN' => 'Administrator',
            'ROLE_SUPER_ADMIN' => 'Super Administrator'
        ];

        return $this->render('admin/users/roles.html.twig', [
            'user' => $user,
            'available_roles' => $availableRoles,
            'current_roles' => $user->getRoles()
        ]);
    }

    #[Route('/{id}/roles', name: 'update_roles', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateRoles(Request $request, User $user): Response
    {
        // Prüfen ob der aktuelle Benutzer die Berechtigung hat, diese Rolle zu vergeben
        $currentUser = $this->getUser();
        $selectedRoles = $request->request->all()['roles'] ?? [];

        // Sicherstellen dass ROLE_USER immer gesetzt ist
        if (!in_array('ROLE_USER', $selectedRoles)) {
            $selectedRoles[] = 'ROLE_USER';
        }

        // Prüfen ob der aktuelle Benutzer die ausgewählten Rollen vergeben darf
        $userRoles = $this->roleHierarchy->getReachableRoleNames($currentUser->getRoles());
        foreach ($selectedRoles as $role) {
            if (!in_array($role, $userRoles)) {
                $this->addFlash('error', 'Sie haben nicht die Berechtigung, diese Rolle zu vergeben: ' . $role);

                return $this->redirectToRoute('admin_users_edit_roles', ['id' => $user->getId()]);
            }
        }

        try {
            $user->setRoles($selectedRoles);
            $this->em->flush();
            $this->addFlash('success', 'Benutzerrollen wurden erfolgreich aktualisiert.');
        } catch (Exception $e) {
            $this->addFlash('error', 'Fehler beim Aktualisieren der Rollen: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_users_edit_roles', ['id' => $user->getId()]);
    }

    #[Route('/{id}/assign', name: 'assign', methods: ['GET'])]
    public function assignForm(User $user): Response
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
                    'entity' => $entity,
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

        return $this->render('admin/users/assign.html.twig', [
            'user' => $user,
            'players' => $players,
            'coaches' => $coaches,
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

            $playerAssignments = $request->request->all('player_assignments');
            foreach ($playerAssignments as $index => $assignment) {
                if (empty($assignment['id']) || empty($assignment['type'])) {
                    throw new InvalidArgumentException("Ungültige Daten für Spielerzuordnung #{$index}");
                }

                $player = $this->em->getRepository(Player::class)->find($assignment['id']);
                if (!$player) {
                    throw new InvalidArgumentException("Spieler mit ID {$assignment['id']} nicht gefunden");
                }

                $relationType = $this->em->getRepository(RelationType::class)->find($assignment['type']);
                if (!$relationType) {
                    throw new InvalidArgumentException("Beziehungstyp mit ID {$assignment['type']} nicht gefunden");
                }

                if ('player' !== $relationType->getCategory()) {
                    throw new InvalidArgumentException("Beziehungstyp {$relationType->getName()} ist nicht für Spieler vorgesehen");
                }

                $relation = new UserRelation();
                $relation->setUser($user);
                $relation->setPlayer($player);
                $relation->setRelationType($relationType);
                $relation->setPermissions($assignment['permissions'] ?? []);

                $this->em->persist($relation);
            }

            // Neue Coach-Zuordnungen
            $coachAssignments = $request->request->all('coach_assignments');
            foreach ($coachAssignments as $index => $assignment) {
                if (empty($assignment['id']) || empty($assignment['type'])) {
                    throw new InvalidArgumentException("Ungültige Daten für Trainerzuordnung #{$index}");
                }

                $coach = $this->em->getRepository(Coach::class)->find($assignment['id']);
                if (!$coach) {
                    throw new InvalidArgumentException("Trainer mit ID {$assignment['id']} nicht gefunden");
                }

                $relationType = $this->em->getRepository(RelationType::class)->find($assignment['type']);
                if (!$relationType) {
                    throw new InvalidArgumentException("Beziehungstyp mit ID {$assignment['type']} nicht gefunden");
                }

                if ('coach' !== $relationType->getCategory()) {
                    throw new InvalidArgumentException("Beziehungstyp {$relationType->getName()} ist nicht für Trainer vorgesehen");
                }

                $relation = new UserRelation();
                $relation->setUser($user);
                $relation->setCoach($coach);
                $relation->setRelationType($relationType);
                $relation->setPermissions($assignment['permissions'] ?? []);

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
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Im Produktivbetrieb entfernen!
            ], 400);
        }
    }

    #[Route('/{id}/toggle-status', name: 'toggle_status', methods: ['GET'])]
    public function toggleStatus(User $user): Response
    {
        $user->setIsEnabled(!$user->isEnabled());
        $this->em->flush();

        $this->addFlash(
            'success',
            sprintf(
                'Benutzer %s wurde erfolgreich %s.',
                $user->getEmail(),
                $user->isEnabled() ? 'aktiviert' : 'deaktiviert'
            ),
        );

        return $this->redirectToRoute('admin_users_index');
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
}
