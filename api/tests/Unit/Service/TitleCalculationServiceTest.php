<?php

namespace App\Tests\Unit\Service;

// Dummy-Klassen für die benötigten Methoden (nach namespace, vor Testklasse)
class DummyRelationType {
    public function getIdentifier() { return 'self_player'; }
}

class DummyUserRelation {
    private $user;
    public function __construct($user) { $this->user = $user; }
    public function getUser() { return $this->user; }
    public function getRelationType() { return new DummyRelationType(); }
}

class DummyPlayer {
    private $userRelation;
    public function __construct($userRelation) { $this->userRelation = $userRelation; }
    public function getUserRelations() { return [$this->userRelation]; }
    public function getId() { return 1; }
}

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTitle;
use App\Repository\UserTitleRepository;
use App\Service\TitleCalculationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TitleCalculationServiceTest extends TestCase
{
    public function testAwardTitleDoesNotCreateDuplicates()
    {
        $user = $this->createMock(User::class);
        $team = $this->createMock(Team::class);
        $repo = $this->createMock(UserTitleRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        // Repository gibt bereits existierenden Titel zurück
        $repo->method('findOneBy')->willReturn(new UserTitle());
        $repo->method('deactivateTitles');
        $em->method('getRepository')->willReturn($repo);

        $service = new TitleCalculationService($em, $repo);



        $userRelationMock = new DummyUserRelation($user);
        $playerMock = new DummyPlayer($userRelationMock);

        $playerGoals = [
            [
                'player' => $playerMock,
                'goal_count' => 5
            ]
        ];

        $result = $this->invokeAwardTitlesPerPlayerFromArray($service, $playerGoals, 'top_scorer', 'platform', null, '2025/2026');
        $this->assertCount(1, $result, 'Es sollte nur ein Titel vergeben werden, auch bei erneutem Aufruf.');
    }

    private function invokeAwardTitlesPerPlayerFromArray($service, $playerGoals, $cat, $scope, $team, $season)
    {
        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('awardTitlesPerPlayerFromArray');
        $method->setAccessible(true);
        return $method->invoke($service, $playerGoals, $cat, $scope, $team, $season);
    }
}
