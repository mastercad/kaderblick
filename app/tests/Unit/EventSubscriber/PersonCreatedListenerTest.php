<?php

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\RelationType;
use App\Entity\User;
use App\Entity\UserRelation;
use App\EventSubscriber\PersonCreatedListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PersonCreatedListenerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    /** @var ObjectRepository<User>&MockObject */
    private ObjectRepository $userRepository;
    /** @var ObjectRepository<RelationType>&MockObject */
    private ObjectRepository $relationTypeRepository;
    /** @var ObjectRepository<UserRelation>&MockObject */
    private ObjectRepository $userRelationRepository;
    /** @phpstan-ignore-next-line all tests are skipped for now */
    private PersonCreatedListener $listener;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(ObjectRepository::class);
        $this->relationTypeRepository = $this->createMock(ObjectRepository::class);
        $this->userRelationRepository = $this->createMock(ObjectRepository::class);

        $this->entityManager->method('getRepository')
            ->willReturnMap([
                [User::class, $this->userRepository],
                [RelationType::class, $this->relationTypeRepository],
                [UserRelation::class, $this->userRelationRepository]
            ]);

        $this->listener = new PersonCreatedListener();
    }

    public function testPostPersistPlayerWithEmailAndUser(): void
    {
        $this->markTestIncomplete();
        /** @phpstan-ignore-next-line all tests are skipped for now */
        $player = (new Player())->setEmail('player@test.com');
        $user = new User();
        $relationType = (new RelationType())->setIdentifier('self_player');

        $args = $this->createMock(PostPersistEventArgs::class);
        $args->method('getObjectManager')->willReturn($this->entityManager);

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->relationTypeRepository->method('findOneBy')->willReturn($relationType);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->listener->postPersistPlayer($player, $args);
    }

    public function testPostPersistPlayerWithoutEmail(): void
    {
        $this->markTestIncomplete();
        /** @phpstan-ignore-next-line all tests are skipped for now */
        $player = new Player(); // no email
        $args = $this->createMock(PostPersistEventArgs::class);

        $this->entityManager->expects($this->never())->method('persist');
        $this->listener->postPersistPlayer($player, $args);
    }

    public function testPostPersistPlayerWithoutUser(): void
    {
        $this->markTestIncomplete();
        /** @phpstan-ignore-next-line all tests are skipped for now */
        $player = (new Player())->setEmail('player@test.com');
        $args = $this->createMock(PostPersistEventArgs::class);
        $args->method('getObjectManager')->willReturn($this->entityManager);

        $this->userRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->never())->method('persist');
        $this->listener->postPersistPlayer($player, $args);
    }

    public function testPostUpdatePlayerWithExistingRelation(): void
    {
        $this->markTestIncomplete();
        /** @phpstan-ignore-next-line all tests are skipped for now */
        $player = (new Player())->setEmail('player@test.com');
        $user = new User();
        $relationType = (new RelationType())->setIdentifier('self_player');
        $existingRelation = (new UserRelation())->setRelationType($relationType);

        $args = $this->createMock(PostUpdateEventArgs::class);
        $args->method('getObjectManager')->willReturn($this->entityManager);

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->userRelationRepository->method('findOneBy')->willReturn($existingRelation);

        $this->entityManager->expects($this->never())->method('persist');
        $this->listener->postUpdatePlayer($player, $args);
    }

    public function testPostUpdatePlayerCreatesNewRelation(): void
    {
        $this->markTestIncomplete();
        /** @phpstan-ignore-next-line all tests are skipped for now */
        $player = (new Player())->setEmail('player@test.com');
        $user = new User();
        $relationType = (new RelationType())->setIdentifier('self_player');

        $args = $this->createMock(PostUpdateEventArgs::class);
        $args->method('getObjectManager')->willReturn($this->entityManager);

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->userRelationRepository->method('findOneBy')->willReturn(null);
        $this->relationTypeRepository->method('findOneBy')->willReturn($relationType);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->listener->postUpdatePlayer($player, $args);
    }

    public function testPostPersistCoachWithEmailAndUser(): void
    {
        $this->markTestIncomplete();
        /** @phpstan-ignore-next-line all tests are skipped for now */
        $coach = (new Coach())->setEmail('coach@test.com');
        $user = new User();
        $relationType = (new RelationType())->setIdentifier('self_coach');

        $args = $this->createMock(PostPersistEventArgs::class);
        $args->method('getObjectManager')->willReturn($this->entityManager);

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->relationTypeRepository->method('findOneBy')->willReturn($relationType);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->listener->postPersistCoach($coach, $args);
    }

    public function testPostUpdateCoachWithExistingRelation(): void
    {
        $this->markTestIncomplete();
        /** @phpstan-ignore-next-line all tests are skipped for now */
        $coach = (new Coach())->setEmail('coach@test.com');
        $user = new User();
        $relationType = (new RelationType())->setIdentifier('self_coach');
        $existingRelation = (new UserRelation())->setRelationType($relationType);

        $args = $this->createMock(PostUpdateEventArgs::class);
        $args->method('getObjectManager')->willReturn($this->entityManager);

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->userRelationRepository->method('findOneBy')->willReturn($existingRelation);

        $this->entityManager->expects($this->never())->method('persist');
        $this->listener->postUpdateCoach($coach, $args);
    }

    public function testPostUpdateCoachCreatesNewRelation(): void
    {
        $this->markTestIncomplete();
        /** @phpstan-ignore-next-line all tests are skipped for now */
        $coach = (new Coach())->setEmail('coach@test.com');
        $user = new User();
        $relationType = (new RelationType())->setIdentifier('self_coach');

        $args = $this->createMock(PostUpdateEventArgs::class);
        $args->method('getObjectManager')->willReturn($this->entityManager);

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->userRelationRepository->method('findOneBy')->willReturn(null);
        $this->relationTypeRepository->method('findOneBy')->willReturn($relationType);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->listener->postUpdateCoach($coach, $args);
    }

    public function testPostUpdatePlayerWithoutRelationType(): void
    {
        $this->markTestIncomplete();
        /** @phpstan-ignore-next-line all tests are skipped for now */
        $player = (new Player())->setEmail('player@test.com');
        $user = new User();

        $args = $this->createMock(PostUpdateEventArgs::class);
        $args->method('getObjectManager')->willReturn($this->entityManager);

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->userRelationRepository->method('findOneBy')->willReturn(null);
        $this->relationTypeRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->never())->method('persist');
        $this->listener->postUpdatePlayer($player, $args);
    }
}
