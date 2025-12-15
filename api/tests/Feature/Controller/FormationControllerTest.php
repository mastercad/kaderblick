<?php

namespace App\Tests\Feature\Controller;

use App\Entity\Formation;
use App\Entity\FormationType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FormationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testIndexOnlyReturnsOwnFormations(): void
    {
        $user1 = $this->createUser('voter-test-user1@example.com', ['ROLE_USER']);
        $user2 = $this->createUser('voter-test-user2@example.com', ['ROLE_USER']);
        $type = $this->getOrCreateFormationType();

        $this->createFormation($user1, $type, 'voter-test-Own Formation');
        $this->createFormation($user2, $type, 'voter-test-Other Formation');

        $this->client->loginUser($user1);
        $this->client->request('GET', '/formations');

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $formationNames = array_column($data['formations'], 'name');
        $this->assertContains('voter-test-Own Formation', $formationNames);
        $this->assertNotContains('voter-test-Other Formation', $formationNames);
    }

    public function testEditDeniesAccessToOtherUsersFormation(): void
    {
        $user1 = $this->createUser('voter-test-user1@example.com', ['ROLE_USER']);
        $user2 = $this->createUser('voter-test-user2@example.com', ['ROLE_USER']);
        $type = $this->getOrCreateFormationType();
        $otherFormation = $this->createFormation($user2, $type, 'voter-test-Other Formation');

        $this->client->loginUser($user1);
        $this->client->request('POST', '/formation/' . $otherFormation->getId() . '/edit', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'voter-test-Hacked', 'formationData' => []])
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditAllowsAccessToOwnFormation(): void
    {
        $user = $this->createUser('voter-test-user@example.com', ['ROLE_USER']);
        $type = $this->getOrCreateFormationType();
        $formation = $this->createFormation($user, $type, 'voter-test-My Formation');

        $this->client->loginUser($user);
        $this->client->request('POST', '/formation/' . $formation->getId() . '/edit', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'voter-test-Updated', 'formationData' => []])
        );

        $this->assertResponseIsSuccessful();
    }

    public function testDeleteDeniesAccessToOtherUsersFormation(): void
    {
        $user1 = $this->createUser('voter-test-user1@example.com', ['ROLE_USER']);
        $user2 = $this->createUser('voter-test-user2@example.com', ['ROLE_USER']);
        $type = $this->getOrCreateFormationType();
        $otherFormation = $this->createFormation($user2, $type, 'voter-test-Other Formation');

        $this->client->loginUser($user1);
        $this->client->request('DELETE', '/formation/' . $otherFormation->getId() . '/delete');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteAllowsAccessToOwnFormation(): void
    {
        $user = $this->createUser('voter-test-user@example.com', ['ROLE_USER']);
        $type = $this->getOrCreateFormationType();
        $formation = $this->createFormation($user, $type, 'voter-test-My Formation');

        $this->client->loginUser($user);
        $this->client->request('DELETE', '/formation/' . $formation->getId() . '/delete');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanAccessOtherUsersFormation(): void
    {
        $regularUser = $this->createUser('voter-test-user@example.com', ['ROLE_USER']);
        $admin = $this->createUser('voter-test-admin@example.com', ['ROLE_ADMIN']);
        $type = $this->getOrCreateFormationType();
        $userFormation = $this->createFormation($regularUser, $type, 'voter-test-User Formation');

        $this->client->loginUser($admin);
        $this->client->request('POST', '/formation/' . $userFormation->getId() . '/edit', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'voter-test-Admin Modified', 'formationData' => []])
        );

        $this->assertResponseIsSuccessful();
    }

    /**
     * @param array<string> $roles
     */
    private function createUser(string $email, array $roles = ['ROLE_USER']): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPassword('password');
        $user->setRoles($roles);
        $user->setIsEnabled(true);
        $user->setIsVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function getOrCreateFormationType(): FormationType
    {
        $typeRepo = $this->entityManager->getRepository(FormationType::class);
        $type = $typeRepo->findOneBy(['name' => 'fußball']);

        if (!$type) {
            $type = new FormationType();
            $type->setName('fußball');
            $this->entityManager->persist($type);
            $this->entityManager->flush();
        }

        return $type;
    }

    private function createFormation(User $user, FormationType $type, string $name): Formation
    {
        $formation = new Formation();
        $formation->setUser($user);
        $formation->setFormationType($type);
        $formation->setName($name);
        $formation->setFormationData([]);

        $this->entityManager->persist($formation);
        $this->entityManager->flush();

        return $formation;
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();

        // Delete only test data with voter-test- prefix
        $connection->executeStatement('DELETE FROM formations WHERE name LIKE "voter-test-%"');
        $connection->executeStatement('DELETE FROM users WHERE email LIKE "voter-test-%"');

        $this->entityManager->close();

        parent::tearDown();
    }
}
