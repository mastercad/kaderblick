<?php

namespace App\Controller\Api;

use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
use App\Security\Voter\TeamVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/teams', name: 'api_teams_')]
class TeamsController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('/list', name: 'api_teams_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var TeamRepository $teamsRepository */
        $teamsRepository = $this->em->getRepository(Team::class);
        /** @var Team[] $teams */
        $teams = $teamsRepository->fetchOptimizedList($user);

        return $this->json([
            'teams' => array_map(fn ($team) => [
                'id' => $team['id'],
                'name' => $team['name'],
                'permissions' => [
                    'canView' => $this->isGranted(TeamVoter::VIEW, $team),
                    'canEdit' => $this->isGranted(TeamVoter::EDIT, $team),
                    'canDelete' => $this->isGranted(TeamVoter::DELETE, $team),
                    'canCreate' => $this->isGranted(TeamVoter::CREATE, $team)
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
        $teamsRepository = $this->em->getRepository(Team::class);
        /** @var Team[] $teams */
        $teams = $teamsRepository->fetchOptimizedList($user);

        return $this->json([
            'teams' => array_map(fn ($team) => [
                'id' => $team['id'],
                'name' => $team['name'],
                'permissions' => [
                    'canView' => $this->isGranted(TeamVoter::VIEW, $team),
                    'canEdit' => $this->isGranted(TeamVoter::EDIT, $team),
                    'canDelete' => $this->isGranted(TeamVoter::DELETE, $team),
                    'canCreate' => $this->isGranted(TeamVoter::CREATE, $team)
                ]
            ], $teams)
        ]);
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
            $result[] = [
                'id' => $player->getId(),
                'fullName' => $player->getFirstName() . ' ' . $player->getLastName(),
                'shirtNumber' => $shirtNumber,
            ];
        }

        return $this->json($result);
    }
}
