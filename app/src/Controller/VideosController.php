<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\Video;
use App\Repository\CameraRepository;
use App\Repository\GameRepository;
use App\Repository\VideoRepository;
use App\Repository\VideoTypeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class VideosController extends AbstractController
{
    #[Route('/videos/{gameId}', name: 'videos_list', methods: ['GET'])]
    public function list(int $gameId, GameRepository $gameRepository, VideoTypeRepository $videoTypeRepository, CameraRepository $cameraRepository): JsonResponse
    {
        $game = $gameRepository->find($gameId);
        if (!$game) {
            return new JsonResponse(['error' => 'Spiel nicht gefunden'], 404);
        }

        $videos = $game->getVideos();
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
                'createdAt' => $video->getCreatedAt()?->format('c'),
                'updatedAt' => $video->getUpdatedAt()?->format('c'),
            ];
        }

        return new JsonResponse([
            'videos' => $data,
            'videoTypes' => $videoTypes,
            'cameras' => $cameras
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

        $videoId = $request->request->get('video_id');
        $name = $request->request->get('name');
        $url = $request->request->get('url');
        $filePath = $request->request->get('filePath');
        $gameStart = $request->request->get('gameStart');
        $videoTypeId = $request->request->get('videoType');
        $cameraId = $request->request->get('camera');
        $videoType = null;
        $sort = $request->request->get('sort');
        $length = $request->request->get('length');

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

    #[Route('/videos/delete/{id}', name: 'videos_delete', methods: ['POST'])]
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
        $token = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete' . $video->getId(), $token))) {
            return new JsonResponse(['error' => 'Ungültiges CSRF-Token'], 403);
        }
        $em->remove($video);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}
