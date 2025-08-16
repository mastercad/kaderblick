<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NewsRepositoryInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/news', name: 'app_news_')]
class NewsController extends AbstractController
{
    private EntityManagerInterface $em;
    private NewsRepositoryInterface $newsRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $repository = $em->getRepository(\App\Entity\News::class);
        assert($repository instanceof NewsRepositoryInterface);
        $this->newsRepository = $repository;
    }

    #[Route(name: 'index')]
    public function index(): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $news = $this->newsRepository->findForUser($user);

        // Debug Information - kann später entfernt werden
        $debugInfo = [];
        if ($user instanceof User) {
            $debugInfo['userId'] = $user->getId();
            $debugInfo['userEmail'] = $user->getEmail();
            $debugInfo['relationCount'] = $user->getRelations()->count();

            $clubIds = [];
            $teamIds = [];
            foreach ($user->getRelations() as $relation) {
                if ($relation->getPlayer()) {
                    foreach ($relation->getPlayer()->getPlayerClubAssignments() as $pca) {
                        $club = $pca->getClub();
                        if ($club) {
                            $clubIds[] = $club->getId();
                        }
                    }
                    foreach ($relation->getPlayer()->getPlayerTeamAssignments() as $pta) {
                        $team = $pta->getTeam();
                        $teamIds[] = $team->getId();
                    }
                }
                if ($relation->getCoach()) {
                    foreach ($relation->getCoach()->getCoachClubAssignments() as $cca) {
                        $club = $cca->getClub();
                        if ($club) {
                            $clubIds[] = $club->getId();
                        }
                    }
                    foreach ($relation->getCoach()->getCoachTeamAssignments() as $cta) {
                        $team = $cta->getTeam();
                        $teamIds[] = $team->getId();
                    }
                }
            }
            $debugInfo['clubIds'] = array_unique($clubIds);
            $debugInfo['teamIds'] = array_unique($teamIds);
        }

        // Alle News für Debug-Zwecke
        $allNews = $this->em->getRepository(\App\Entity\News::class)->findAll();
        $debugInfo['allNewsCount'] = count($allNews);
        $debugInfo['filteredNewsCount'] = count($news);

        return $this->render('news/index.html.twig', [
            'news' => $news,
            'user' => $user,
            'debugInfo' => $debugInfo, // Temporär für Debugging
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        if (
            !in_array('ROLE_ADMIN', $user->getRoles(), true)
            && !in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)
        ) {
            throw $this->createAccessDeniedException();
        }

        $visibilityOptions = [
            'Platform' => 'platform',
            'Club' => 'club',
            'Team' => 'team',
        ];

        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $content = $request->request->get('content');
            $visibility = $request->request->get('visibility');
            $club = null;
            $team = null;
            if ('club' === $visibility) {
                $clubId = $request->request->get('club_id');
                $club = $this->em->getRepository(\App\Entity\Club::class)->find($clubId);
            }
            if ('team' === $visibility) {
                $teamId = $request->request->get('team_id');
                $team = $this->em->getRepository(\App\Entity\Team::class)->find($teamId);
            }
            $news = new \App\Entity\News();
            $news->setTitle($title)
                ->setContent($content)
                ->setVisibility($visibility)
                ->setCreatedBy($user)
                ->setCreatedAt(new DateTimeImmutable())
                ->setClub($club)
                ->setTeam($team);
            $this->em->persist($news);
            $this->em->flush();

            return $this->render('news/success.html.twig', [
                'message' => 'News sent successfully',
            ]);
        }

        // Vereine und Teams für Auswahl
        $clubs = [];
        $teams = [];

        // Für Admins: alle Clubs und Teams
        if (in_array('ROLE_ADMIN', $user->getRoles(), true) || in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            $allClubs = $this->em->getRepository(\App\Entity\Club::class)->findAll();
            foreach ($allClubs as $club) {
                $clubs[$club->getId()] = $club;
            }
            $allTeams = $this->em->getRepository(\App\Entity\Team::class)->findAll();
            foreach ($allTeams as $team) {
                $teams[$team->getId()] = $team;
            }
        } else {
            // Für normale User: nur über Relations ermitteln
            foreach ($user->getRelations() as $relation) {
                if ($relation->getPlayer()) {
                    foreach ($relation->getPlayer()->getPlayerTeamAssignments() as $pta) {
                        $team = $pta->getTeam();
                        $teams[$team->getId()] = $team;
                    }
                    foreach ($relation->getPlayer()->getPlayerClubAssignments() as $pca) {
                        $club = $pca->getClub();
                        $clubs[$club->getId()] = $club;
                    }
                }
                if ($relation->getCoach()) {
                    foreach ($relation->getCoach()->getCoachTeamAssignments() as $cta) {
                        $team = $cta->getTeam();
                        $teams[$team->getId()] = $team;
                    }
                    foreach ($relation->getCoach()->getCoachClubAssignments() as $cca) {
                        $club = $cca->getClub();
                        $clubs[$club->getId()] = $club;
                    }
                }
            }
        }

        return $this->render('news/create.html.twig', [
            'visibilityOptions' => $visibilityOptions,
            'clubs' => $clubs,
            'teams' => $teams,
        ]);
    }
}
