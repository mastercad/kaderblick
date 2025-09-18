<?php

namespace App\Controller\Api;

use App\Entity\AgeGroup;
use App\Entity\League;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
use App\Security\Voter\TeamVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/teams', name: 'api_teams_')]
class TeamsController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/list', name: 'api_teams_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var TeamRepository $teamsRepository */
        $teamsRepository = $this->entityManager->getRepository(Team::class);
        /** @var Team[] $teams */
        $teams = $teamsRepository->fetchOptimizedList($user);

        return $this->json([
            'teams' => array_map(fn ($team) => [
                'id' => $team['id'],
                'name' => $team['name'],
                'ageGroup' => [
                    'id' => $team['age_group_id'],
                    'name' => $team['age_group_name'],
                ],
                'league' => [
                    'id' => $team['league_id'],
                    'name' => $team['league_name'],
                ],
                'permissions' => [
                    /*
                    'canView' => $this->isGranted(TeamVoter::VIEW, $team),
                    'canEdit' => $this->isGranted(TeamVoter::EDIT, $team),
                    'canDelete' => $this->isGranted(TeamVoter::DELETE, $team),
                    'canCreate' => $this->isGranted(TeamVoter::CREATE, $team)
                    */
                    'canView' => true,
                    'canEdit' => $this->isGranted('ROLE_ADMIN', $user) || $this->isGranted('ROLE_SUPERADMIN', $user),
                    'canDelete' => $this->isGranted('ROLE_ADMIN', $user) || $this->isGranted('ROLE_SUPERADMIN', $user),
                    'canCreate' => $this->isGranted('ROLE_ADMIN', $user) || $this->isGranted('ROLE_SUPERADMIN', $user),
                ]
            ], $teams)
        ]);
    }

    #[Route('', name: 'api_teams_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var TeamRepository $teamsRepository */
        $teamsRepository = $this->entityManager->getRepository(Team::class);
        /** @var Team[] $teams */
        $teams = $teamsRepository->fetchOptimizedList($user);

        return $this->json([
            'teams' => array_map(fn ($team) => [
                'id' => $team['id'],
                'name' => $team['name'],
                'ageGroup' => [
                    'id' => $team['age_group_id'],
                    'name' => $team['age_group_name'],
                ],
                'league' => [
                    'id' => $team['league_id'],
                    'name' => $team['league_name'],
                ],
                'permissions' => [
                    'canView' => true,
                    'canEdit' => $this->isGranted('ROLE_ADMIN', $user) || $this->isGranted('ROLE_SUPERADMIN', $user),
                    'canDelete' => $this->isGranted('ROLE_ADMIN', $user) || $this->isGranted('ROLE_SUPERADMIN', $user),
                    'canCreate' => $this->isGranted('ROLE_ADMIN', $user) || $this->isGranted('ROLE_SUPERADMIN', $user),
                ]
            ], $teams)
        ]);
    }

    #[Route('/{id}/details', name: 'api_team_show', methods: ['GET'])]
    public function show(Team $team): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'team' => [
                'id' => $team->getId(),
                'name' => $team->getName(),
                'ageGroup' => [
                    'id' => $team->getAgeGroup()->getId(),
                    'name' => $team->getAgeGroup()->getName(),
                ],
                'league' => [
                    'id' => $team->getLeague()?->getId(),
                    'name' => $team->getLeague()?->getName(),
                ],
                'permissions' => [
                    'canView' => $this->isGranted(TeamVoter::VIEW, $team),
                    'canEdit' => $this->isGranted(TeamVoter::EDIT, $team),
                    'canDelete' => $this->isGranted(TeamVoter::DELETE, $team),
                    'canCreate' => $this->isGranted(TeamVoter::CREATE, $team)
                ]
            ]
        ]);
    }

    #[Route('/{id}', name: 'api_team_update', methods: ['PUT'])]
    public function update(Team $team, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isGranted(TeamVoter::CREATE, $team)) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $teamData = json_decode($request->getContent(), true);

        $team->setName($teamData['name']);

        if (isset($teamData['ageGroup']['id'])) {
            $ageGroupRepository = $this->entityManager->getRepository(AgeGroup::class);
            $ageGroup = $ageGroupRepository->find($teamData['ageGroup']['id']);
            if ($ageGroup) {
                $team->setAgeGroup($ageGroup);
            }
        }

        if (isset($teamData['league']['id'])) {
            $leagueRepository = $this->entityManager->getRepository(League::class);
            $league = $leagueRepository->find($teamData['league']['id']);
            if ($league) {
                $team->setLeague($league);
            }
        }

        $this->entityManager->persist($team);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('', name: 'api_team_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $teamData = json_decode($request->getContent(), true);

        $team = new Team();

        if (!$this->isGranted(TeamVoter::CREATE, $team)) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $team->setName($teamData['name']);

        if (isset($teamData['ageGroup']['id'])) {
            $ageGroupRepository = $this->entityManager->getRepository(AgeGroup::class);
            $ageGroup = $ageGroupRepository->find($teamData['ageGroup']['id']);
            if ($ageGroup) {
                $team->setAgeGroup($ageGroup);
            }
        }

        if (isset($teamData['league']['id'])) {
            $leagueRepository = $this->entityManager->getRepository(League::class);
            $league = $leagueRepository->find($teamData['league']['id']);
            if ($league) {
                $team->setLeague($league);
            }
        }
        $this->entityManager->persist($team);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}', name: 'api_team_delete', methods: ['DELETE'])]
    public function delete(Team $team): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isGranted(TeamVoter::DELETE, $team)) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($team);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/players', name: 'api_team_players', methods: ['GET'])]
    public function players(Team $team): JsonResponse
    {
        $players = $team->getCurrentPlayers();
        $result = [];
        foreach ($players as $player) {
            $shirtNumber = '';
            foreach ($player->getPlayerTeamAssignments() as $pta) {
                if ($pta->getTeam()->getId() === $team->getId()) {
                    $shirtNumber = $pta->getShirtNumber();
                    break;
                }
            }
            $result[$shirtNumber] = [
                'id' => $player->getId(),
                'fullName' => $player->getFirstName() . ' ' . $player->getLastName(),
                'shirtNumber' => $shirtNumber,
            ];
        }

        ksort($result);

        return $this->json($result);
    }
}
