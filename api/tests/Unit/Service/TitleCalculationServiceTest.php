<?php

namespace App\Tests\Unit\Service;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTitle;
use App\Repository\UserTitleRepository;
use App\Service\TitleCalculationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

// Dummy-Klassen für die benötigten Methoden (nach namespace, vor Testklasse)
class DummyRelationType
{
    public function getIdentifier(): string
    {
        return 'self_player';
    }
}

class DummyUserRelation
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRelationType(): DummyRelationType
    {
        return new DummyRelationType();
    }
}

class DummyPlayer
{
    private DummyUserRelation $userRelation;

    public function __construct(DummyUserRelation $userRelation)
    {
        $this->userRelation = $userRelation;
    }

    /**
     * @return DummyUserRelation[]
     */
    public function getUserRelations(): array
    {
        return [$this->userRelation];
    }

    public function getId(): int
    {
        return 1;
    }
}

class TitleCalculationServiceTest extends TestCase
{
    public function testAwardTitleDoesNotCreateDuplicates(): void
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

    /**
     * @param array<int, array<string, mixed>> $playerGoals
     *
     * @return UserTitle[]
     */
    private function invokeAwardTitlesPerPlayerFromArray(TitleCalculationService $service, array $playerGoals, string $cat, string $scope, ?Team $team, ?string $season): array
    {
        $ref = new ReflectionClass($service);
        $method = $ref->getMethod('awardTitlesPerPlayerFromArray');
        $method->setAccessible(true);

        return $method->invoke($service, $playerGoals, $cat, $scope, $team, $season);
    }
}
