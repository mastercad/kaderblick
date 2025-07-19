<?php

namespace App\Tests\Feature;

use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\User;
use App\Entity\UserRelation;
use App\Repository\UserRelationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PersonCreatedListenerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRelationRepository $userRelationRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->userRelationRepository = $this->entityManager->getRepository(UserRelation::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testPlayerCreationCreatesUserRelation()
    {
        $this->markTestIncomplete();
        $email = 'player_endtoend@test.com';

        // User erstellen
        $user = (new User())
            ->setFirstName('User')
            ->setLastName('EndToEnd')
            ->setEmail($email)
            ->setPassword('password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Player erstellen (sollte Listener triggern)
        $player = (new Player())
            ->setFirstName('Player')
            ->setLastName('EndToEnd')
            ->setEmail($email)
            ->setFirstName('Test')
            ->setLastName('Player');
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        // Relation überprüfen
        $relation = $this->userRelationRepository->findOneBy([
            'relatedUser' => $user,
            'player' => $player
        ]);

        $this->assertNotNull($relation, 'UserRelation sollte erstellt worden sein');
        $this->assertEquals('self_player', $relation->getRelationType()->getIdentifier());
    }

    public function testCoachCreationCreatesUserRelation()
    {
        $this->markTestIncomplete();
        $email = 'coach_endtoend@test.com';

        // User erstellen
        $user = (new User())
            ->setFirstName('User')
            ->setLastName('EndToEnd')
            ->setEmail($email)
            ->setPassword('password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Coach erstellen (sollte Listener triggern)
        $coach = (new Coach())
            ->setFirstName('Player')
            ->setLastName('EndToEnd')
            ->setEmail($email)
            ->setFirstName('Test')
            ->setLastName('Coach');
        $this->entityManager->persist($coach);
        $this->entityManager->flush();

        // Relation überprüfen
        $relation = $this->userRelationRepository->findOneBy([
            'relatedUser' => $user,
            'coach' => $coach
        ]);

        $this->assertNotNull($relation, 'UserRelation sollte erstellt worden sein');
        $this->assertEquals('self_coach', $relation->getRelationType()->getIdentifier());
    }

    public function testPlayerUpdateCreatesUserRelationWhenEmailAddedLater()
    {
        $this->markTestIncomplete();
        $email = 'player_update@test.com';

        // Player ohne Email erstellen (sollte keine Relation erstellen)
        $player = (new Player())
            ->setFirstName('Player')
            ->setLastName('EndToEnd')
            ->setFirstName('Test')
            ->setLastName('Player');
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        // User erstellen
        $user = (new User())
            ->setFirstName('User')
            ->setLastName('EndToEnd')
            ->setEmail($email)
            ->setPassword('password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Player updaten mit Email (sollte Listener triggern)
        $player->setEmail($email);
        $this->entityManager->flush();

        // Relation überprüfen
        $relation = $this->userRelationRepository->findOneBy([
            'relatedUser' => $user,
            'player' => $player
        ]);

        $this->assertNotNull($relation, 'UserRelation sollte nach Update erstellt worden sein');
    }

    public function testNoDuplicateRelationsCreated()
    {
        $this->markTestIncomplete();
        $email = 'no_duplicate@test.com';

        // User erstellen
        $user = (new User())
            ->setFirstName('User')
            ->setLastName('EndToEnd')
            ->setEmail($email)
            ->setPassword('password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Player erstellen
        $player = (new Player())
            ->setFirstName('Player')
            ->setLastName('EndToEnd')
            ->setEmail($email)
            ->setFirstName('Test')
            ->setLastName('Player');
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        // Player erneut speichern
        $player->setFirstName('Updated');
        $this->entityManager->flush();

        // Relations zählen
        $relations = $this->userRelationRepository->findBy([
            'relatedUser' => $user,
            'player' => $player
        ]);

        $this->assertCount(1, $relations, 'Es sollte nur eine einzige Relation existieren');
    }
}
