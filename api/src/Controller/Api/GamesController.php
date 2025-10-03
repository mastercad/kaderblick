<?php

namespace App\Controller\Api;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\GameEventType;
use App\Entity\Video;
use App\Repository\CameraRepository;
use App\Repository\GameEventRepository;
use App\Repository\GameRepository;
use App\Repository\VideoTypeRepository;
use App\Security\Voter\GameEventVoter;
use App\Security\Voter\VideoVoter;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/games', name: 'api_games_')]
class GamesController extends ApiController
{
    private int $youtubeLinkStartOffset = -60;

    private EntityManagerInterface $entityManager;
    protected string $entityName = 'Game';
    protected string $entityNamePlural = 'Games';
    protected string $entityClass = Game::class;
    protected array $relations = [
        'homeTeam' => [
            'entityName' => 'Team',
            'fieldName' => 'homeTeam',
            'methodName' => 'homeTeam',
            'type' => 2
        ],
        'awayTeam' => [
            'entityName' => 'Team',
            'fieldName' => 'awayTeam',
            'methodName' => 'awayTeam',
            'type' => 2
        ],
        'location' => [
            'entityName' => 'Location',
            'type' => 2,
        ],
        'gameType' => [
            'entityName' => 'GameType',
            'type' => 2
        ],
        'gameEvents' => [
            'entityName' => 'GameEvent',
            'type' => 4,
            'label_fields' => ['gameEventType.name']
        ],
        'substitutions' => [
            'entityName' => 'Substitution',
            'type' => 4
        ],
        'calendarEvents' => [
            'entityName' => 'CalendarEvent',
            'type' => 1,
        ]
    ];
    protected array $relationEntries = [];
    protected string $urlPart = 'games';
    protected bool $createAndEditAllowed = false;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/{id}/details', name: 'details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function details(Game $game, GameEventRepository $gameEventRepository, VideoTypeRepository $videoTypeRepository, CameraRepository $cameraRepository): JsonResponse
    {
        $calendarEvent = $game->getCalendarEvent();
        $gameEvents = $gameEventRepository->findAllGameEvents($game);

        $cameras = [];
        foreach ($cameraRepository->findAll() as $camera) {
            $cameras[$camera->getId()] = $camera;
        }
        ksort($cameras);

        $scores = $this->collectScores($gameEvents, $game);
        $location = $game->getCalendarEvent()->getLocation();
        $serializeGame = function ($game) use ($calendarEvent, $location) {
            return [
                'id' => $game->getId(),
                'homeTeam' => $game->getHomeTeam() ? [
                    'id' => $game->getHomeTeam()->getId(),
                    'name' => $game->getHomeTeam()->getName(),
                ] : null,
                'awayTeam' => $game->getAwayTeam() ? [
                    'id' => $game->getAwayTeam()->getId(),
                    'name' => $game->getAwayTeam()->getName(),
                ] : null,
                'location' => $location ? [
                    'id' => $location->getId(),
                    'name' => $location->getName(),
                    'latitude' => $location->getLatitude(),
                    'longitude' => $location->getLongitude(),
                    'address' => $location->getAddress() . ', ' . $location->getCity()
                ] : null,
                'calendarEvent' => $calendarEvent ? [
                    'id' => $calendarEvent->getId(),
                    'startDate' => $calendarEvent->getStartDate()?->format('c'),
                    'endDate' => $calendarEvent->getEndDate()?->format('c'),
                    'weatherData' => $calendarEvent->getWeatherData() ? [
                        'dailyWeatherData' => $calendarEvent->getWeatherData()->getDailyWeatherData(),
                        'hourlyWeatherData' => $calendarEvent->getWeatherData()->getHourlyWeatherData(),
                    ] : [],
                ] : null,
                'fussballDeUrl' => method_exists($game, 'getFussballDeUrl') ? $game->getFussballDeUrl() : null,
                'permissions' => [
                    'can_create_videos' => $this->isGranted(VideoVoter::CREATE, $game->getHomeTeam()) || $this->isGranted(VideoVoter::CREATE, $game->getAwayTeam()),
                    'can_create_game_events' => $this->isGranted(GameEventVoter::CREATE, $game)
                ]
            ];
        };

        $gameEvents = $gameEventRepository->findAllGameEvents($game);
        $scores = $this->collectScores($gameEvents, $game);

        // Events serialisieren (nur relevante Felder)
        $serializeEvent = function ($event) {
            return [
                'id' => $event->getId(),
                'gameEventType' => $event->getGameEventType() ? [
                    'id' => $event->getGameEventType()->getId(),
                    'name' => $event->getGameEventType()->getName(),
                    'code' => $event->getGameEventType()->getCode(),
                    'icon' => $event->getGameEventType()->getIcon(),
                    'color' => $event->getGameEventType()->getColor()
                ] : null,
                'player' => $event->getPlayer() ? [
                    'id' => $event->getPlayer()->getId(),
                    'firstName' => $event->getPlayer()->getFirstName(),
                    'lastName' => $event->getPlayer()->getLastName(),
                ] : null,
                'relatedPlayer' => $event->getRelatedPlayer() ? [
                    'id' => $event->getRelatedPlayer()->getId(),
                    'firstName' => $event->getRelatedPlayer()->getFirstName(),
                    'lastName' => $event->getRelatedPlayer()->getLastName(),
                ] : null,
                'team' => $event->getTeam() ? [
                    'id' => $event->getTeam()->getId(),
                    'name' => $event->getTeam()->getName(),
                ] : null,
                'timestamp' => $event->getTimestamp()?->format('c'),
                'description' => $event->getDescription(),
            ];
        };
        $gameEventsArr = array_map($serializeEvent, $gameEvents);

        return $this->json([
            'game' => $serializeGame($game),
            'gameEvents' => $gameEventsArr,
            'homeScore' => $scores['home'],
            'awayScore' => $scores['away'],
            'videos' => $this->prepareYoutubeLinks($game, $gameEvents),
            //            'videoTypes' => $videoTypeRepository->findAll(),
            //            'cameras' => $cameras,
        ]);
    }

    #[Route('/overview', name: 'overview', methods: ['GET'])]
    public function overview(GameRepository $gameRepository, GameEventRepository $gameEventRepository): JsonResponse
    {
        $now = new DateTimeImmutable();

        $upcomingGames = $gameRepository->createQueryBuilder('g')
            ->addSelect('ce', 'cet', 'ht', 'at', 'l', 'wd')
            ->leftJoin('g.calendarEvent', 'ce')
            ->leftJoin('ce.calendarEventType', 'cet')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->leftJoin('g.location', 'l')
            ->leftJoin('ce.weatherData', 'wd')
            ->where('cet.name = :spiel')
            ->andWhere('ce.startDate > :now')
            ->andWhere('ce.endDate > :now OR ce.endDate IS NULL')
            ->setParameter('spiel', 'Spiel')
            ->setParameter('now', $now)
            ->orderBy('ce.startDate', 'ASC')
            ->getQuery()
            ->getResult();

        $otherGames = $gameRepository->createQueryBuilder('g')
            ->addSelect('ce', 'cet', 'ht', 'at', 'l', 'wd')
            ->leftJoin('g.calendarEvent', 'ce')
            ->leftJoin('ce.calendarEventType', 'cet')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->leftJoin('g.location', 'l')
            ->leftJoin('ce.weatherData', 'wd')
            ->where('cet.name = :spiel')
            ->andWhere('ce.startDate <= :now')
            ->setParameter('spiel', 'Spiel')
            ->setParameter('now', $now)
            ->orderBy('ce.startDate', 'DESC')
            ->getQuery()
            ->getResult();

        $running = [];
        $finished = [];

        $serializeGame = function ($game) {
            $calendarEvent = $game->getCalendarEvent();
            $location = $calendarEvent->getLocation();

            return [
                'id' => $game->getId(),
                'homeTeam' => $game->getHomeTeam() ? [
                    'id' => $game->getHomeTeam()->getId(),
                    'name' => $game->getHomeTeam()->getName(),
                ] : null,
                'awayTeam' => $game->getAwayTeam() ? [
                    'id' => $game->getAwayTeam()->getId(),
                    'name' => $game->getAwayTeam()->getName(),
                ] : null,
                'location' => $location ? [
                    'id' => $location->getId(),
                    'name' => $location->getName(),
                    'latitude' => $location->getLatitude(),
                    'longitude' => $location->getLongitude(),
                    'address' => $location->getAddress() . ', ' . $location->getCity()
                ] : null,
                'calendarEvent' => $calendarEvent ? [
                    'id' => $calendarEvent->getId(),
                    'startDate' => $calendarEvent->getStartDate()?->format('c'),
                    'endDate' => $calendarEvent->getEndDate()?->format('c'),
                    'weatherData' => $calendarEvent->getWeatherData() ? [
                        'weatherCode' => $calendarEvent->getWeatherData()->getDailyWeatherData()['weathercode'] ?? [],
                    ] : [],
                ] : null,
            ];
        };

        foreach ($otherGames as $game) {
            $ce = $game->getCalendarEvent();
            if (!$ce) {
                continue;
            }
            $start = $ce->getStartDate();
            $end = $ce->getEndDate();
            if ($start && $end && $now >= $start && $now <= $end) {
                $running[] = $serializeGame($game);
            } else {
                $gameEvents = [];
                foreach ($game->getGameEvents() as $gameEvent) {
                    $gameEvents[] = $gameEvent;
                }
                $scores = $this->collectScores($gameEvents, $game);
                $finished[] = [
                    'game' => $serializeGame($game),
                    'homeScore' => $scores['home'],
                    'awayScore' => $scores['away']
                ];
            }
        }

        $upcomingGamesArr = array_map($serializeGame, $upcomingGames);

        return $this->json([
            'running_games' => $running,
            'upcoming_games' => $upcomingGamesArr,
            'finished_games' => $finished,
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
        $gameEventOwnGoal = $this->entityManager->getRepository(GameEventType::class)->findOneBy(['code' => 'own_goal']);

        $homeScore = 0;
        $awayScore = 0;

        foreach ($gameEvents as $gameEvent) {
            if ($gameEvent->getGameEventType() === $gameEventGoal) {
                if ($gameEvent->getTeam() === $game->getHomeTeam()) {
                    ++$homeScore;
                } elseif ($gameEvent->getTeam() === $game->getAwayTeam()) {
                    ++$awayScore;
                }
            } elseif ($gameEvent->getGameEventType() === $gameEventOwnGoal) {
                if ($gameEvent->getTeam() === $game->getHomeTeam()) {
                    ++$awayScore;
                } elseif ($gameEvent->getTeam() === $game->getAwayTeam()) {
                    ++$homeScore;
                }
            }
        }

        return [
            'home' => $homeScore,
            'away' => $awayScore
        ];
    }

    /**
     * @param array<GameEvent> $gameEvents
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
     * @return array<array<Video>> $videos
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
