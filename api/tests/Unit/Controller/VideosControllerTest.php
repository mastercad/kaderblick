<?php

namespace App\Tests\Unit\Controller;

use App\Controller\VideosController;
use App\Entity\CalendarEvent;
use App\Entity\Camera;
use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\Video;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VideosControllerTest extends TestCase
{
    /**
     * @param array<int, Video[]>            $videos
     * @param GameEvent[]                    $events
     * @param array<int, array<int, string>> $expectedLinks
     */
    #[DataProvider('videoEventProvider')]
    public function testYoutubeLinksForMultipleVideos(
        array $videos,
        array $events,
        int $calendarStart,
        int $offset,
        array $expectedLinks
    ): void {
        $controller = $this->getMockBuilder(VideosController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isGranted', 'orderVideos'])
            ->getMock();
        $controller->method('isGranted')->willReturn(true);
        $controller->setYoutubeLinkStartOffset($offset);

        $game = new Game();
        $calendarEvent = new CalendarEvent();
        $calendarEvent->setStartDate((new DateTimeImmutable())->setTimestamp($calendarStart));
        $game->setCalendarEvent($calendarEvent);

        $controller->method('orderVideos')->willReturn($videos);

        $result = $this->invokePrepareYoutubeLinks($controller, $game, $events);
        $this->assertEquals($expectedLinks, $result);
    }

    public static function videoEventProvider(): Generator
    {
        $camera1 = new Camera();
        self::invokePrivateMember($camera1, 'id', 1);
        $camera2 = new Camera();
        self::invokePrivateMember($camera2, 'id', 2);

        $videoA = new Video();
        $videoA->setCamera($camera1);
        $videoA->setGameStart(0);
        $videoA->setLength(100);
        $videoA->setUrl('https://youtube.com/watch?v=vidA');

        $videoB = new Video();
        $videoB->setCamera($camera1);
        $videoB->setGameStart(100);
        $videoB->setLength(100);
        $videoB->setUrl('https://youtube.com/watch?v=vidB');
        $videoB->setSort(2);

        $videoC = new Video();
        $videoC->setCamera($camera2);
        $videoC->setGameStart(0);
        $videoC->setLength(200);
        $videoC->setUrl('https://youtube.com/watch?v=vidC');
        $videoC->setSort(1);

        // Events
        $event1 = new GameEvent();
        self::invokePrivateMember($event1, 'id', 1);
        $event1->setTimestamp((new DateTimeImmutable())->setTimestamp(1010)); // 10s nach Start
        $event2 = new GameEvent();
        self::invokePrivateMember($event2, 'id', 2);
        $event2->setTimestamp((new DateTimeImmutable())->setTimestamp(1100)); // 100s nach Start
        $event3 = new GameEvent();
        self::invokePrivateMember($event3, 'id', 3);
        $event3->setTimestamp((new DateTimeImmutable())->setTimestamp(1200)); // 200s nach Start

        // Testfall: Events in mehreren Videos, Offset 0
        yield 'events_in_multiple_videos_offset0' => [
            [1 => [0 => $videoA, 100 => $videoB], 2 => [0 => $videoC]],
            [$event1, $event2, $event3],
            1000,
            0,
            [
                1 => [
                    1 => [
                        'https://youtube.com/watch?v=vidA&t=10s'
                    ],
                    2 => [
                        'https://youtube.com/watch?v=vidC&t=10s'
                    ]
                ],
                2 => [
                    1 => [
                        'https://youtube.com/watch?v=vidB&t=0s'
                    ],
                    2 => [
                        'https://youtube.com/watch?v=vidC&t=100s'
                    ]
                ],
            ],
        ];

        // Testfall: Event in VideoA, positiver Offset
        $event4 = new GameEvent();
        self::invokePrivateMember($event4, 'id', 4);
        $event4->setTimestamp((new DateTimeImmutable())->setTimestamp(1015)); // 15s nach Start
        yield 'event_in_videoA_positive_offset' => [
            [1 => [0 => $videoA]],
            [$event4],
            1000,
            10,
            [4 => [1 => ['https://youtube.com/watch?v=vidA&t=25s']]], // 15s + 10 Offset
        ];

        // Testfall: Event in VideoA, negativer Offset
        $event5 = new GameEvent();
        self::invokePrivateMember($event5, 'id', 5);
        $event5->setTimestamp((new DateTimeImmutable())->setTimestamp(1015)); // 15s nach Start
        yield 'event_in_videoA_negative_offset' => [
            [1 => [0 => $videoA]],
            [$event5],
            1000,
            -5,
            [5 => [1 => ['https://youtube.com/watch?v=vidA&t=10s']]], // 15s - 5 Offset
        ];

        // Testfall: Event außerhalb aller Videos
        $event6 = new GameEvent();
        self::invokePrivateMember($event6, 'id', 6);
        $event6->setTimestamp((new DateTimeImmutable())->setTimestamp(2000)); // 1000s nach Start
        yield 'event_not_in_any_video' => [
            [1 => [0 => $videoA]],
            [$event6],
            1000,
            0,
            [],
        ];

        // Testfall: Event in Video mit Startverzögerung
        $videoD = new Video();
        $videoD->setCamera($camera1);
        $videoD->setGameStart(10);
        $videoD->setLength(100);
        $videoD->setUrl('https://youtube.com/watch?v=vidD');
        $videoD->setSort(1);

        $event7 = new GameEvent();
        self::invokePrivateMember($event7, 'id', 7);
        $event7->setTimestamp((new DateTimeImmutable())->setTimestamp(1020)); // 20s nach Start

        yield 'event_in_video_with_delay' => [
            [1 => [10 => $videoD]],
            [$event7],
            1000,
            0,
            [7 => [1 => ['https://youtube.com/watch?v=vidD&t=10s']]], // 20s - 10s
        ];
    }

    /**
     * @param GameEvent[] $events
     */
    private function invokePrepareYoutubeLinks(AbstractController $controller, Game $game, array $events): mixed
    {
        $ref = new ReflectionClass($controller);
        $method = $ref->getMethod('prepareYoutubeLinks');
        $method->setAccessible(true);

        return $method->invoke($controller, $game, $events);
    }

    public static function invokePrivateMethod(object $object, string $methodName, mixed ...$args): mixed
    {
        $ref = new ReflectionClass($object);
        $method = $ref->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invoke($object, ...$args);
    }

    public static function invokePrivateMember(object $object, string $memberName, mixed $value): void
    {
        $ref = new ReflectionClass($object);
        $property = $ref->getProperty($memberName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
