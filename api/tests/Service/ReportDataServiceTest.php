<?php

namespace App\Tests\Service;

use App\Service\ReportDataService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionObject;

class ReportDataServiceTest extends TestCase
{
    public function testGenerateSpatialPointsNormalizesAndClamps(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $svc = new ReportDataService($em);

        // create simple stubs with pos getters
        $ev1 = new class {
            public function getPosX(): float
            {
                return 0.5;
            }

            public function getPosY(): float
            {
                return 0.75;
            }
        };

        $ev2 = new class {
            public function getPosX(): float
            {
                return 150;
            }

            public function getPosY(): float
            {
                return 200;
            }
        };

        $ev3 = new class {
            public function getPosX(): float
            {
                return -5;
            }

            public function getPosY(): float
            {
                return 50;
            }
        };

        $ref = new ReflectionClass(ReportDataService::class);
        $m = $ref->getMethod('generateSpatialPoints');
        $m->setAccessible(true);

        $points = $m->invokeArgs($svc, [[$ev1, $ev2, $ev3], []]);

        $this->assertIsArray($points);
        $this->assertCount(3, $points);

        // ev1: 0.5 -> 50.00, 0.75 -> 75.00
        $this->assertEquals(50.00, $points[0]['x']);
        $this->assertEquals(75.00, $points[0]['y']);

        // ev2: values >100 should be clamped to 100
        $this->assertEquals(100.00, $points[1]['x']);
        $this->assertEquals(100.00, $points[1]['y']);

        // ev3: x negative -> clamped to 0, y 50 stays
        $this->assertEquals(0.00, $points[2]['x']);
        $this->assertEquals(50.00, $points[2]['y']);
    }

    public function testGenerateReportDataForRadarCountsGoalsAndMeta(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $svc = new ReportDataService($em);

        // Create stub GameEventType with code 'goal'
        $typeGoal = new class {
            public function getCode(): string
            {
                return 'goal';
            }

            public function getName(): string
            {
                return 'Tor';
            }
        };

        // Event stub returning the above type
        $ev1 = new class ($typeGoal) {
            private mixed $t;

            public function __construct(mixed $t)
            {
                $this->t = $t;
            }

            public function getGameEventType(): mixed
            {
                return $this->t;
            }
        };

        $ev2 = new class ($typeGoal) {
            private mixed $t;

            public function __construct(mixed $t)
            {
                $this->t = $t;
            }

            public function getGameEventType(): mixed
            {
                return $this->t;
            }
        };

        // Manually set the private property via reflection for simplicity
        $refEv1 = new ReflectionObject($ev1);
        $prop1 = $refEv1->getProperty('t');
        $prop1->setAccessible(true);
        $prop1->setValue($ev1, $typeGoal);

        $refEv2 = new ReflectionObject($ev2);
        $prop2 = $refEv2->getProperty('t');
        $prop2->setAccessible(true);
        $prop2->setValue($ev2, $typeGoal);

        $ref = new ReflectionClass(ReportDataService::class);
        $m = $ref->getMethod('generateReportDataForRadar');
        $m->setAccessible(true);

        // Request metric 'goals'
        $out = $m->invokeArgs($svc, [[$ev1, $ev2], ['goals'], []]);

        $this->assertArrayHasKey('labels', $out);
        $this->assertArrayHasKey('datasets', $out);
        $this->assertEquals('radar', $out['diagramType']);
        $this->assertEquals(1, count($out['labels']));
        $this->assertEquals('Tore', $out['labels'][0]);

        $this->assertCount(1, $out['datasets']);
        $this->assertEquals([2], $out['datasets'][0]['data']);

        $this->assertArrayHasKey('meta', $out);
        $this->assertArrayHasKey('radarHasData', $out['meta']);
        $this->assertTrue($out['meta']['radarHasData']);
    }

    public function testGenerateReportDataLineAndGroupAndPie(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $svc = new ReportDataService($em);

        // events: players A,A,B; groupKey: teamX/teamY
        // Create Player and Team instances (subclasses) that stringify to readable names
        $playerA = new class extends \App\Entity\Player {
            public function __toString(): string
            {
                return 'Alice';
            }

            public function getFullName(): string
            {
                return 'Alice';
            }
        };

        $playerB = new class extends \App\Entity\Player {
            public function __toString(): string
            {
                return 'Bob';
            }

            public function getFullName(): string
            {
                return 'Bob';
            }
        };

        $teamX = new class extends \App\Entity\Team {
            public function __toString(): string
            {
                return 'TeamX';
            }

            public function getName(): string
            {
                return 'TeamX';
            }
        };

        $teamY = new class extends \App\Entity\Team {
            public function __toString(): string
            {
                return 'TeamY';
            }

            public function getName(): string
            {
                return 'TeamY';
            }
        };

        $evA1 = $this->createMock(\App\Entity\GameEvent::class);
        $evA1->method('getPlayer')->willReturn($playerA);
        $evA1->method('getTeam')->willReturn($teamX);

        $evA2 = $this->createMock(\App\Entity\GameEvent::class);
        $evA2->method('getPlayer')->willReturn($playerA);
        $evA2->method('getTeam')->willReturn($teamY);

        $evB = $this->createMock(\App\Entity\GameEvent::class);
        $evB->method('getPlayer')->willReturn($playerB);
        $evB->method('getTeam')->willReturn($teamX);

        $ref = new ReflectionClass(ReportDataService::class);
        $mLine = $ref->getMethod('generateReportDataForLineOrBarWithoutGroup');
        $mLine->setAccessible(true);
        $outLine = $mLine->invokeArgs($svc, [[$evA1, $evA2, $evB], 'player', 'count']);

        $this->assertEquals(['Alice', 'Bob'], $outLine['labels']);
        $this->assertEquals([2, 1], $outLine['datasets'][0]['data']);

        $mGroup = $ref->getMethod('generateReportDataForGroup');
        $mGroup->setAccessible(true);
        $outGroup = $mGroup->invokeArgs($svc, [[$evA1, $evA2, $evB], 'player', ['team']]);
        $this->assertArrayHasKey('labels', $outGroup);
        $this->assertArrayHasKey('datasets', $outGroup);

        $mPie = $ref->getMethod('generateReportDataForPieWithoutGroup');
        $mPie->setAccessible(true);
        $outPie = $mPie->invokeArgs($svc, [[$evA1, $evA2, $evB], 'player']);
        $this->assertEquals(['Alice', 'Bob'], $outPie['labels']);
        $this->assertEquals([2, 1], $outPie['datasets'][0]['data']);
    }

    public function testGenerateReportDataFiltersPrecipitationYesNo(): void
    {
        // Prepare two events: one with precipitation, one without
        // Create WeatherData mocks that return arrays as expected by the service
        $wdWith = $this->createMock(\App\Entity\WeatherData::class);
        $wdWith->method('getDailyWeatherData')->willReturn(['precipitation_sum' => [5]]);
        $wdWith->method('getHourlyWeatherData')->willReturn(['precipitation' => [0, 0]]);

        $wdNo = $this->createMock(\App\Entity\WeatherData::class);
        $wdNo->method('getDailyWeatherData')->willReturn(['precipitation_sum' => [0]]);
        $wdNo->method('getHourlyWeatherData')->willReturn(['precipitation' => [0, 0]]);

        // CalendarEvent mocks
        $calEventWith = $this->createMock(\App\Entity\CalendarEvent::class);
        $calEventWith->method('getWeatherData')->willReturn($wdWith);

        $calEventNo = $this->createMock(\App\Entity\CalendarEvent::class);
        $calEventNo->method('getWeatherData')->willReturn($wdNo);

        // Game mocks returning the calendar events (match entity types)
        $gameWith = $this->createMock(\App\Entity\Game::class);
        $gameWith->method('getCalendarEvent')->willReturn($calEventWith);

        $gameNo = $this->createMock(\App\Entity\Game::class);
        $gameNo->method('getCalendarEvent')->willReturn($calEventNo);

        // GameEvent mocks returning the Game instances
        $evWith = $this->createMock(\App\Entity\GameEvent::class);
        $evWith->method('getGame')->willReturn($gameWith);

        $evNo = $this->createMock(\App\Entity\GameEvent::class);
        $evNo->method('getGame')->willReturn($gameNo);

        // Build a fake query chain that returns [$evWith, $evNo]
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $query->method('getResult')->willReturn([$evWith, $evNo]);

        $qb = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('join')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $repo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $repo->method('createQueryBuilder')->willReturn($qb);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $svc = new ReportDataService($em);

        // precipitation = 'yes' should keep only the first event
        $outYes = $svc->generateReportData([
            'xField' => 'player',
            'yField' => 'goals',
            'filters' => [
                'precipitation' => 'yes',
            ],
        ]);
        $this->assertEquals(1, $outYes['meta']['eventsCount']);

        // precipitation = 'no' should keep only the second event
        $outNo = $svc->generateReportData([
            'xField' => 'player',
            'yField' => 'goals',
            'filters' => [
                'precipitation' => 'no',
            ],
        ]);
        $this->assertEquals(1, $outNo['meta']['eventsCount']);
    }
}
