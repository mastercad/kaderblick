<?php

namespace App\Tests\Feature;

use App\Entity\PushSubscription;
use App\Entity\User;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Minishlink\WebPush\MessageSentReport;
use Minishlink\WebPush\WebPush;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PushNotificationTest extends WebTestCase
{
    private static KernelBrowser $client;
    // @phpstan-ignore-next-line
    private static User $user;
    private static EntityManagerInterface $entityManager;

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();
        self::$entityManager = self::$client->getContainer()->get('doctrine')->getManager();
        self::$user = self::createUser();
    }

    public function testSubscriptionEndpoint(): void
    {
        $this->markTestIncomplete();
        // @phpstan-ignore-next-line
        self::$client->loginUser(self::$user);

        self::$client->request('POST', '/app/push/subscribe', [], [], [], json_encode([
            'endpoint' => 'https://test.push.service',
            'keys' => [
                'p256dh' => 'test-key',
                'auth' => 'test-auth'
            ]
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString(
            '{"status":"success"}',
            self::$client->getResponse()->getContent()
        );

        $subscription = self::$entityManager->getRepository(PushSubscription::class)
            ->findOneBy(['endpoint' => 'https://test.push.service']);
        $this->assertNotNull($subscription);
        $this->assertEquals(self::$user->getId(), $subscription->getUser()->getId());
    }

    public function testPushNotificationService(): void
    {
        $this->markTestIncomplete();
        // @phpstan-ignore-next-line
        $subscription = new PushSubscription();
        $subscription->setUser(self::$user)
            ->setEndpoint('https://test.push.service')
            ->setPublicKey('test-key')
            ->setAuthToken('test-auth');

        self::$entityManager->persist($subscription);
        self::$entityManager->flush();

        $webPushMock = $this->createMock(WebPush::class);
        $report = $this->createMock(MessageSentReport::class);
        $report->method('isSuccess')->willReturn(true);

        $webPushMock->expects($this->once())
            ->method('sendNotification')
            ->willReturn($report);

        $webPushMock->expects($this->once())
            ->method('flush')
            ->willReturn([$report]);

        $pushService = new PushNotificationService(
            self::$entityManager,
            'test-public-key',
            'test-private-key'
        );
        $pushService->setWebPush($webPushMock);

        $pushService->sendNotification(
            self::$user,
            'Test Title',
            'Test Message',
            '/test-url'
        );
    }

    public function testInvalidSubscriptionRemoval(): void
    {
        $this->markTestIncomplete();
        // @phpstan-ignore-next-line
        $subscription = new PushSubscription();
        $subscription->setUser(self::$user)
            ->setEndpoint('https://invalid.push.service')
            ->setPublicKey('test-key')
            ->setAuthToken('test-auth');

        self::$entityManager->persist($subscription);
        self::$entityManager->flush();

        $webPushMock = $this->createMock(WebPush::class);
        $report = $this->createMock(MessageSentReport::class);
        $report->method('isSuccess')->willReturn(false);
        $report->method('getSubscription')->willReturn($subscription);

        $webPushMock->method('sendNotification')->willReturn($report);
        $webPushMock->method('flush')->willReturn([$report]);

        $pushService = new PushNotificationService(
            self::$entityManager,
            'test-public-key',
            'test-private-key'
        );
        $pushService->setWebPush($webPushMock);

        $pushService->sendNotification(self::$user, 'Test', 'Message');

        $this->assertNull(
            self::$entityManager->getRepository(PushSubscription::class)
                ->findOneBy(['endpoint' => 'https://invalid.push.service'])
        );
    }

    private static function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com')
            ->setPassword('password')
            ->setFirstName('Test')
            ->setLastName('User')
            ->setIsVerified(true);

        self::$entityManager->persist($user);
        self::$entityManager->flush();
        self::$entityManager->clear();

        return $user;
    }
}
