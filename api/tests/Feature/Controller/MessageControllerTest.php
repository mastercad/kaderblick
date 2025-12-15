<?php

namespace App\Tests\Feature\Controller;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MessageControllerTest extends WebTestCase
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

    public function testIndexOnlyReturnsMessagesForRecipient(): void
    {
        $user1 = $this->createUser('voter-test-user1@example.com');
        $user2 = $this->createUser('voter-test-user2@example.com');
        $sender = $this->createUser('voter-test-sender@example.com');

        $this->createMessage($sender, [$user1], 'voter-test-Message for user1');
        $this->createMessage($sender, [$user2], 'voter-test-Message for user2');

        $this->client->loginUser($user1);
        $this->client->request('GET', '/api/messages');

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $subjects = array_column($data['messages'], 'subject');
        $this->assertContains('voter-test-Message for user1', $subjects);
        $this->assertNotContains('voter-test-Message for user2', $subjects);
    }

    public function testShowDeniesAccessToMessageForNonRecipient(): void
    {
        $user1 = $this->createUser('voter-test-user1@example.com');
        $user2 = $this->createUser('voter-test-user2@example.com');
        $sender = $this->createUser('voter-test-sender@example.com');

        $message = $this->createMessage($sender, [$user2], 'voter-test-Private message');

        $this->client->loginUser($user1);
        $this->client->request('GET', '/api/messages/' . $message->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShowAllowsAccessToMessageForRecipient(): void
    {
        $user = $this->createUser('voter-test-user@example.com');
        $sender = $this->createUser('voter-test-sender@example.com');

        $message = $this->createMessage($sender, [$user], 'voter-test-Message for user');

        $this->client->loginUser($user);
        $this->client->request('GET', '/api/messages/' . $message->getId());

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('voter-test-Message for user', $data['subject']);
    }

    public function testShowAllowsAccessToMessageForSender(): void
    {
        $sender = $this->createUser('voter-test-sender@example.com');
        $recipient = $this->createUser('voter-test-recipient@example.com');

        $message = $this->createMessage($sender, [$recipient], 'voter-test-Sent message');

        $this->client->loginUser($sender);
        $this->client->request('GET', '/api/messages/' . $message->getId());

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('voter-test-Sent message', $data['subject']);
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

    /**
     * @param array<User> $recipients
     */
    private function createMessage(User $sender, array $recipients, string $subject): Message
    {
        $message = new Message();
        $message->setSender($sender);
        $message->setSubject($subject);
        $message->setContent('Test content');

        foreach ($recipients as $recipient) {
            $message->addRecipient($recipient);
        }

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();

        // Delete only test data with voter-test- prefix
        $connection->executeStatement('DELETE FROM message_recipients WHERE message_id IN (SELECT id FROM messages WHERE subject LIKE "voter-test-%")');
        $connection->executeStatement('DELETE FROM messages WHERE subject LIKE "voter-test-%"');
        $connection->executeStatement('DELETE FROM users WHERE email LIKE "voter-test-%"');

        $this->entityManager->close();

        parent::tearDown();
    }
}
