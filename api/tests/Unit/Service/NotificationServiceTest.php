<?php

namespace App\Tests\Unit\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class NotificationServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private NotificationRepository&MockObject $repo;
    private PushNotificationService&MockObject $pushService;
    private LoggerInterface&MockObject $logger;
    private NotificationService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repo = $this->createMock(NotificationRepository::class);
        $this->pushService = $this->createMock(PushNotificationService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new NotificationService(
            $this->em,
            $this->repo,
            $this->pushService,
            $this->logger
        );
    }

    // ======================================================================
    //  createNotification — single user
    // ======================================================================

    public function testCreateNotificationPersistsAndSendsPush(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(Notification::class));
        // flush called twice: once for persist, once for isSent = true
        $this->em->expects($this->exactly(2))->method('flush');

        $this->pushService->expects($this->once())
            ->method('sendNotification')
            ->with($user, 'Test Title', 'Test Body', '/test/url');

        $notification = $this->service->createNotification(
            $user,
            'news',
            'Test Title',
            'Test Body',
            ['url' => '/test/url']
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertSame('news', $notification->getType());
        $this->assertSame('Test Title', $notification->getTitle());
        $this->assertSame('Test Body', $notification->getMessage());
        $this->assertTrue($notification->isSent(), 'Notification must be marked as sent after push succeeds');
    }

    public function testCreateNotificationExtractsUrlFromData(): void
    {
        $user = $this->createMock(User::class);

        $this->pushService->expects($this->once())
            ->method('sendNotification')
            ->with($user, 'Title', '', '/news/42');

        $this->service->createNotification($user, 'news', 'Title', null, ['newsId' => 42, 'url' => '/news/42']);
    }

    public function testCreateNotificationUsesSlashWhenNoUrlInData(): void
    {
        $user = $this->createMock(User::class);

        $this->pushService->expects($this->once())
            ->method('sendNotification')
            ->with($user, 'Title', 'Body', '/');

        $this->service->createNotification($user, 'system', 'Title', 'Body');
    }

    public function testCreateNotificationHandlesPushFailureGracefully(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $this->pushService->expects($this->once())
            ->method('sendNotification')
            ->willThrowException(new Exception('Connection timeout'));

        // Must log the error
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->stringContains('Connection timeout'));

        // Notification should still be created
        $notification = $this->service->createNotification($user, 'news', 'Title', 'Body');

        $this->assertInstanceOf(Notification::class, $notification);
        // isSent should be false since push failed
        $this->assertFalse($notification->isSent(), 'Notification must NOT be marked sent when push fails');
    }

    // ======================================================================
    //  createNotificationForUsers — multiple users
    // ======================================================================

    public function testCreateNotificationForUsersSendsToAllUsers(): void
    {
        $user1 = $this->createMock(User::class);
        $user1->method('getId')->willReturn(1);
        $user2 = $this->createMock(User::class);
        $user2->method('getId')->willReturn(2);
        $user3 = $this->createMock(User::class);
        $user3->method('getId')->willReturn(3);

        // Persist called once per user
        $this->em->expects($this->exactly(3))->method('persist');

        // Push sent to each user
        $this->pushService->expects($this->exactly(3))
            ->method('sendNotification');

        $notifications = $this->service->createNotificationForUsers(
            [$user1, $user2, $user3],
            'news',
            'Neue Nachricht',
            'Details...',
            ['url' => '/news/1']
        );

        $this->assertCount(3, $notifications);

        // All should be marked as sent
        foreach ($notifications as $notification) {
            $this->assertTrue($notification->isSent(), 'All notifications must be marked sent on success');
        }
    }

    public function testCreateNotificationForUsersMarksFailedAsSentFalse(): void
    {
        $user1 = $this->createMock(User::class);
        $user1->method('getId')->willReturn(1);
        $user2 = $this->createMock(User::class);
        $user2->method('getId')->willReturn(2);

        // First push succeeds, second fails
        $this->pushService->expects($this->exactly(2))
            ->method('sendNotification')
            ->willReturnCallback(function (User $user) {
                if ($user->getId() === 2) {
                    throw new Exception('Push failed for user 2');
                }
            });

        $this->logger->expects($this->once())->method('warning');

        $notifications = $this->service->createNotificationForUsers(
            [$user1, $user2],
            'news',
            'Title',
            'Body',
            ['url' => '/news/1']
        );

        $this->assertCount(2, $notifications);
        $this->assertTrue($notifications[0]->isSent(), 'First notification should be sent');
        $this->assertFalse($notifications[1]->isSent(), 'Second notification should NOT be sent (push failed)');
    }

    public function testCreateNotificationForUsersExtractsUrl(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $this->pushService->expects($this->once())
            ->method('sendNotification')
            ->with($user, 'T', 'B', '/games/5');

        $this->service->createNotificationForUsers(
            [$user],
            'participation',
            'T',
            'B',
            ['url' => '/games/5']
        );
    }

    public function testCreateNotificationForUsersHandlesEmptyArray(): void
    {
        $this->pushService->expects($this->never())->method('sendNotification');
        $this->em->expects($this->never())->method('persist');

        $result = $this->service->createNotificationForUsers([], 'news', 'T');
        $this->assertSame([], $result);
    }

    // ======================================================================
    //  Convenience methods — createNewsNotification, createMessageNotification
    // ======================================================================

    public function testCreateNewsNotificationSetsCorrectTypeAndUrl(): void
    {
        $user = $this->createMock(User::class);

        $this->pushService->expects($this->once())
            ->method('sendNotification')
            ->with($user, 'Neue Nachricht: Breaking News', 'Short text', '/news/42');

        $notification = $this->service->createNewsNotification($user, 'Breaking News', 'Short text', 42);

        $this->assertSame('news', $notification->getType());
        $this->assertSame('Neue Nachricht: Breaking News', $notification->getTitle());
        $this->assertSame(['newsId' => 42, 'url' => '/news/42'], $notification->getData());
    }

    public function testCreateMessageNotificationSetsCorrectTypeAndUrl(): void
    {
        $user = $this->createMock(User::class);

        $this->pushService->expects($this->once())
            ->method('sendNotification')
            ->with($user, 'Neue Nachricht von Max', 'RE: Training', '/messages/99');

        $notification = $this->service->createMessageNotification($user, 'Max', 'RE: Training', 99);

        $this->assertSame('message', $notification->getType());
        $this->assertSame(['messageId' => 99, 'url' => '/messages/99'], $notification->getData());
    }
}
