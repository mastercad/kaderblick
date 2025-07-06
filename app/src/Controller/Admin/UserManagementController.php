<?php

namespace App\Controller\Admin;

use App\Entity\Club;
use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
        private RequestStack $requestStack,
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
        $clubs = $this->em->getRepository(Club::class)->findAll();

        // Flash Messages von vorherigen Status-Änderungen löschen
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->requestStack->getSession();
        $session->getFlashBag()->clear();

        return $this->render('admin/users/assign.html.twig', [
            'user' => $user,
            'players' => $players,
            'coaches' => $coaches,
            'clubs' => $clubs,
            'currentAssignment' => $this->getCurrentAssignment($user)
        ]);
    }

    #[Route('/{id}/assign', name: 'assign_post', methods: ['POST'])]
    public function handleAssign(Request $request, User $user): Response
    {
        try {
            // Wenn Club ausgewählt wurde, alle anderen Zuordnungen entfernen
            if ($request->request->get('club_id')) {
                $user->setPlayer(null)->setCoach(null);
                $club = $this->em->getRepository(Club::class)->find($request->request->get('club_id'));
                $user->setClub($club); // Hier wird null gesetzt wenn keine ID übergeben wurde
            } else {
                $user->setClub(null);

                // Player setzen oder entfernen
                $player = $request->request->get('player_id')
                    ? $this->em->getRepository(Player::class)->find($request->request->get('player_id'))
                    : null;
                $user->setPlayer($player);

                // Coach setzen oder entfernen
                $coach = $request->request->get('coach_id')
                    ? $this->em->getRepository(Coach::class)->find($request->request->get('coach_id'))
                    : null;
                $user->setCoach($coach);
            }

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Zuordnungen erfolgreich aktualisiert.');
        } catch (Exception $e) {
            $this->addFlash('error', 'Fehler bei der Zuordnung: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_users_assign', ['id' => $user->getId()]);
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

    /** @return array{type: string|null, entity: Player|Coach|Club|null} */
    private function getCurrentAssignment(User $user): array
    {
        if ($user->getPlayer()) {
            return ['type' => 'player', 'entity' => $user->getPlayer()];
        }
        if ($user->getCoach()) {
            return ['type' => 'coach', 'entity' => $user->getCoach()];
        }
        if ($user->getClub()) {
            return ['type' => 'club', 'entity' => $user->getClub()];
        }

        return ['type' => null, 'entity' => null];
    }
}
