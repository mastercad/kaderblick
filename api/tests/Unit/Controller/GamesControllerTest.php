<?php

namespace App\Tests\Unit\Controller;

use App\Controller\Api\GamesController;
use App\Entity\CalendarEvent;
use App\Entity\Camera;
use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\Video;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GamesControllerTest extends TestCase
{
    /**
     * Test für orderVideos mit gameStart Berücksichtigung.
     *
     * @param Video[]                       $videoEntries
     * @param array<int, array<int, Video>> $expectedVideos
     */
    #[DataProvider('orderVideosProvider')]
    public function testOrderVideosWithGameStart(
        array $videoEntries,
        array $expectedVideos
    ): void {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $controller = new GamesController($entityManager);

        $game = $this->createMock(Game::class);
        $game->method('getVideos')->willReturn(new \Doctrine\Common\Collections\ArrayCollection($videoEntries));

        $result = $this->invokeOrderVideos($controller, $game);

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
        $camera1 = new Camera();
        self::setPrivateProperty($camera1, 'id', 1);

        // Testfall 1: Video mit gameStart (Spielereinlauf) - nur effektive Spielzeit zählt
        $video1 = new Video();
        $video1->setCamera($camera1);
        $video1->setGameStart(359); // Spiel startet bei Sekunde 359
        $video1->setLength(1550); // Video ist 1550s lang
        $video1->setSort(1);

        $video2 = new Video();
        $video2->setCamera($camera1);
        $video2->setGameStart(null); // Kein gameStart - volle Länge
        $video2->setLength(1556);
        $video2->setSort(2);

        yield 'single_camera_with_gameStart_offset' => [
            [$video1, $video2],
            [
                1 => [
                    0 => $video1,      // Startet bei 0
                    1191 => $video2,   // Startet bei 1550 - 359 = 1191 (effektive Spielzeit)
                ]
            ]
        ];

        // Testfall 2: Mehrere Videos mit und ohne gameStart (simuliert 1. und 2. Halbzeit)
        $camera2 = new Camera();
        self::setPrivateProperty($camera2, 'id', 1);

        $firstHalf1 = new Video();
        $firstHalf1->setCamera($camera2);
        $firstHalf1->setGameStart(359);
        $firstHalf1->setLength(1550);
        $firstHalf1->setSort(1);

        $firstHalf2 = new Video();
        $firstHalf2->setCamera($camera2);
        $firstHalf2->setGameStart(null);
        $firstHalf2->setLength(1556);
        $firstHalf2->setSort(2);

        $firstHalf3 = new Video();
        $firstHalf3->setCamera($camera2);
        $firstHalf3->setGameStart(null);
        $firstHalf3->setLength(186);
        $firstHalf3->setSort(3);

        $secondHalf1 = new Video();
        $secondHalf1->setCamera($camera2);
        $secondHalf1->setGameStart(133); // 2. Halbzeit startet auch mit Vorlauf
        $secondHalf1->setLength(1560);
        $secondHalf1->setSort(4);

        $secondHalf2 = new Video();
        $secondHalf2->setCamera($camera2);
        $secondHalf2->setGameStart(null);
        $secondHalf2->setLength(1551);
        $secondHalf2->setSort(5);

        $secondHalf3 = new Video();
        $secondHalf3->setCamera($camera2);
        $secondHalf3->setGameStart(null);
        $secondHalf3->setLength(346);
        $secondHalf3->setSort(6);

        yield 'full_game_first_and_second_half' => [
            [$firstHalf1, $firstHalf2, $firstHalf3, $secondHalf1, $secondHalf2, $secondHalf3],
            [
                1 => [
                    0 => $firstHalf1,       // 0
                    1191 => $firstHalf2,    // 1550 - 359 = 1191
                    2747 => $firstHalf3,    // 1191 + 1556 = 2747
                    2933 => $secondHalf1,   // 2747 + 186 = 2933
                    4360 => $secondHalf2,   // 2933 + (1560 - 133) = 2933 + 1427 = 4360
                    5911 => $secondHalf3,   // 4360 + 1551 = 5911
                ]
            ]
        ];

        // Testfall 3: Video ohne gameStart (normale Videos)
        $camera3 = new Camera();
        self::setPrivateProperty($camera3, 'id', 1);

        $normalVideo1 = new Video();
        $normalVideo1->setCamera($camera3);
        $normalVideo1->setGameStart(null);
        $normalVideo1->setLength(1000);
        $normalVideo1->setSort(1);

        $normalVideo2 = new Video();
        $normalVideo2->setCamera($camera3);
        $normalVideo2->setGameStart(null);
        $normalVideo2->setLength(1000);
        $normalVideo2->setSort(2);

        yield 'videos_without_gameStart' => [
            [$normalVideo1, $normalVideo2],
            [
                1 => [
                    0 => $normalVideo1,
                    1000 => $normalVideo2,
                ]
            ]
        ];

        // Testfall 4: Mehrere Kameras gleichzeitig
        $cameraA = new Camera();
        self::setPrivateProperty($cameraA, 'id', 1);
        $cameraB = new Camera();
        self::setPrivateProperty($cameraB, 'id', 2);

        $cam1Video1 = new Video();
        $cam1Video1->setCamera($cameraA);
        $cam1Video1->setGameStart(300);
        $cam1Video1->setLength(1500);
        $cam1Video1->setSort(1);

        $cam1Video2 = new Video();
        $cam1Video2->setCamera($cameraA);
        $cam1Video2->setGameStart(null);
        $cam1Video2->setLength(1500);
        $cam1Video2->setSort(2);

        $cam2Video1 = new Video();
        $cam2Video1->setCamera($cameraB);
        $cam2Video1->setGameStart(200);
        $cam2Video1->setLength(1800);
        $cam2Video1->setSort(1);

        yield 'multiple_cameras' => [
            [$cam1Video1, $cam1Video2, $cam2Video1],
            [
                1 => [
                    0 => $cam1Video1,
                    1200 => $cam1Video2,  // 1500 - 300 = 1200
                ],
                2 => [
                    0 => $cam2Video1,
                ]
            ]
        ];
    }

    /**
     * Test für prepareYoutubeLinks mit korrekter gameStart Berücksichtigung.
     *
     * @param Video[]                          $videoEntries
     * @param GameEvent[]                      $events
     * @param array<int, array<int, string[]>> $expectedLinks
     */
    #[DataProvider('youtubeLinksProvider')]
    public function testPrepareYoutubeLinksWithGameStart(
        array $videoEntries,
        array $events,
        int $calendarStart,
        array $expectedLinks
    ): void {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $controller = $this->getMockBuilder(GamesController::class)
            ->setConstructorArgs([$entityManager])
            ->onlyMethods(['isGranted'])
            ->getMock();
        $controller->method('isGranted')->willReturn(true);

        $game = $this->createMock(Game::class);
        $calendarEvent = new CalendarEvent();
        $calendarEvent->setStartDate((new DateTimeImmutable())->setTimestamp($calendarStart));
        $game->method('getCalendarEvent')->willReturn($calendarEvent);
        $game->method('getVideos')->willReturn(new \Doctrine\Common\Collections\ArrayCollection($videoEntries));

        $result = $this->invokePrepareYoutubeLinks($controller, $game, $events);

        $this->assertEquals($expectedLinks, $result);
    }

    public static function youtubeLinksProvider(): Generator
    {
        $camera1 = new Camera();
        self::setPrivateProperty($camera1, 'id', 1);

        // Testfall: Real-World Szenario - Events in 1. und 2. Halbzeit
        $video45 = new Video();
        $video45->setCamera($camera1);
        $video45->setGameStart(359);
        $video45->setLength(1550);
        $video45->setUrl('https://youtu.be/yE4GwJ3zTvE');

        $video46 = new Video();
        $video46->setCamera($camera1);
        $video46->setGameStart(null);
        $video46->setLength(1556);
        $video46->setUrl('https://youtu.be/-hY82oPu4eU');
        $video46->setSort(2);

        $video47 = new Video();
        $video47->setCamera($camera1);
        $video47->setGameStart(null);
        $video47->setLength(186);
        $video47->setUrl('https://youtu.be/eOEOiZorjV8');
        $video47->setSort(3);

        $video48 = new Video();
        $video48->setCamera($camera1);
        $video48->setGameStart(133);
        $video48->setLength(1560);
        $video48->setUrl('https://youtu.be/OLnoG-Og6sI');
        $video48->setSort(4);

        $video49 = new Video();
        $video49->setCamera($camera1);
        $video49->setGameStart(null);
        $video49->setLength(1551);
        $video49->setUrl('https://youtu.be/lIhsIl7SDWw');
        $video49->setSort(5);

        $video50 = new Video();
        $video50->setCamera($camera1);
        $video50->setGameStart(null);
        $video50->setLength(346);
        $video50->setUrl('https://youtu.be/rInbNPXchJI');
        $video50->setSort(6);

        // Game start: 2025-11-15 11:00:00
        $gameStartTimestamp = (new DateTimeImmutable('2025-11-15 11:00:00'))->getTimestamp();

        // Events - EXAKTE Zeitstempel aus deinen Daten
        $event134 = new GameEvent();
        self::setPrivateProperty($event134, 'id', 134);
        $event134->setTimestamp(new DateTimeImmutable('2025-11-15 11:01:10')); // 70s nach Start

        $event128 = new GameEvent();
        self::setPrivateProperty($event128, 'id', 128);
        $event128->setTimestamp(new DateTimeImmutable('2025-11-15 11:29:00')); // 1740s nach Start

        $event129 = new GameEvent();
        self::setPrivateProperty($event129, 'id', 129);
        $event129->setTimestamp(new DateTimeImmutable('2025-11-15 11:35:00')); // 2100s nach Start

        $event130 = new GameEvent();
        self::setPrivateProperty($event130, 'id', 130);
        $event130->setTimestamp(new DateTimeImmutable('2025-11-15 11:45:00')); // 2700s nach Start

        $event131 = new GameEvent();
        self::setPrivateProperty($event131, 'id', 131);
        $event131->setTimestamp(new DateTimeImmutable('2025-11-15 11:58:00')); // 3480s nach Start

        $event132 = new GameEvent();
        self::setPrivateProperty($event132, 'id', 132);
        $event132->setTimestamp(new DateTimeImmutable('2025-11-15 12:11:00')); // 4260s nach Start

        $event133 = new GameEvent();
        self::setPrivateProperty($event133, 'id', 133);
        $event133->setTimestamp(new DateTimeImmutable('2025-11-15 12:11:00')); // 4260s nach Start

        $event135 = new GameEvent();
        self::setPrivateProperty($event135, 'id', 135);
        $event135->setTimestamp(new DateTimeImmutable('2025-11-15 12:20:00')); // 4800s nach Start

        $event136 = new GameEvent();
        self::setPrivateProperty($event136, 'id', 136);
        $event136->setTimestamp(new DateTimeImmutable('2025-11-15 12:20:00')); // 4800s nach Start

        $event137 = new GameEvent();
        self::setPrivateProperty($event137, 'id', 137);
        $event137->setTimestamp(new DateTimeImmutable('2025-11-15 12:20:00')); // 4800s nach Start

        $event138 = new GameEvent();
        self::setPrivateProperty($event138, 'id', 138);
        $event138->setTimestamp(new DateTimeImmutable('2025-11-15 12:20:00')); // 4800s nach Start

        yield 'real_world_scenario_with_late_events' => [
            [$video45, $video46, $video47, $video48, $video49, $video50],
            [$event134, $event128, $event129, $event130, $event131, $event132, $event133, $event135, $event136, $event137, $event138],
            $gameStartTimestamp,
            [
                134 => [
                    1 => ['https://youtu.be/yE4GwJ3zTvE&t=369s'] // 70 + 359 - 60 = 369
                ],
                128 => [
                    1 => ['https://youtu.be/-hY82oPu4eU&t=489s'] // 1740 - 1191 - 60 = 489
                ],
                129 => [
                    1 => ['https://youtu.be/-hY82oPu4eU&t=849s'] // 2100 - 1191 - 60 = 849
                ],
                130 => [
                    1 => ['https://youtu.be/-hY82oPu4eU&t=1449s'] // 2700 - 1191 - 60 = 1449
                ],
                131 => [
                    1 => ['https://youtu.be/OLnoG-Og6sI&t=620s'] // 3480 - 2933 + 133 - 60 = 620
                ],
                132 => [
                    1 => ['https://youtu.be/OLnoG-Og6sI&t=1400s'] // 4260 - 2933 + 133 - 60 = 1400
                ],
                133 => [
                    1 => ['https://youtu.be/OLnoG-Og6sI&t=1400s'] // 4260 - 2933 + 133 - 60 = 1400
                ],
                135 => [
                    1 => ['https://youtu.be/lIhsIl7SDWw&t=380s'] // 4800 - 4360 - 60 = 380
                ],
                136 => [
                    1 => ['https://youtu.be/lIhsIl7SDWw&t=380s'] // 4800 - 4360 - 60 = 380
                ],
                137 => [
                    1 => ['https://youtu.be/lIhsIl7SDWw&t=380s'] // 4800 - 4360 - 60 = 380
                ],
                138 => [
                    1 => ['https://youtu.be/lIhsIl7SDWw&t=380s'] // 4800 - 4360 - 60 = 380
                ],
            ]
        ];

        // Testfall: Event genau beim gameStart
        $videoWithStart = new Video();
        $videoWithStart->setCamera($camera1);
        $videoWithStart->setGameStart(300);
        $videoWithStart->setLength(1000);
        $videoWithStart->setUrl('https://youtu.be/testVideo');
        $videoWithStart->setSort(1);

        $eventAtStart = new GameEvent();
        self::setPrivateProperty($eventAtStart, 'id', 99);
        $eventAtStart->setTimestamp(new DateTimeImmutable('2025-11-15 11:00:00'));

        yield 'event_at_game_start_with_gamestart_offset' => [
            [$videoWithStart],
            [$eventAtStart],
            (new DateTimeImmutable('2025-11-15 11:00:00'))->getTimestamp(),
            [
                99 => [
                    1 => ['https://youtu.be/testVideo&t=240s'] // 0s - 60s offset + 300s gameStart
                ]
            ]
        ];

        // Testfall: Events am Ende des Videos (Edge-Case für 80. Minute)
        $camera2 = new Camera();
        self::setPrivateProperty($camera2, 'id', 1);

        $lateVideo1 = new Video();
        $lateVideo1->setCamera($camera2);
        $lateVideo1->setGameStart(133);
        $lateVideo1->setLength(1560);
        $lateVideo1->setUrl('https://youtu.be/video1');
        $lateVideo1->setSort(1);

        $lateVideo2 = new Video();
        $lateVideo2->setCamera($camera2);
        $lateVideo2->setGameStart(null);
        $lateVideo2->setLength(1551);
        $lateVideo2->setUrl('https://youtu.be/video2');
        $lateVideo2->setSort(2);

        $lateVideo3 = new Video();
        $lateVideo3->setCamera($camera2);
        $lateVideo3->setGameStart(null);
        $lateVideo3->setLength(346);
        $lateVideo3->setUrl('https://youtu.be/video3');
        $lateVideo3->setSort(3);

        // Timeline: video1: 0-1427, video2: 1427-2978, video3: 2978-3324
        $eventAt1500 = new GameEvent();
        self::setPrivateProperty($eventAt1500, 'id', 101);
        $eventAt1500->setTimestamp((new DateTimeImmutable('2025-11-15 11:00:00'))->modify('+1500 seconds'));

        $eventAt2900 = new GameEvent();
        self::setPrivateProperty($eventAt2900, 'id', 102);
        $eventAt2900->setTimestamp((new DateTimeImmutable('2025-11-15 11:00:00'))->modify('+2900 seconds'));

        $eventAt3300 = new GameEvent();
        self::setPrivateProperty($eventAt3300, 'id', 103);
        $eventAt3300->setTimestamp((new DateTimeImmutable('2025-11-15 11:00:00'))->modify('+3300 seconds'));

        yield 'events_at_end_of_videos_late_game' => [
            [$lateVideo1, $lateVideo2, $lateVideo3],
            [$eventAt1500, $eventAt2900, $eventAt3300],
            (new DateTimeImmutable('2025-11-15 11:00:00'))->getTimestamp(),
            [
                101 => [
                    1 => ['https://youtu.be/video2&t=13s'] // (1500 - 1427) - 60 = 13s
                ],
                102 => [
                    1 => ['https://youtu.be/video2&t=1413s'] // (2900 - 1427) - 60 = 1413s
                ],
                103 => [
                    1 => ['https://youtu.be/video3&t=262s'] // (3300 - 2978) - 60 = 262s
                ],
            ]
        ];

        // Testfall: Event außerhalb aller Videos
        $singleVideo = new Video();
        $singleVideo->setCamera($camera1);
        $singleVideo->setGameStart(0);
        $singleVideo->setLength(1000);
        $singleVideo->setUrl('https://youtu.be/single');
        $singleVideo->setSort(1);

        $eventOutsideVideo = new GameEvent();
        self::setPrivateProperty($eventOutsideVideo, 'id', 200);
        $eventOutsideVideo->setTimestamp((new DateTimeImmutable('2025-11-15 11:00:00'))->modify('+2000 seconds'));

        yield 'event_outside_all_videos' => [
            [$singleVideo],
            [$eventOutsideVideo],
            (new DateTimeImmutable('2025-11-15 11:00:00'))->getTimestamp(),
            [], // Keine Links erwartet
        ];

        // Testfall: Grenzen - Event genau am Video-Ende
        $boundaryVideo = new Video();
        $boundaryVideo->setCamera($camera1);
        $boundaryVideo->setGameStart(100);
        $boundaryVideo->setLength(1000);
        $boundaryVideo->setUrl('https://youtu.be/boundary');
        $boundaryVideo->setSort(1);

        // Event genau am Ende: 900s (1000 - 100)
        $eventAtEnd = new GameEvent();
        self::setPrivateProperty($eventAtEnd, 'id', 300);
        $eventAtEnd->setTimestamp((new DateTimeImmutable('2025-11-15 11:00:00'))->modify('+900 seconds'));

        yield 'event_at_exact_video_end' => [
            [$boundaryVideo],
            [$eventAtEnd],
            (new DateTimeImmutable('2025-11-15 11:00:00'))->getTimestamp(),
            [
                300 => [
                    1 => ['https://youtu.be/boundary&t=940s'] // 900 - 0 + 100 - 60 = 940s
                ]
            ]
        ];
    }

    /**
     * @return array<int, array<int, Video>>
     */
    private function invokeOrderVideos(GamesController $controller, Game $game): array
    {
        $ref = new ReflectionClass($controller);
        $method = $ref->getMethod('orderVideos');
        $method->setAccessible(true);

        return $method->invoke($controller, $game);
    }

    /**
     * @param GameEvent[] $events
     *
     * @return array<int, array<int, list<string>>>
     */
    private function invokePrepareYoutubeLinks(AbstractController $controller, Game $game, array $events): array
    {
        $ref = new ReflectionClass($controller);
        $method = $ref->getMethod('prepareYoutubeLinks');
        $method->setAccessible(true);

        return $method->invoke($controller, $game, $events);
    }

    private static function setPrivateProperty(object $object, string $propertyName, mixed $value): void
    {
        $ref = new ReflectionClass($object);
        $property = $ref->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
