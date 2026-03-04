<?php

namespace App\Tests\Unit\Service;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\Player;
use App\Service\ReportVirtualFieldService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class ReportVirtualFieldServiceTest extends TestCase
{
    public function testEinsatzminutenReturnsZeroForNoGames(): void
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('join')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();

        $query = $this->createMock(\Doctrine\ORM\AbstractQuery::class);
        $query->method('getResult')->willReturn([]);
        $qb->method('getQuery')->willReturn($query);

        $gameRepo = $this->createMock(EntityRepository::class);
        $gameRepo->method('createQueryBuilder')->willReturn($qb);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($gameRepo);

        $svc = new ReportVirtualFieldService($em);
        $player = $this->createMock(Player::class);

        $result = $svc->einsatzminuten($player);

        $this->assertSame(0, $result);
    }

    public function testEinsatzminutenCalculatesMinutesForFullGame(): void
    {
        $player = $this->createMock(Player::class);

        $startTime = new \DateTimeImmutable('2025-01-01 15:00:00');
        $endTime = new \DateTimeImmutable('2025-01-01 16:30:00');

        $game = $this->createMock(Game::class);
        $game->method('getStartTime')->willReturn($startTime);
        $game->method('getEndTime')->willReturn($endTime);

        // Event: no sub_in and no sub_out → full game (startTime to endTime)
        $event = $this->createMock(GameEvent::class);
        $event->method('isSubstitutionIn')->willReturn(false);
        $event->method('isSubstitutionOut')->willReturn(false);
        $event->method('getTimestamp')->willReturn(null);

        // Game repo returns the game
        $gameQb = $this->createMock(QueryBuilder::class);
        $gameQb->method('join')->willReturnSelf();
        $gameQb->method('where')->willReturnSelf();
        $gameQb->method('setParameter')->willReturnSelf();

        $gameQuery = $this->createMock(\Doctrine\ORM\AbstractQuery::class);
        $gameQuery->method('getResult')->willReturn([$game]);
        $gameQb->method('getQuery')->willReturn($gameQuery);

        // Event repo returns events for the game
        $eventRepo = $this->createMock(EntityRepository::class);
        $eventRepo->method('findBy')->willReturn([$event]);

        $gameRepo = $this->createMock(EntityRepository::class);
        $gameRepo->method('createQueryBuilder')->willReturn($gameQb);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnCallback(function (string $className) use ($gameRepo, $eventRepo) {
            if ($className === Game::class) {
                return $gameRepo;
            }

            return $eventRepo;
        });

        $svc = new ReportVirtualFieldService($em);
        $result = $svc->einsatzminuten($player);

        // 90 minutes (endTime - startTime = 90 min)
        $this->assertSame(90, $result);
    }

    public function testEinsatzminutenSkipsGameWithoutStartTime(): void
    {
        $player = $this->createMock(Player::class);

        $game = $this->createMock(Game::class);
        $game->method('getStartTime')->willReturn(null);
        $game->method('getEndTime')->willReturn(null);

        $event = $this->createMock(GameEvent::class);
        $event->method('isSubstitutionIn')->willReturn(false);
        $event->method('isSubstitutionOut')->willReturn(false);

        $gameQb = $this->createMock(QueryBuilder::class);
        $gameQb->method('join')->willReturnSelf();
        $gameQb->method('where')->willReturnSelf();
        $gameQb->method('setParameter')->willReturnSelf();

        $gameQuery = $this->createMock(\Doctrine\ORM\AbstractQuery::class);
        $gameQuery->method('getResult')->willReturn([$game]);
        $gameQb->method('getQuery')->willReturn($gameQuery);

        $eventRepo = $this->createMock(EntityRepository::class);
        $eventRepo->method('findBy')->willReturn([$event]);

        $gameRepo = $this->createMock(EntityRepository::class);
        $gameRepo->method('createQueryBuilder')->willReturn($gameQb);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnCallback(function (string $className) use ($gameRepo, $eventRepo) {
            if ($className === Game::class) {
                return $gameRepo;
            }

            return $eventRepo;
        });

        $svc = new ReportVirtualFieldService($em);
        $result = $svc->einsatzminuten($player);

        $this->assertSame(0, $result);
    }

    public function testEinsatzminutenDefaultsToNinetyMinutesWithoutEndTime(): void
    {
        $player = $this->createMock(Player::class);

        $startTime = new \DateTimeImmutable('2025-01-01 15:00:00');

        $game = $this->createMock(Game::class);
        $game->method('getStartTime')->willReturn($startTime);
        $game->method('getEndTime')->willReturn(null);

        $event = $this->createMock(GameEvent::class);
        $event->method('isSubstitutionIn')->willReturn(false);
        $event->method('isSubstitutionOut')->willReturn(false);
        $event->method('getTimestamp')->willReturn(null);

        $gameQb = $this->createMock(QueryBuilder::class);
        $gameQb->method('join')->willReturnSelf();
        $gameQb->method('where')->willReturnSelf();
        $gameQb->method('setParameter')->willReturnSelf();

        $gameQuery = $this->createMock(\Doctrine\ORM\AbstractQuery::class);
        $gameQuery->method('getResult')->willReturn([$game]);
        $gameQb->method('getQuery')->willReturn($gameQuery);

        $eventRepo = $this->createMock(EntityRepository::class);
        $eventRepo->method('findBy')->willReturn([$event]);

        $gameRepo = $this->createMock(EntityRepository::class);
        $gameRepo->method('createQueryBuilder')->willReturn($gameQb);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnCallback(function (string $className) use ($gameRepo, $eventRepo) {
            if ($className === Game::class) {
                return $gameRepo;
            }

            return $eventRepo;
        });

        $svc = new ReportVirtualFieldService($em);
        $result = $svc->einsatzminuten($player);

        // Without end time, defaults to start + 90 minutes
        $this->assertSame(90, $result);
    }
}
