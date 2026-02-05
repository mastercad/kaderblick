<?php

namespace App\Controller\ApiResource;

use App\Entity\Tournament;
use App\Entity\User;
use App\Entity\TournamentMatch;
use App\Entity\TournamentTeam;
use App\Entity\Team;
use App\Security\Voter\TournamentVoter;
use App\Service\TournamentPlanGenerator;
use App\Service\TournamentMatchGameService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route('/api/tournaments', name: 'api_tournament_')]
class TournamentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private TournamentPlanGenerator $planGenerator,
        private TournamentMatchGameService $matchGameService
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $repo = $this->em->getRepository(Tournament::class);
        $tournaments = $repo->findAll();

        $data = [];
        foreach ($tournaments as $tournament) {
            if (!$this->isGranted(TournamentVoter::VIEW, $tournament)) {
                continue;
            }

            $data[] = [
                'id' => $tournament->getId(),
                'name' => $tournament->getName(),
                'type' => $tournament->getType(),
                'startAt' => $tournament->getStartAt()?->format(DATE_ATOM),
                'endAt' => $tournament->getEndAt()?->format(DATE_ATOM),
                'createdBy' => $tournament->getCreatedBy()?->getId(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Tournament $tournament): JsonResponse
    {
        $repo = $this->em->getRepository(Tournament::class);

        $data = [
            'id' => $tournament->getId(),
            'name' => $tournament->getName(),
            'type' => $tournament->getType(),
            'startAt' => $tournament->getStartAt()?->format(DATE_ATOM),
            'endAt' => $tournament->getEndAt()?->format(DATE_ATOM),
            'settings' => $tournament->getSettings(),
            'createdBy' => $tournament->getCreatedBy()?->getId(),
        ];

        return new JsonResponse($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(TournamentVoter::CREATE);

        /** @var User $user */
        $user = $this->getUser();

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        $tournament = new Tournament();
        $tournament->setName($payload['name'] ?? '');
        $tournament->setType($payload['type'] ?? 'knockout');
        if (!empty($payload['startAt'])) {
            $tournament->setStartAt(new DateTimeImmutable($payload['startAt']));
        }
        if (!empty($payload['endAt'])) {
            $tournament->setEndAt(new DateTimeImmutable($payload['endAt']));
        }
        $tournament->setSettings($payload['settings'] ?? null);
        $tournament->setCreatedBy($user);

        $this->em->persist($tournament);
        $this->em->flush();

        return new JsonResponse(['id' => $tournament->getId()], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Tournament $tournament): JsonResponse
    {
        $this->denyAccessUnlessGranted(TournamentVoter::EDIT, $tournament);

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        $this->denyAccessUnlessGranted(TournamentVoter::EDIT, $tournament);

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($payload['name'])) {
            $tournament->setName($payload['name']);
        }
        if (isset($payload['type'])) {
            $tournament->setType($payload['type']);
        }
        if (isset($payload['startAt'])) {
            $tournament->setStartAt(new DateTimeImmutable($payload['startAt']));
        }
        if (isset($payload['endAt'])) {
            $tournament->setEndAt(new DateTimeImmutable($payload['endAt']));
        }
        if (array_key_exists('settings', $payload)) {
            $tournament->setSettings($payload['settings']);
        }

        $this->em->persist($tournament);
        $this->em->flush();

        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Tournament $tournament): JsonResponse
    {
        $this->denyAccessUnlessGranted(TournamentVoter::DELETE, $tournament);

        $this->em->remove($tournament);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/matches', name: 'matches', methods: ['GET'])]
    public function matches(Tournament $tournament): JsonResponse
    {
        if (!$this->isGranted(TournamentVoter::VIEW, $tournament)) {
            return new JsonResponse(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $mRepo = $this->em->getRepository(TournamentMatch::class);
        $matches = $mRepo->findBy(['tournament' => $tournament], ['round' => 'ASC', 'slot' => 'ASC']);

        $data = array_map(function (TournamentMatch $tournamentMatch) {
            return [
                'id' => $tournamentMatch->getId(),
                'round' => $tournamentMatch->getRound(),
                'slot' => $tournamentMatch->getSlot(),
                'homeTeamId' => $tournamentMatch->getHomeTeam()?->getTeam()?->getId(),
                'awayTeamId' => $tournamentMatch->getAwayTeam()?->getTeam()?->getId(),
                'homeTeamName' => $tournamentMatch->getHomeTeam()?->getTeam()?->__toString(),
                'awayTeamName' => $tournamentMatch->getAwayTeam()?->getTeam()?->__toString(),
            ];
        }, $matches);

        return new JsonResponse($data);
    }

    #[Route('/{id}/generate-plan', name: 'generate_plan', methods: ['POST'])]
    public function generatePlan(Tournament $tournament, Request $request): JsonResponse
    {
        $repo = $this->em->getRepository(Tournament::class);

        $this->denyAccessUnlessGranted(TournamentVoter::EDIT, $tournament);

        $payload = json_decode((string) $request->getContent(), true);

        // If client supplies explicit matches list -> create them
        if (is_array($payload) && ((!empty($payload['mode']) && $payload['mode'] === 'matches') || !empty($payload['matches']))) {
            $matches = $payload['matches'] ?? [];
            if (!is_array($matches) || count($matches) === 0) {
                return new JsonResponse(['error' => 'No matches provided'], Response::HTTP_BAD_REQUEST);
            }

            $teamRepo = $this->em->getRepository(Team::class);
            $ttRepo = $this->em->getRepository(TournamentTeam::class);
            $created = [];
            $slotCounter = 1;

            foreach ($matches as $match) {
                $homeId = $match['homeTeamId'] ?? null;
                $awayId = $match['awayTeamId'] ?? null;
                if (!$homeId || !$awayId) {
                    continue;
                }

                $home = $teamRepo->find($homeId);
                $away = $teamRepo->find($awayId);
                if (!$home || !$away) {
                    return new JsonResponse(['error' => 'Unknown team id in matches'], Response::HTTP_BAD_REQUEST);
                }

                // ensure TournamentTeam exists
                $homeTT = $ttRepo->findOneBy(['tournament' => $tournament, 'team' => $home]);
                if (!$homeTT) {
                    $homeTT = new TournamentTeam();
                    $homeTT->setTournament($tournament);
                    $homeTT->setTeam($home);
                    if (!empty($match['group'])) {
                        $homeTT->setGroupKey((string)$match['group']);
                    }
                    $this->em->persist($homeTT);
                }
                $awayTT = $ttRepo->findOneBy(['tournament' => $tournament, 'team' => $away]);
                if (!$awayTT) {
                    $awayTT = new TournamentTeam();
                    $awayTT->setTournament($tournament);
                    $awayTT->setTeam($away);
                    if (!empty($match['group'])) {
                        $awayTT->setGroupKey((string)$match['group']);
                    }
                    $this->em->persist($awayTT);
                }

                $tournamentMatch = new TournamentMatch();
                $tournamentMatch->setTournament($tournament);
                $tournamentMatch->setHomeTeam($homeTT);
                $tournamentMatch->setAwayTeam($awayTT);
                $tournamentMatch->setRound(isset($match['round']) ? (int)$match['round'] : 0);
                $tournamentMatch->setSlot(isset($match['slot']) ? (int)$match['slot'] : $slotCounter++);
                if (!empty($match['status'])) {
                    $tournamentMatch->setStatus((string)$match['status']);
                }
                if (!empty($match['scheduledAt'])) {
                    try {
                        $tournamentMatch->setScheduledAt(new DateTimeImmutable($match['scheduledAt']));
                    } catch (Throwable $e) {
                    }
                }

                $this->em->persist($tournamentMatch);
                $created[] = $tournamentMatch;
            }

            $this->em->flush();

            // Für alle angelegten Matches: Game + CalendarEvent anlegen, falls Teams gesetzt
            foreach ($created as $tournamentMatch) {
                if ($tournamentMatch->getHomeTeam() && $tournamentMatch->getAwayTeam()) {
                    $this->matchGameService->createGameForMatch($tournamentMatch, $tournamentMatch->getScheduledAt());
                }
            }


            $data = array_map(function (TournamentMatch $tournamentMatch) {
                return ['id' => $tournamentMatch->getId(), 'round' => $tournamentMatch->getRound(), 'slot' => $tournamentMatch->getSlot()];
            }, $created);

            return new JsonResponse($data, Response::HTTP_CREATED);
        }

        // fallback: seed-based generation (existing behaviour)
        $created = $this->planGenerator->generateSingleElimination($tournament);

        $data = array_map(function (TournamentMatch $tournamentMatch) {
            return [
                'id' => $tournamentMatch->getId(),
                'round' => $tournamentMatch->getRound(),
                'slot' => $tournamentMatch->getSlot(),
            ];
        }, $created);

        return new JsonResponse($data, Response::HTTP_CREATED);
    }
}
