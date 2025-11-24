<?php

namespace App\Tests\Unit\Service;

use App\Entity\CalendarEvent;
use App\Entity\Camera;
use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\Video;
use App\Service\VideoTimelineService;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;

class VideoTimelineServiceTest extends TestCase
{
    private Security $security;
    private VideoTimelineService $service;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->security->method('isGranted')->willReturn(true);
        $this->service = new VideoTimelineService($this->security, -60);
    }

    /**
     * Test orderVideos - Sortiert Videos nach Kamera und erstellt Timeline.
     *
     * @param Video[]                       $videoEntries
     * @param array<int, array<int, Video>> $expectedVideos
     */
    #[DataProvider('orderVideosProvider')]
    public function testOrderVideos(
        array $videoEntries,
        array $expectedVideos
    ): void {
        $game = $this->createMock(Game::class);
        $game->method('getVideos')->willReturn(new ArrayCollection($videoEntries));

        $result = $this->service->orderVideos($game);

        foreach ($expectedVideos as $cameraId => $expectedCameraVideos) {
            $this->assertArrayHasKey($cameraId, $result, "Camera $cameraId should be present in result");

            foreach ($expectedCameraVideos as $startTime => $video) {
                $this->assertArrayHasKey($startTime, $result[$cameraId], "Start time $startTime should be present for camera $cameraId");
                $this->assertSame($video, $result[$cameraId][$startTime], "Video at start time $startTime for camera $cameraId should match");
            }
        }
    }

    public static function orderVideosProvider(): Generator
    {
        // Szenario 1: Eine Kamera, zwei Videos mit gameStart
        $camera1 = self::createCamera(1, 'DJI Osmo Action 5');
        $video1 = self::createVideo(1, $camera1, 1, 1550, 359);
        $video2 = self::createVideo(2, $camera1, 2, 1556, null);

        yield 'single_camera_two_videos_with_gameStart' => [
            [$video1, $video2],
            [1 => [0 => $video1, 1550 => $video2]]
        ];

        // Szenario 2: Mehrere Kameras mit unterschiedlichen Aufnahmen
        $camera2 = self::createCamera(2, 'GoPro Hero 11');
        $video3 = self::createVideo(3, $camera2, 1, 2700, 900); // Beginnt 15 Min später
        $video4 = self::createVideo(4, $camera1, 3, 186, null);

        yield 'multiple_cameras_different_timings' => [
            [$video1, $video2, $video3, $video4],
            [
                1 => [0 => $video1, 1550 => $video2, 3106 => $video4],
                2 => [0 => $video3]
            ]
        ];

        // Szenario 3: Vollständiges Spiel (6 Videos wie im echten Beispiel)
        $video5 = self::createVideo(11, $camera1, 1, 1550, 359);
        $video6 = self::createVideo(12, $camera1, 2, 1556, null);
        $video7 = self::createVideo(13, $camera1, 3, 186, null);
        $video8 = self::createVideo(14, $camera1, 4, 1560, 133);
        $video9 = self::createVideo(15, $camera1, 5, 1551, null);
        $video10 = self::createVideo(16, $camera1, 6, 346, null);

        yield 'full_game_six_videos' => [
            [$video5, $video6, $video7, $video8, $video9, $video10],
            [
                1 => [
                    0 => $video5,
                    1550 => $video6,
                    3106 => $video7,
                    3292 => $video8,
                    4852 => $video9,
                    6403 => $video10
                ]
            ]
        ];

        // Szenario 4: Drei Kameras mit überlappenden Zeiten
        $camera3 = self::createCamera(3, 'iPhone 14 Pro');
        $videoA = self::createVideo(20, $camera1, 1, 2700, 300);
        $videoB = self::createVideo(21, $camera2, 1, 2400, 600);
        $videoC = self::createVideo(22, $camera3, 1, 1800, 1200);

        yield 'three_cameras_overlapping' => [
            [$videoA, $videoB, $videoC],
            [
                1 => [0 => $videoA],
                2 => [0 => $videoB],
                3 => [0 => $videoC]
            ]
        ];
    }

    /**
     * Test prepareYoutubeLinks - Berechnet YouTube-Links für GameEvents.
     *
     * @param Video[]                              $videoEntries
     * @param GameEvent[]                          $gameEvents
     * @param array<int, array<int, list<string>>> $expectedLinks
     */
    #[DataProvider('youtubeLinksProvider')]
    public function testPrepareYoutubeLinks(
        array $videoEntries,
        array $gameEvents,
        int $gameStartTimestamp,
        array $expectedLinks
    ): void {
        $game = $this->createMock(Game::class);
        $game->method('getVideos')->willReturn(new ArrayCollection($videoEntries));

        $calendarEvent = $this->createMock(CalendarEvent::class);
        $calendarEvent->method('getStartDate')
            ->willReturn(new DateTimeImmutable('@' . $gameStartTimestamp));
        $game->method('getCalendarEvent')->willReturn($calendarEvent);

        $result = $this->service->prepareYoutubeLinks($game, $gameEvents);

        $this->assertEquals($expectedLinks, $result);
    }

    public static function youtubeLinksProvider(): Generator
    {
        $camera1 = self::createCamera(1, 'DJI Osmo Action 5');
        $gameStartTimestamp = 1731146400; // 2025-11-09 10:00:00

        // Szenario 1: Echtes Beispiel - Event #28 bei 10:29:00 (29 Minuten = 1740s)
        $video1 = self::createVideo(11, $camera1, 1, 1550, 359, 'https://youtu.be/yE4GwJ3zTvE');
        $video2 = self::createVideo(12, $camera1, 2, 1556, null, 'https://youtu.be/-hY82oPu4eU');
        $video3 = self::createVideo(13, $camera1, 3, 186, null, 'https://youtu.be/eOEOiZorjV8');

        $event1 = self::createGameEvent(27, $gameStartTimestamp + 60); // 10:01:00 = 60s
        $event2 = self::createGameEvent(28, $gameStartTimestamp + 1740); // 10:29:00 = 1740s
        $event3 = self::createGameEvent(29, $gameStartTimestamp + 2100); // 10:35:00 = 2100s

        yield 'real_world_scenario_first_half' => [
            [$video1, $video2, $video3],
            [$event1, $event2, $event3],
            $gameStartTimestamp,
            [
                27 => [1 => ['https://youtu.be/yE4GwJ3zTvE&t=359s']], // gameStart(359) + 60 - 60 = 359s
                28 => [1 => ['https://youtu.be/-hY82oPu4eU&t=489s']], // 0 + (1740-1191) - 60 = 489s
                29 => [1 => ['https://youtu.be/-hY82oPu4eU&t=849s']] // 0 + (2100-1191) - 60 = 849s
            ]
        ];

        // Szenario 2: Zweite Halbzeit mit neuem gameStart
        $video4 = self::createVideo(14, $camera1, 4, 1560, 133, 'https://youtu.be/OLnoG-Og6sI');
        $video5 = self::createVideo(15, $camera1, 5, 1551, null, 'https://youtu.be/lIhsIl7SDWw');

        $event4 = self::createGameEvent(31, $gameStartTimestamp + 3480); // 10:58:00 = 3480s (58 Min)
        $event5 = self::createGameEvent(32, $gameStartTimestamp + 4260); // 11:11:00 = 4260s (71 Min)

        yield 'real_world_scenario_second_half' => [
            [$video1, $video2, $video3, $video4, $video5],
            [$event4, $event5],
            $gameStartTimestamp,
            [
                31 => [1 => ['https://youtu.be/OLnoG-Og6sI&t=620s']], // 133 + (3480-2933) - 60 = 620s
                32 => [1 => ['https://youtu.be/OLnoG-Og6sI&t=1400s']] // 133 + (4260-2933) - 60 = 1400s
            ]
        ];

        // Szenario 3: Mehrere Kameras, gleiches Event
        $camera2 = self::createCamera(2, 'GoPro');
        $videoA1 = self::createVideo(30, $camera1, 1, 2700, 300, 'https://youtu.be/cam1vid1');
        $videoA2 = self::createVideo(31, $camera2, 1, 2700, 300, 'https://youtu.be/cam2vid1');

        $event6 = self::createGameEvent(40, $gameStartTimestamp + 1200); // 20 Minuten

        yield 'multiple_cameras_same_event' => [
            [$videoA1, $videoA2],
            [$event6],
            $gameStartTimestamp,
            [
                40 => [
                    1 => ['https://youtu.be/cam1vid1&t=1440s'], // 300 + 1200 - 60 = 1440s
                    2 => ['https://youtu.be/cam2vid1&t=1440s']
                ]
            ]
        ];

        // Szenario 4: Event außerhalb aller Videos (keine Links)
        $videoB1 = self::createVideo(50, $camera1, 1, 1000, 300, 'https://youtu.be/short');
        $event7 = self::createGameEvent(50, $gameStartTimestamp + 2000); // Event nach Video-Ende

        yield 'event_outside_video_coverage' => [
            [$videoB1],
            [$event7],
            $gameStartTimestamp,
            [] // Kein Link, da Event außerhalb
        ];

        // Szenario 5: Kamera beginnt später (gameStart=900s = 15 Min)
        $videoC1 = self::createVideo(60, $camera1, 1, 2700, 0, 'https://youtu.be/cam1early');
        $videoC2 = self::createVideo(61, $camera2, 1, 2700, 900, 'https://youtu.be/cam2late');

        $event8 = self::createGameEvent(60, $gameStartTimestamp + 600); // 10 Min (vor Kamera2)
        $event9 = self::createGameEvent(61, $gameStartTimestamp + 1800); // 30 Min (beide Kameras)

        yield 'camera_starting_late' => [
            [$videoC1, $videoC2],
            [$event8, $event9],
            $gameStartTimestamp,
            [
                60 => [
                    1 => ['https://youtu.be/cam1early&t=540s'], // 0 + 600 - 60 = 540s
                    2 => ['https://youtu.be/cam2late&t=1440s']  // 900 + 600 - 60 = 1440s
                ],
                61 => [
                    1 => ['https://youtu.be/cam1early&t=1740s'], // 0 + 1800 - 60 = 1740s
                    2 => ['https://youtu.be/cam2late&t=2640s']   // 900 + 1800 - 60 = 2640s
                ]
            ]
        ];

        // Szenario 6: Event genau am Videostart (mit gameStart)
        $videoD1 = self::createVideo(70, $camera1, 1, 1800, 300, 'https://youtu.be/exactstart');
        $event10 = self::createGameEvent(70, $gameStartTimestamp + 300); // Genau bei gameStart

        yield 'event_at_exact_gameStart' => [
            [$videoD1],
            [$event10],
            $gameStartTimestamp,
            [
                70 => [1 => ['https://youtu.be/exactstart&t=540s']] // 300 + 300 - 60 = 540s
            ]
        ];

        // Szenario 7: Event am Video-Ende
        $videoE1 = self::createVideo(80, $camera1, 1, 1000, 200, 'https://youtu.be/endvid');
        $event11 = self::createGameEvent(80, $gameStartTimestamp + 800); // 800s Spielzeit (innerhalb 0-800s Range)

        yield 'event_at_video_end' => [
            [$videoE1],
            [$event11],
            $gameStartTimestamp,
            [
                80 => [1 => ['https://youtu.be/endvid&t=940s']] // 200 + 800 - 60 = 940s
            ]
        ];

        // Szenario 8: Mehrere Events im selben Video
        $videoF1 = self::createVideo(90, $camera1, 1, 3000, 300, 'https://youtu.be/multipleevents');
        $event12 = self::createGameEvent(90, $gameStartTimestamp + 600);
        $event13 = self::createGameEvent(91, $gameStartTimestamp + 1200);
        $event14 = self::createGameEvent(92, $gameStartTimestamp + 1800);

        yield 'multiple_events_same_video' => [
            [$videoF1],
            [$event12, $event13, $event14],
            $gameStartTimestamp,
            [
                90 => [1 => ['https://youtu.be/multipleevents&t=840s']],   // 300 + 600 - 60 = 840s
                91 => [1 => ['https://youtu.be/multipleevents&t=1440s']],  // 300 + 1200 - 60 = 1440s
                92 => [1 => ['https://youtu.be/multipleevents&t=2040s']]   // 300 + 1800 - 60 = 2040s
            ]
        ];
    }

    /**
     * Test mit verschiedenen Offset-Werten.
     */
    #[DataProvider('offsetProvider')]
    public function testDifferentOffsets(int $offset, int $expectedSeconds): void
    {
        $service = new VideoTimelineService($this->security, $offset);

        $camera1 = self::createCamera(1, 'Test Camera');
        $video1 = self::createVideo(1, $camera1, 1, 1800, 300, 'https://youtu.be/test');

        $game = $this->createMock(Game::class);
        $game->method('getVideos')->willReturn(new ArrayCollection([$video1]));

        $calendarEvent = $this->createMock(CalendarEvent::class);
        $calendarEvent->method('getStartDate')
            ->willReturn(new DateTimeImmutable('@1000'));
        $game->method('getCalendarEvent')->willReturn($calendarEvent);

        $event = self::createGameEvent(1, 1600); // 600s Spielzeit (300s gameStart + 300s ins Spiel)

        $result = $service->prepareYoutubeLinks($game, [$event]);

        $this->assertArrayHasKey(1, $result);
        $this->assertStringContainsString("&t={$expectedSeconds}s", $result[1][1][0]);
    }

    public static function offsetProvider(): Generator
    {
        yield 'offset_minus_60' => [-60, 840]; // 300 (gameStart) + 600 (Spielzeit) - 60 = 840s
        yield 'offset_minus_30' => [-30, 870]; // 300 + 600 - 30 = 870s
        yield 'offset_0' => [0, 900]; // 300 + 600 - 0 = 900s
        yield 'offset_plus_10' => [10, 910]; // 300 + 600 + 10 = 910s
    }

    private static function createCamera(int $id, string $name): Camera
    {
        $camera = new Camera();
        $reflection = new ReflectionClass($camera);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($camera, $id);
        $camera->setName($name);

        return $camera;
    }

    private static function createVideo(
        int $id,
        Camera $camera,
        int $sort,
        int $length,
        ?int $gameStart,
        string $url = 'https://youtu.be/test'
    ): Video {
        $video = new Video();
        $reflection = new ReflectionClass($video);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($video, $id);

        $video->setCamera($camera);
        $video->setSort($sort);
        $video->setLength($length);
        $video->setGameStart($gameStart);
        $video->setUrl($url);

        return $video;
    }

    private static function createGameEvent(int $id, int $timestamp): GameEvent
    {
        $event = new GameEvent();
        $reflection = new ReflectionClass($event);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($event, $id);

        $event->setTimestamp(new DateTimeImmutable('@' . $timestamp));

        return $event;
    }
}
