<?php

namespace App\Tests\Unit\Controller;

use App\Controller\CalendarController;
use App\Entity\CalendarEvent;
use App\Entity\User;
use App\Repository\ParticipationRepository;
use App\Security\Voter\CalendarEventVoter;
use App\Service\CalendarEventService;
use App\Service\EmailNotificationService;
use App\Service\NotificationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CalendarControllerCancelTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private NotificationService&MockObject $notificationService;
    private CalendarController $controller;
    private User&MockObject $user;
    private AuthorizationCheckerInterface&MockObject $authChecker;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $emailService = $this->createMock(EmailNotificationService::class);
        $participationRepo = $this->createMock(ParticipationRepository::class);
        $calendarEventService = $this->createMock(CalendarEventService::class);
        $this->notificationService = $this->createMock(NotificationService::class);

        $this->controller = new CalendarController(
            $this->entityManager,
            $emailService,
            $participationRepo,
            $calendarEventService,
            $this->notificationService,
        );

        // Set up authenticated user
        $this->user = $this->createMock(User::class);
        $this->user->method('getId')->willReturn(1);
        $this->user->method('getFullName')->willReturn('Max Mustermann');

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $this->authChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $container = new ContainerBuilder();
        $container->set('security.token_storage', $tokenStorage);
        $container->set('security.authorization_checker', $this->authChecker);

        $this->controller->setContainer($container);
    }

    public function testCancelEventForbiddenReturns403(): void
    {
        $event = new CalendarEvent();
        $event->setTitle('Training');

        $this->authChecker->method('isGranted')
            ->with(CalendarEventVoter::CANCEL, $event)
            ->willReturn(false);

        $request = new Request(content: json_encode(['reason' => 'Regen']));

        $response = $this->controller->cancelEvent($event, $request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Forbidden', $data['error']);
    }

    public function testCancelEventAlreadyCancelledReturns400(): void
    {
        $event = new CalendarEvent();
        $event->setTitle('Training');
        $event->setCancelled(true);

        $this->authChecker->method('isGranted')->willReturn(true);

        $request = new Request(content: json_encode(['reason' => 'Regen']));

        $response = $this->controller->cancelEvent($event, $request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('bereits abgesagt', $data['error']);
    }

    public function testCancelEventWithoutReasonReturns400(): void
    {
        $event = new CalendarEvent();
        $event->setTitle('Training');

        $this->authChecker->method('isGranted')->willReturn(true);

        $request = new Request(content: json_encode(['reason' => '']));

        $response = $this->controller->cancelEvent($event, $request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('Grund', $data['error']);
    }

    public function testCancelEventWithWhitespaceReasonReturns400(): void
    {
        $event = new CalendarEvent();
        $event->setTitle('Training');

        $this->authChecker->method('isGranted')->willReturn(true);

        $request = new Request(content: json_encode(['reason' => '   ']));

        $response = $this->controller->cancelEvent($event, $request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testCancelEventWithMissingReasonKeyReturns400(): void
    {
        $event = new CalendarEvent();
        $event->setTitle('Training');

        $this->authChecker->method('isGranted')->willReturn(true);

        $request = new Request(content: json_encode([]));

        $response = $this->controller->cancelEvent($event, $request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testCancelEventSuccessUpdatesEntityAndReturnsSuccess(): void
    {
        $event = new CalendarEvent();
        $event->setTitle('Training');
        $startDate = new DateTime('2025-06-15 18:00');
        $event->setStartDate($startDate);

        $this->authChecker->method('isGranted')->willReturn(true);
        $this->entityManager->expects($this->once())->method('flush');

        // No permissions, no game — so no recipients
        $request = new Request(content: json_encode(['reason' => 'Platzsperrung']));

        $response = $this->controller->cancelEvent($event, $request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertSame(0, $data['recipientCount']);

        // Verify entity state changed
        $this->assertTrue($event->isCancelled());
        $this->assertSame('Platzsperrung', $event->getCancelReason());
        $this->assertSame($this->user, $event->getCancelledBy());
    }

    public function testCancelEventSendsNotificationsToRecipients(): void
    {
        $event = new CalendarEvent();
        $event->setTitle('Wichtiges Spiel');
        $startDate = new DateTime('2025-06-15 18:00');
        $event->setStartDate($startDate);

        // Add a user permission so there's at least one recipient
        $recipientUser = $this->createMock(User::class);
        $recipientUser->method('getId')->willReturn(42);

        $permission = new \App\Entity\CalendarEventPermission();
        $permission->setPermissionType(\App\Enum\CalendarEventPermissionType::USER);
        $permission->setUser($recipientUser);
        $event->addPermission($permission);

        $this->authChecker->method('isGranted')->willReturn(true);
        $this->entityManager->method('flush');

        // Mock the TeamRide repository to return empty
        $teamRideRepo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $teamRideRepo->method('findBy')->willReturn([]);
        $this->entityManager->method('getRepository')->willReturn($teamRideRepo);

        $this->notificationService->expects($this->once())
            ->method('createNotificationForUsers')
            ->with(
                $this->callback(function ($users) {
                    return 1 === count($users) && 42 === $users[0]->getId();
                }),
                'event_cancelled',
                $this->stringContains('Absage'),
                $this->stringContains('Platzsperrung'),
                $this->isArray(),
            );

        $request = new Request(content: json_encode(['reason' => 'Platzsperrung']));

        $response = $this->controller->cancelEvent($event, $request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($data['success']);
        $this->assertSame(1, $data['recipientCount']);
    }

    public function testCancelEventExcludesCancellerFromRecipients(): void
    {
        $event = new CalendarEvent();
        $event->setTitle('Training');
        $event->setStartDate(new DateTime('2025-06-15 18:00'));

        // Add the cancelling user (ID 1) as a permission recipient
        $permission = new \App\Entity\CalendarEventPermission();
        $permission->setPermissionType(\App\Enum\CalendarEventPermissionType::USER);
        $permission->setUser($this->user); // The canceller
        $event->addPermission($permission);

        $this->authChecker->method('isGranted')->willReturn(true);
        $this->entityManager->method('flush');

        $teamRideRepo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $teamRideRepo->method('findBy')->willReturn([]);
        $this->entityManager->method('getRepository')->willReturn($teamRideRepo);

        // Notification should NOT be called because the only recipient is the canceller
        $this->notificationService->expects($this->never())
            ->method('createNotificationForUsers');

        $request = new Request(content: json_encode(['reason' => 'Grund']));

        $response = $this->controller->cancelEvent($event, $request);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(0, $data['recipientCount']);
    }
}
