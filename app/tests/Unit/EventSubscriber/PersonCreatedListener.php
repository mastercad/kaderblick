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
use PHPUnit\Framework\TestCase;
use Doctrine\Persistence\ObjectRepository;

class PersonCreatedListenerTest extends TestCase
{
    private $entityManager;
    private $userRepository;
    private $relationTypeRepository;
    private $userRelationRepository;
    private $listener;

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

    public function testPostPersistPlayerWithEmailAndUser()
    {
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

    public function testPostPersistPlayerWithoutEmail()
    {
        $player = new Player(); // no email
        $args = $this->createMock(PostPersistEventArgs::class);

        $this->entityManager->expects($this->never())->method('persist');
        $this->listener->postPersistPlayer($player, $args);
    }

    public function testPostPersistPlayerWithoutUser()
    {
        $player = (new Player())->setEmail('player@test.com');
        $args = $this->createMock(PostPersistEventArgs::class);
        $args->method('getObjectManager')->willReturn($this->entityManager);

        $this->userRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->never())->method('persist');
        $this->listener->postPersistPlayer($player, $args);
    }

    public function testPostUpdatePlayerWithExistingRelation()
    {
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

    public function testPostUpdatePlayerCreatesNewRelation()
    {
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

    public function testPostPersistCoachWithEmailAndUser()
    {
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

    public function testPostUpdateCoachWithExistingRelation()
    {
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

    public function testPostUpdateCoachCreatesNewRelation()
    {
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

    public function testPostUpdatePlayerWithoutRelationType()
    {
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
