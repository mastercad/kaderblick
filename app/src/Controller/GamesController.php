<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\Video;
use App\Repository\CameraRepository;
use App\Repository\GameRepository;
use App\Repository\VideoTypeRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/games', name: 'games_')]
class GamesController extends AbstractController
{
    private int $youtubeLinkStartOffset = -60;

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->createQueryBuilder('g')
            ->leftJoin('g.calendarEvent', 'ce')
            ->leftJoin('ce.calendarEventType', 'cet')
            ->addSelect('ce', 'cet')
            ->where('cet.name = :spiel')
            ->setParameter('spiel', 'Spiel')
            ->orderBy('ce.startDate', 'DESC')
            ->getQuery()
            ->getResult();

        $now = new DateTimeImmutable();
        $running = [];
        $upcoming = [];
        $finished = [];

        foreach ($games as $game) {
            $ce = $game->getCalendarEvent();
            if (!$ce) {
                continue;
            }
            $start = $ce->getStartDate();
            $end = $ce->getEndDate();
            if ($start && $end && $now >= $start && $now <= $end) {
                $running[] = $game;
            } elseif ($start && $now < $start) {
                $upcoming[] = $game;
            } else {
                $finished[] = $game;
            }
        }

        return $this->render('games/index.html.twig', [
            'running_games' => $running,
            'upcoming_games' => $upcoming,
            'finished_games' => $finished,
        ]);
    }

    #[Route(path: '/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Game $game, VideoTypeRepository $videoTypeRepository, CameraRepository $cameraRepository): Response
    {
        $calendarEvent = $game->getCalendarEvent();
        $gameEvents = $game->getGameEvents();
        $cameras = [];

        foreach ($cameraRepository->findAll() as $camera) {
            $cameras[$camera->getId()] = $camera;
        }
        ksort($cameras);

        return $this->render('games/show.html.twig', [
            'game' => $game,
            'calendarEvent' => $calendarEvent,
            'gameEvents' => $gameEvents,
            'videoTypes' => $videoTypeRepository->findAll(),
            'videos' => $this->prepareYoutubeLinks($game, $gameEvents),
            'cameras' => $cameras
        ]);
    }

    /**
     * @param Collection<int, GameEvent> $gameEvents
     *
     * @return array<int, array<int, list<string>>> $youtubeLinks
     */
    private function prepareYoutubeLinks(Game $game, Collection $gameEvents): array
    {
        $youtubeLinks = [];
        $videos = $this->orderVideos($game);

        $startTimestamp = $game->getCalendarEvent()?->getStartDate()?->getTimestamp();

        foreach ($gameEvents as $event) {
            $eventSeconds = ($event->getTimestamp()->getTimestamp() - $startTimestamp);

            foreach ($videos as $cameraId => $currentVideos) {
                $elapsedTime = 0;
                foreach ($currentVideos as $startTime => $video) {
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
