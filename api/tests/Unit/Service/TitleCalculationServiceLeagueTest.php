<?php

namespace App\Tests\Unit\Service;

use App\Entity\GameType;
use App\Repository\PlayerTitleRepository;
use App\Service\TitleCalculationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TitleCalculationServiceLeagueTest extends TestCase
{
    public function testAwardTitlesOlympicPrincipleLeague(): void
    {
        $repo = $this->createMock(PlayerTitleRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);
        $repo->method('findOneBy')->willReturn(null);
        $repo->method('deactivateTitles');

        $service = new TitleCalculationService($em, $repo);
        $gameType = $this->createConfiguredMock(GameType::class, ['getId' => 1]);

        $player1 = $this->getMockBuilder(\App\Entity\Player::class)->onlyMethods(['getId', 'getLastName'])->getMock();
        $player1->method('getId')->willReturn(1);
        $player1->method('getLastName')->willReturn('A');
        $player2 = $this->getMockBuilder(\App\Entity\Player::class)->onlyMethods(['getId', 'getLastName'])->getMock();
        $player2->method('getId')->willReturn(2);
        $player2->method('getLastName')->willReturn('B');
        $player3 = $this->getMockBuilder(\App\Entity\Player::class)->onlyMethods(['getId', 'getLastName'])->getMock();
        $player3->method('getId')->willReturn(3);
        $player3->method('getLastName')->willReturn('C');

        $playerGoals = [
            ['player' => $player1, 'goal_count' => 10],
            ['player' => $player2, 'goal_count' => 10],
            ['player' => $player3, 'goal_count' => 8],
        ];

        $ref = new ReflectionClass($service);
        $method = $ref->getMethod('awardTitlesPerPlayerFromArray');
        $method->setAccessible(true);
        $result = $method->invoke($service, $playerGoals, 'top_scorer', 'league', null, '2025/2026', $gameType);
        $this->assertCount(3, $result, 'Alle drei Spieler sollten einen Titel erhalten.');
        $ranks = array_map(fn ($t) => $t->getTitleRank(), $result);
        $this->assertEqualsCanonicalizing(['bronze', 'gold', 'gold'], $ranks, 'Zwei Gold, dann Bronze (Silber entfällt, Logik wie im Service).');
    }

    public function testCalculateLeagueTopScorersAwardsTitlesPerLeague(): void
    {
        $gameType1 = $this->createConfiguredMock(GameType::class, ['getId' => 1, 'getName' => 'Kreisliga']);
        $gameType2 = $this->createConfiguredMock(GameType::class, ['getId' => 2, 'getName' => 'Bezirksliga']);
        $gameTypes = [$gameType1, $gameType2];

        $repo = $this->createMock(PlayerTitleRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $entityRepoClass = class_exists('Doctrine\\ORM\\EntityRepository') ? 'Doctrine\\ORM\\EntityRepository' : 'Doctrine\\Persistence\\ObjectRepository';
        $gameTypeRepo = $this->createMock($entityRepoClass);
        $gameTypeRepo->method('findAll')->willReturn($gameTypes);
        $em->method('getRepository')->willReturnMap([
            ['App\\Entity\\GameType', $gameTypeRepo],
            ['App\\Entity\\PlayerTitle', $repo],
        ]);

        $service = $this->getMockBuilder(TitleCalculationService::class)
            ->setConstructorArgs([$em, $repo])
            ->onlyMethods(['debugGoalsForSeason'])
            ->getMock();

        // Simuliere: Für jede Liga gibt es ein Tor von Spieler 1
        $goalMock = $this->getMockBuilder(\App\Entity\GameEvent::class)->disableOriginalConstructor()->onlyMethods(['getPlayer'])->getMock();
        $playerMock = $this->getMockBuilder(\App\Entity\Player::class)->onlyMethods(['getId', 'getLastName'])->getMock();
        $playerMock->method('getId')->willReturn(1);
        $playerMock->method('getLastName')->willReturn('Test');
        $goalMock->method('getPlayer')->willReturn($playerMock);
        $service->expects($this->exactly(2))
            ->method('debugGoalsForSeason')
            ->willReturn([$goalMock]);

        $result = $service->calculateLeagueTopScorers('2025/2026');
        $this->assertCount(2, $result, 'Es sollten für beide Ligen Titel vergeben werden.');
    }
}
