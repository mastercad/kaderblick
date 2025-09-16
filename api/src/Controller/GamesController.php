<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\GameEventType;
use App\Entity\Video;
use App\Repository\CameraRepository;
use App\Repository\GameEventRepository;
use App\Repository\GameRepository;
use App\Repository\VideoTypeRepository;
use App\Security\Voter\VideoVoter;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/games', name: 'games_')]
class GamesController extends AbstractController
{
    private int $youtubeLinkStartOffset = -60;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(GameRepository $gameRepository): Response
    {
        $now = new DateTimeImmutable();

        $upcomingGames = $gameRepository->createQueryBuilder('g')
            ->leftJoin('g.calendarEvent', 'ce')
            ->leftJoin('ce.calendarEventType', 'cet')
            ->addSelect('ce', 'cet')
            ->where('cet.name = :spiel')
            ->andWhere('ce.startDate > :now')
            ->andWhere('ce.endDate > :now OR ce.endDate IS NULL')
            ->setParameter('spiel', 'Spiel')
            ->setParameter('now', $now)
            ->orderBy('ce.startDate', 'ASC')
            ->getQuery()
            ->getResult();

        $otherGames = $gameRepository->createQueryBuilder('g')
            ->leftJoin('g.calendarEvent', 'ce')
            ->leftJoin('ce.calendarEventType', 'cet')
            ->addSelect('ce', 'cet')
            ->where('cet.name = :spiel')
            ->andWhere('ce.startDate <= :now')
            ->setParameter('spiel', 'Spiel')
            ->setParameter('now', $now)
            ->orderBy('ce.startDate', 'DESC')
            ->getQuery()
            ->getResult();

        $running = [];
        $finished = [];

        foreach ($otherGames as $game) {
            $ce = $game->getCalendarEvent();
            if (!$ce) {
                continue;
            }
            $start = $ce->getStartDate();
            $end = $ce->getEndDate();
            if ($start && $end && $now >= $start && $now <= $end) {
                $running[] = $game;
            } else {
                $gameEvents = [];

                foreach ($game->getGameEvents() as $gameEvent) {
                    $gameEvents[] = $gameEvent;
                }
                $scores = $this->collectScores($gameEvents, $game);

                $finished[] = [
                    'game' => $game,
                    'homeScore' => $scores['home'],
                    'awayScore' => $scores['away']
                ];
            }
        }

        return $this->render('games/index.html.twig', [
            'running_games' => $running,
            'upcoming_games' => $upcomingGames,
            'finished_games' => $finished,
        ]);
    }

    #[Route(path: '/api/overview', name: 'api_overview', methods: ['GET'])]
    public function apiOverview(GameRepository $gameRepository): Response
    {
        $now = new DateTimeImmutable();

        $upcomingGames = $gameRepository->createQueryBuilder('g')
            ->leftJoin('g.calendarEvent', 'ce')
            ->leftJoin('ce.calendarEventType', 'cet')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->leftJoin('g.location', 'l')
            ->addSelect('ce', 'cet', 'ht', 'at', 'l')
            ->where('cet.name = :spiel')
            ->andWhere('ce.startDate > :now')
            ->andWhere('ce.endDate > :now OR ce.endDate IS NULL')
            ->setParameter('spiel', 'Spiel')
            ->setParameter('now', $now)
            ->orderBy('ce.startDate', 'ASC')
            ->getQuery()
            ->getResult();

        $otherGames = $gameRepository->createQueryBuilder('g')
            ->leftJoin('g.calendarEvent', 'ce')
            ->leftJoin('ce.calendarEventType', 'cet')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->leftJoin('g.location', 'l')
            ->addSelect('ce', 'cet', 'ht', 'at', 'l')
            ->where('cet.name = :spiel')
            ->andWhere('ce.startDate <= :now')
            ->setParameter('spiel', 'Spiel')
            ->setParameter('now', $now)
            ->orderBy('ce.startDate', 'DESC')
            ->getQuery()
            ->getResult();

        $running = [];
        $finished = [];

        foreach ($otherGames as $game) {
            $ce = $game->getCalendarEvent();
            if (!$ce) {
                continue;
            }
            $start = $ce->getStartDate();
            $end = $ce->getEndDate();
            if ($start && $end && $now >= $start && $now <= $end) {
                $running[] = $game;
            } else {
                $gameEvents = [];

                foreach ($game->getGameEvents() as $gameEvent) {
                    $gameEvents[] = $gameEvent;
                }
                $scores = $this->collectScores($gameEvents, $game);

                $finished[] = [
                    'game' => $game,
                    'homeScore' => $scores['home'],
                    'awayScore' => $scores['away']
                ];
            }
        }

        return $this->json([
            'running_games' => $running,
            'upcoming_games' => $upcomingGames,
            'finished_games' => $finished,
        ]);
    }

    #[Route(path: '/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Game $game, GameEventRepository $gameEventRepository, VideoTypeRepository $videoTypeRepository, CameraRepository $cameraRepository): Response
    {
        $calendarEvent = $game->getCalendarEvent();
        $gameEvents = $gameEventRepository->findAllGameEvents($game);

        $cameras = [];
        foreach ($cameraRepository->findAll() as $camera) {
            $cameras[$camera->getId()] = $camera;
        }
        ksort($cameras);

        $scores = $this->collectScores($gameEvents, $game);

        return $this->render('games/show.html.twig', [
            'game' => $game,
            'calendarEvent' => $calendarEvent,
            'gameEvents' => $gameEvents,
            'videoTypes' => $videoTypeRepository->findAll(),
            'videos' => $this->prepareYoutubeLinks($game, $gameEvents),
            'cameras' => $cameras,
            'homeScore' => $scores['home'],
            'awayScore' => $scores['away'],
            'permissions' => [
                'VIEW' => VideoVoter::VIEW,
                'EDIT' => VideoVoter::EDIT,
                'DELETE' => VideoVoter::DELETE,
                'CREATE' => VideoVoter::CREATE
            ],
        ]);
    }

    #[Route(path: '/{id}/details', name: 'api_details', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function apiDetails(Game $game, GameEventRepository $gameEventRepository, VideoTypeRepository $videoTypeRepository, CameraRepository $cameraRepository): Response
    {
        $gameEvents = $gameEventRepository->findAllGameEvents($game);
        $calendarEvent = $game->getCalendarEvent();
        $gameEvents = $gameEventRepository->findAllGameEvents($game);

        $cameras = [];
        foreach ($cameraRepository->findAll() as $camera) {
            $cameras[$camera->getId()] = $camera;
        }
        ksort($cameras);

        $scores = $this->collectScores($gameEvents, $game);

        return $this->json([
            'game' => $game,
            'location' => $game->getCalendarEvent()->getLocation(),
            'gameEvents' => $gameEvents,
            'homeScore' => $scores['home'],
            'awayScore' => $scores['away'],
            'videoTypes' => $videoTypeRepository->findAll(),
            'cameras' => $cameras,
            'videos' => $this->prepareYoutubeLinks($game, $gameEvents),
        ]);
    }

    /**
     * @param array<int, GameEvent> $gameEvents
     *
     * @return array<string, int>
     */
    private function collectScores(array $gameEvents, Game $game): array
    {
        $gameEventGoal = $this->entityManager->getRepository(GameEventType::class)->findOneBy(['code' => 'goal']);

        $homeScore = 0;
        $awayScore = 0;

        foreach ($gameEvents as $gameEvent) {
            if ($gameEvent->getGameEventType() === $gameEventGoal) {
                if ($gameEvent->getTeam() === $game->getHomeTeam()) {
                    ++$homeScore;
                } elseif ($gameEvent->getTeam() === $game->getAwayTeam()) {
                    ++$awayScore;
                }
            }
        }

        return [
            'home' => $homeScore,
            'away' => $awayScore
        ];
    }

    /**
     * @param array<int, GameEvent> $gameEvents
     *
     * @return array<int, array<int, list<string>>> $youtubeLinks
     */
    private function prepareYoutubeLinks(Game $game, array $gameEvents): array
    {
        $youtubeLinks = [];
        $videos = $this->orderVideos($game);

        $startTimestamp = $game->getCalendarEvent()?->getStartDate()?->getTimestamp();

        foreach ($gameEvents as $event) {
            $eventSeconds = ($event->getTimestamp()->getTimestamp() - $startTimestamp);

            foreach ($videos as $cameraId => $currentVideos) {
                $elapsedTime = 0;
                foreach ($currentVideos as $startTime => $video) {
                    if (!$this->isGranted(VideoVoter::VIEW, $video)) {
                        continue;
                    }
                    if (
                        $startTime <= ($eventSeconds + $video->getGameStart())
                        && (int) ($startTime + $video->getLength()) >= (int) ($eventSeconds + $video->getGameStart())
                    ) {
                        $seconds = $eventSeconds - $elapsedTime + (int) $video->getGameStart() + $this->youtubeLinkStartOffset;
                        $youtubeLinks[(int) $event->getId()][(int) $cameraId][] = $video->getUrl() .
                            '&t=' . $seconds . 's';
                    }
                    $elapsedTime += $video->getLength();
                }
            }
        }

        return $youtubeLinks;
    }

    /**
     * @return array<int, array<int, Video>> $videos
     */
    private function orderVideos(Game $game): array
    {
        $videosEntries = $game->getVideos()->toArray();
        $videos = [];
        $cameras = [];

        foreach ($videosEntries as $videoEntry) {
            $cameras[(int) $videoEntry->getCamera()->getId()][(int) $videoEntry->getSort()] = $videoEntry;
        }

        /* TODO potenziell performancelastig, aber aktuell nicht so tragisch */
        foreach ($cameras as $camera => $currentVideos) {
            $currentStart = 0;
            foreach ($currentVideos as $video) {
                $videos[$camera][$currentStart] = $video;
                $currentStart += $video->getLength();
            }
        }

        return $videos;
    }
}
