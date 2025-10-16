<?php

namespace App\Controller;

use App\Entity\Camera;
use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoType;
use App\Repository\CameraRepository;
use App\Repository\GameEventRepository;
use App\Repository\GameRepository;
use App\Repository\VideoRepository;
use App\Repository\VideoTypeRepository;
use App\Security\Voter\VideoVoter;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class VideosController extends AbstractController
{
    private int $youtubeLinkStartOffset = -60;

    #[Route('/videos/{gameId}', name: 'videos_list', methods: ['GET'])]
    public function details(
        int $gameId,
        GameEventRepository $gameEventRepository,
        GameRepository $gameRepository,
        VideoTypeRepository $videoTypeRepository,
        CameraRepository $cameraRepository
    ): JsonResponse {
        $game = $gameRepository->find($gameId);
        $gameEvents = $gameEventRepository->findAllGameEvents($game);
        if (!$game) {
            return new JsonResponse(['error' => 'Spiel nicht gefunden'], 404);
        }

        $rawVideos = $game->getVideos();
        $videos = [];
        /** @var Video $rawVideo */
        foreach ($rawVideos as $rawVideo) {
            $videos[$rawVideo->getSort()] = $rawVideo;
        }

        ksort($videos);

        $videoTypes = $videoTypeRepository->findAll();
        $cameras = $cameraRepository->findAll();
        $data = [];

        foreach ($videos as $video) {
            $data[] = [
                'id' => $video->getId(),
                'name' => $video->getName(),
                'url' => $video->getUrl(),
                'youtubeId' => $video->getYoutubeId(),
                'filePath' => $video->getFilePath(),
                'gameStart' => $video->getGameStart(),
                'sort' => $video->getSort(),
                'length' => $video->getLength(),
                'videoType' => [
                    'id' => $video->getVideoType()?->getId(),
                    'name' => $video->getVideoType()?->getName(),
                    'sort' => $video->getVideoType()?->getSort()
                ],
                'camera' => [
                    'id' => $video->getCamera()?->getId(),
                    'name' => $video->getCamera()?->getName()
                ],
                'createdAt' => $video->getCreatedAt()?->format('c'),
                'updatedAt' => $video->getUpdatedAt()?->format('c'),
            ];
        }

        return new JsonResponse([
            'videos' => $data,
            'youtubeLinks' => $this->prepareYoutubeLinks($game, $gameEvents),
            'videoTypes' => array_map(fn (VideoType $videoType) => [
                'id' => $videoType->getId(),
                'name' => $videoType->getName(),
            ], $videoTypes),
            'cameras' => array_map(fn (Camera $camera) => [
                'id' => $camera->getId(),
                'name' => $camera->getName(),
            ], $cameras)
        ]);
    }

    #[Route('/videos/save/{gameId}', name: 'videos_save', methods: ['POST'])]
    public function save(
        int $gameId,
        Request $request,
        GameRepository $gameRepository,
        VideoRepository $videoRepository,
        VideoTypeRepository $videoTypeRepository,
        CameraRepository $cameraRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        /** @var ?Game $game */
        $game = $gameRepository->find($gameId);
        if (null === $game) {
            return new JsonResponse(['error' => 'Spiel nicht gefunden'], 404);
        }

        /** @var User $user */
        $user = $this->getUser();

        $videoData = json_decode($request->getContent(), true);

        $videoId = $videoData['video_id'] ?? null;
        $name = $videoData['name'] ?? null;
        $url = $videoData['url'] ?? null;
        $filePath = $videoData['filePath'] ?? null;
        $gameStart = $videoData['gameStart'] ?? null;
        $videoTypeId = $videoData['videoType'] ?? null;
        $cameraId = $videoData['camera'] ?? null;
        $videoType = null;
        $sort = $videoData['sort'] ?? null;
        $length = $videoData['length'] ?? null;

        // YouTube-ID extrahieren
        $youtubeId = null;
        if (preg_match('~(?:youtu.be/|youtube.com/(?:embed/|v/|watch\?v=|watch\?.+&v=))([\w-]{11})~i', $url, $matches)) {
            $youtubeId = $matches[1];
        }

        if ($videoTypeId) {
            $videoType = $videoTypeRepository->find($videoTypeId);
        }

        if (!$videoType) {
            return new JsonResponse(['error' => 'VideoType nicht gefunden'], 404);
        }

        if ($videoId) {
            $video = $videoRepository->find($videoId);
            if (!$video) {
                return new JsonResponse(['error' => 'Video nicht gefunden'], 404);
            }
        } else {
            $video = new Video();
            $video->setCreatedAt(new DateTimeImmutable());
            $video->setCreatedFrom($user);
            $video->setGame($game);
        }

        if ($cameraId) {
            $camera = $cameraRepository->find($cameraId);
            if (!$camera) {
                return new JsonResponse(['error' => 'Kamera nicht gefunden'], 404);
            }
            $video->setCamera($camera);
        }

        $video->setName($name);
        $video->setUrl($url);
        $video->setYoutubeId($youtubeId);
        $video->setVideoType($videoType);
        $video->setFilePath($filePath);
        $video->setGameStart(null !== $gameStart && '' !== $gameStart ? (int) $gameStart : null);
        $video->setSort(null !== $sort && '' !== $sort ? (int) $sort : null);
        $video->setUpdatedAt(new DateTimeImmutable());
        $video->setUpdatedFrom($user);
        $video->setLength(null !== $length && '' !== $length ? (int) $length : null);

        $em->persist($video);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'video' => [
                'id' => $video->getId(),
                'name' => $video->getName(),
                'url' => $video->getUrl(),
                'youtubeId' => $video->getYoutubeId(),
                'filePath' => $video->getFilePath(),
                'gameStart' => $video->getGameStart(),
                'sort' => $video->getSort(),
                'videoType' => [
                    'name' => $video->getVideoType()?->getName(),
                    'id' => $video->getVideoType()?->getId(),
                    'sort' => $video->getVideoType()?->getSort()
                ],
                'length' => $video->getLength(),
                'createdAt' => $video->getCreatedAt()?->format('c'),
                'updatedAt' => $video->getUpdatedAt()?->format('c'),
            ]
        ]);
    }

    #[Route('/videos/delete/{id}', name: 'videos_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        Request $request,
        VideoRepository $videoRepository,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        $video = $videoRepository->find($id);
        if (!$video) {
            return new JsonResponse(['error' => 'Video nicht gefunden'], 404);
        }
        $em->remove($video);
        $em->flush();

        return new JsonResponse(['success' => true]);
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
    protected function orderVideos(Game $game): array
    {
        $videosEntries = $game->getVideos()->toArray();
        $videos = [];
        $cameras = [];

        foreach ($videosEntries as $videoEntry) {
            $cameras[(int) $videoEntry->getCamera()->getId()][(int) $videoEntry->getSort()] = $videoEntry;
        }

        foreach ($cameras as $cameraId => $currentVideos) {
            ksort($currentVideos);
            $cameras[$cameraId] = $currentVideos;
        }

        ksort($cameras);

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

    public function setYoutubeLinkStartOffset(int $offset): void
    {
        $this->youtubeLinkStartOffset = $offset;
    }
}
