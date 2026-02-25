<?php

namespace App\Tests\Feature\Controller;

use App\Entity\CalendarEventType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CalendarEventTypesControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testListReturnsAllEventTypesForAuthenticatedUser(): void
    {
        $user = $this->createUser('voter-test-test@example.com', ['ROLE_USER']);
        $this->createEventType('voter-test-Training');
        $this->createEventType('voter-test-Match');

        $this->client->loginUser($user);
        $this->client->request('GET', '/api/calendar-event-types');

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('entries', $data);
        $this->assertGreaterThanOrEqual(2, count($data['entries']));
    }

    /**
     * @param array<string> $roles
     */
    private function createUser(string $email, array $roles): User
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

    private function createEventType(string $name): CalendarEventType
    {
        $eventType = new CalendarEventType();
        $eventType->setName($name);
        $eventType->setColor('#000000');

        $this->entityManager->persist($eventType);
        $this->entityManager->flush();

        return $eventType;
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();

        // Delete only test data with voter-test- prefix
        $connection->executeStatement('DELETE FROM calendar_event_types WHERE name LIKE "voter-test-%"');
        $connection->executeStatement('DELETE FROM users WHERE email LIKE "voter-test-%"');

        $this->entityManager->close();

        parent::tearDown();
        restore_exception_handler();
    }
}
