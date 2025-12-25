<?php

namespace App\Tests\Feature\Controller;

use App\Entity\DashboardWidget;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WidgetControllerTest extends WebTestCase
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

    public function testDeleteDeniesAccessToOtherUsersWidget(): void
    {
        $user1 = $this->createUser('voter-test-user1@example.com');
        $user2 = $this->createUser('voter-test-user2@example.com');
        $widget = $this->createWidget($user2, 'voter-test-calendar');

        $this->client->loginUser($user1);
        $this->client->request('DELETE', '/api/widget/' . $widget->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteAllowsAccessToOwnWidget(): void
    {
        $user = $this->createUser('voter-test-user@example.com');
        $widget = $this->createWidget($user, 'voter-test-calendar');

        $this->client->loginUser($user);
        $this->client->request('DELETE', '/api/widget/' . $widget->getId());

        $this->assertResponseIsSuccessful();
    }

    private function createUser(string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);
        $user->setIsEnabled(true);
        $user->setIsVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createWidget(User $user, string $type): DashboardWidget
    {
        $widget = new DashboardWidget();
        $widget->setUser($user);
        $widget->setType($type);
        $widget->setPosition(1);

        $this->entityManager->persist($widget);
        $this->entityManager->flush();

        return $widget;
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM dashboard_widgets WHERE type LIKE "voter-test-%"');
        $connection->executeStatement('DELETE FROM users WHERE email LIKE "voter-test-%"');
        $this->entityManager->close();
        parent::tearDown();
    }
}
