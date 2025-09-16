<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\News;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserRelation;
use App\Repository\NewsRepositoryInterface;
use App\Security\Voter\NewsVoter;
use App\Service\NotificationService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/news', name: 'app_news_')]
class NewsController extends AbstractController
{
    private EntityManagerInterface $em;
    private NewsRepositoryInterface $newsRepository;

    public function __construct(
        EntityManagerInterface $em,
        private NotificationService $notificationService
    ) {
        $this->em = $em;
        $repository = $em->getRepository(News::class);
        assert($repository instanceof NewsRepositoryInterface);
        $this->newsRepository = $repository;
    }

    #[Route(path: '', name: 'app_news_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var ?User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        // News für User
        $news = $this->newsRepository->findForUser($user);
        $newsArr = array_map(function (News $n) {
            $createdByUser = $n->getCreatedBy();

            return [
                'id' => $n->getId(),
                'title' => $n->getTitle(),
                'content' => $n->getContent(),
                'createdAt' => $n->getCreatedAt()->format('c'),
                'createdByUserId' => $n->getCreatedBy()->getId(),
                'createdByUserName' => trim($createdByUser->getFirstName() . ' ' . $createdByUser->getLastName()),
                'visibility' => $n->getVisibility(),
                'club' => $n->getClub()?->getId(),
                'team' => $n->getTeam()?->getId(),
            ];
        }, $news);

        // Clubs & Teams für Auswahl
        $clubs = [];
        $teams = [];
        if (in_array('ROLE_ADMIN', $user->getRoles(), true) || in_array('ROLE_SUPERADMIN', $user->getRoles(), true)) {
            $allClubs = $this->em->getRepository(Club::class)->findAll();
            foreach ($allClubs as $club) {
                $clubs[] = [
                    'id' => $club->getId(),
                    'name' => $club->getName(),
                ];
            }
            $allTeams = $this->em->getRepository(Team::class)->findAll();
            foreach ($allTeams as $team) {
                $teams[] = [
                    'id' => $team->getId(),
                    'name' => $team->getName(),
                ];
            }
        } else {
            foreach ($user->getUserRelations() as $relation) {
                if ($relation->getPlayer()) {
                    foreach ($relation->getPlayer()->getPlayerTeamAssignments() as $pta) {
                        $team = $pta->getTeam();
                        $teams[] = [
                            'id' => $team->getId(),
                            'name' => $team->getName(),
                        ];
                    }
                    foreach ($relation->getPlayer()->getPlayerClubAssignments() as $pca) {
                        $club = $pca->getClub();
                        $clubs[] = [
                            'id' => $club->getId(),
                            'name' => $club->getName(),
                        ];
                    }
                }
                if ($relation->getCoach()) {
                    foreach ($relation->getCoach()->getCoachTeamAssignments() as $cta) {
                        $team = $cta->getTeam();
                        $teams[] = [
                            'id' => $team->getId(),
                            'name' => $team->getName(),
                        ];
                    }
                    foreach ($relation->getCoach()->getCoachClubAssignments() as $cca) {
                        $club = $cca->getClub();
                        $clubs[] = [
                            'id' => $club->getId(),
                            'name' => $club->getName(),
                        ];
                    }
                }
            }
        }

        $visibilityOptions = [
            ['label' => 'Platform', 'value' => 'platform'],
            ['label' => 'Club', 'value' => 'club'],
            ['label' => 'Team', 'value' => 'team'],
        ];

        return new JsonResponse([
            'news' => $newsArr,
            'clubs' => $clubs,
            'teams' => $teams,
            'visibilityOptions' => $visibilityOptions,
        ]);
    }

    #[Route(path: '/create', name: 'app_news_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var ?User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $news = new News();

        if (!$this->isGranted(NewsVoter::CREATE, $news)) {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }
        $title = $data['title'] ?? null;
        $content = $data['content'] ?? null;
        $visibility = $data['visibility'] ?? null;
        $club = null;
        $team = null;
        if ('club' === $visibility && !empty($data['club_id'])) {
            $club = $this->em->getRepository(Club::class)->find($data['club_id']);
        }
        if ('team' === $visibility && !empty($data['team_id'])) {
            $team = $this->em->getRepository(Team::class)->find($data['team_id']);
        }
        if (!$title || !$content || !$visibility) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }
        $news->setTitle($title)
            ->setContent($content)
            ->setVisibility($visibility)
            ->setCreatedBy($user)
            ->setCreatedAt(new DateTimeImmutable())
            ->setClub($club)
            ->setTeam($team);
        $this->em->persist($news);
        $this->em->flush();

        $this->createNewsNotifications($news);

        return new JsonResponse(['success' => true, 'id' => $news->getId()]);
    }

    /**
     * Create notifications for users based on news visibility.
     */
    private function createNewsNotifications(News $news): void
    {
        $users = [];

        switch ($news->getVisibility()) {
            case 'platform':
                // Notify all users
                $users = $this->em->getRepository(User::class)->findAll();
                break;

            case 'club':
                if ($news->getClub()) {
                    // Find all users related to this club
                    $users = $this->retrieveUsersByClub($news->getClub());
                }
                break;

            case 'team':
                if ($news->getTeam()) {
                    // Find all users related to this team
                    $users = $this->retrieveUsersByTeam($news->getTeam());
                }
                break;
        }

        // Create notifications for all relevant users (except the author)
        $filteredUsers = array_filter($users, function (User $user) use ($news) {
            return $user->getId() !== $news->getCreatedBy()->getId();
        });

        if (!empty($filteredUsers)) {
            $this->notificationService->createNotificationForUsers(
                $filteredUsers,
                'news',
                'Neue Nachricht: ' . $news->getTitle(),
                substr($news->getContent(), 0, 100) . (strlen($news->getContent()) > 100 ? '...' : ''),
                ['newsId' => $news->getId(), 'url' => '/news/' . $news->getId()]
            );
        }
    }

    /**
     * Get users related to a club.
     *
     * @return User[]
     */
    private function retrieveUsersByClub(Club $club): array
    {
        $users = [];

        // Get users through UserRelations that have players in this club
        $userRelations = $this->em->getRepository(UserRelation::class)
            ->createQueryBuilder('ur')
            ->join('ur.player', 'p')
            ->join('p.playerClubAssignments', 'pca')
            ->where('pca.club = :club')
            ->setParameter('club', $club)
            ->getQuery()
            ->getResult();

        foreach ($userRelations as $relation) {
            $users[] = $relation->getUser();
        }

        // Get users through UserRelations that have coaches in this club
        $coachRelations = $this->em->getRepository(UserRelation::class)
            ->createQueryBuilder('ur')
            ->join('ur.coach', 'c')
            ->join('c.coachClubAssignments', 'cca')
            ->where('cca.club = :club')
            ->setParameter('club', $club)
            ->getQuery()
            ->getResult();

        foreach ($coachRelations as $relation) {
            $users[] = $relation->getUser();
        }

        return array_unique($users);
    }

    /**
     * Get users related to a team.
     *
     * @return User[]
     */
    private function retrieveUsersByTeam(Team $team): array
    {
        $users = [];

        // Get users through UserRelations that have players in this team
        $userRelations = $this->em->getRepository(UserRelation::class)
            ->createQueryBuilder('ur')
            ->join('ur.player', 'p')
            ->join('p.playerTeamAssignments', 'pta')
            ->where('pta.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();

        foreach ($userRelations as $relation) {
            $users[] = $relation->getUser();
        }

        // Get users through UserRelations that have coaches in this team
        $coachRelations = $this->em->getRepository(UserRelation::class)
            ->createQueryBuilder('ur')
            ->join('ur.coach', 'c')
            ->join('c.coachTeamAssignments', 'cta')
            ->where('cta.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();

        foreach ($coachRelations as $relation) {
            $users[] = $relation->getUser();
        }

        return array_unique($users);
    }
}
