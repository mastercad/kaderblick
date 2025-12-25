<?php

namespace App\Tests\Feature\Controller;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationControllerTest extends WebTestCase
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

    public function testIndexOnlyReturnsOwnNotifications(): void
    {
        $user1 = $this->createUser('voter-test-user1@example.com');
        $user2 = $this->createUser('voter-test-user2@example.com');

        $this->createNotification($user1, 'voter-test-Notification for user1');
        $this->createNotification($user2, 'voter-test-Notification for user2');

        $this->client->loginUser($user1);
        $this->client->request('GET', '/api/notifications');

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $messages = array_column($data['notifications'], 'message');
        $this->assertContains('voter-test-Notification for user1', $messages);
        $this->assertNotContains('voter-test-Notification for user2', $messages);
    }

    public function testMarkReadDeniesAccessToOtherUsersNotification(): void
    {
        $user1 = $this->createUser('voter-test-user1@example.com');
        $user2 = $this->createUser('voter-test-user2@example.com');
        $notification = $this->createNotification($user2, 'voter-test-For user2');

        $this->client->loginUser($user1);
        $this->client->request('POST', '/api/notifications/' . $notification->getId() . '/read');

        // Controller returns 404 because findOneBy filters by user, not 403
        $this->assertResponseStatusCodeSame(404);
    }

    public function testMarkReadAllowsAccessToOwnNotification(): void
    {
        $user = $this->createUser('voter-test-user@example.com');
        $notification = $this->createNotification($user, 'voter-test-My notification');

        $this->client->loginUser($user);
        $this->client->request('POST', '/api/notifications/' . $notification->getId() . '/read');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCannotMarkReadOtherUsersNotification(): void
    {
        $admin = $this->createUser('voter-test-admin@example.com', ['ROLE_ADMIN']);
        $user = $this->createUser('voter-test-user@example.com');
        $notification = $this->createNotification($user, 'voter-test-User notification');

        $this->client->loginUser($admin);
        $this->client->request('POST', '/api/notifications/' . $notification->getId() . '/read');

        // Controller returns 404 because findOneBy filters by user
        // Even admins can only mark their own notifications as read
        $this->assertResponseStatusCodeSame(404);
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

    private function createNotification(User $user, string $message): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setTitle('voter-test-notification');
        $notification->setMessage($message);
        $notification->setType('info');
        $notification->setIsRead(false);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM notifications WHERE message LIKE "voter-test-%"');
        $connection->executeStatement('DELETE FROM users WHERE email LIKE "voter-test-%"');
        $this->entityManager->close();
        parent::tearDown();
    }
}
