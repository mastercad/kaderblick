<?php

namespace App\Tests\Unit\Service;

use App\Entity\CalendarEvent;
use App\Entity\CalendarEventType;
use App\Entity\Game;
use App\Entity\Task;
use App\Entity\TaskAssignment;
use App\Entity\User;
use App\Event\GameDeletedEvent;
use App\Service\CalendarEventService;
use App\Service\TaskEventGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CalendarEventServiceTest extends TestCase
{
    public function testDeleteCalendarEventsForTaskRemovesAllRelatedEvents(): void
    {
        $task = $this->createMock(Task::class);
        $calendarEvent = $this->createMock(CalendarEvent::class);
        $taskAssignment = $this->createMock(TaskAssignment::class);
        $taskAssignment->method('getCalendarEvent')->willReturn($calendarEvent);
        $taskAssignmentRepo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();
        $taskAssignmentRepo->method('findBy')->willReturnCallback(function ($criteria) use ($task, $calendarEvent, $taskAssignment) {
            if (isset($criteria['task']) && $criteria['task'] === $task) {
                return [$taskAssignment];
            }
            if (isset($criteria['calendarEvent']) && $criteria['calendarEvent'] === $calendarEvent) {
                return [];
            }

            return [];
        });
        $teamRideRepo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();
        $teamRideRepo->method('findBy')->willReturn([]);
        $participationRepo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();
        $participationRepo->method('findBy')->willReturn([]);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnMap([
            [TaskAssignment::class, $taskAssignmentRepo],
            [\App\Entity\TeamRide::class, $teamRideRepo],
            [\App\Entity\Participation::class, $participationRepo],
        ]);
        $em->expects($this->once())->method('remove')->with($calendarEvent);
        $em->expects($this->once())->method('flush');
        $service = new CalendarEventService(
            $em,
            $this->createMock(ValidatorInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(TaskEventGeneratorService::class),
            $this->createMock(Security::class)
        );
        $service->deleteCalendarEventsForTask($task);
    }

    public function testDeleteCalendarEventsForTaskNoAssignments(): void
    {
        $task = $this->createMock(Task::class);
        $repo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();
        $repo->method('findBy')->willReturn([]);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(TaskAssignment::class)->willReturn($repo);
        $em->expects($this->never())->method('remove');
        $service = new CalendarEventService(
            $em,
            $this->createMock(ValidatorInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(TaskEventGeneratorService::class),
            $this->createMock(Security::class)
        );
        $service->deleteCalendarEventsForTask($task);
    }

    public function testDeleteCalendarEventWithDependenciesRemovesAllDependenciesAndDispatches(): void
    {
        $calendarEvent = $this->createMock(CalendarEvent::class);
        $game = $this->createMock(Game::class);
        $calendarEvent->method('getGame')->willReturn($game);
        $em = $this->createMock(EntityManagerInterface::class);
        $connection = $this->getMockBuilder(\Doctrine\DBAL\Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['executeStatement'])
            ->getMock();
        $connection->method('executeStatement')->willReturn(true);
        $em->method('getConnection')->willReturn($connection);
        $repo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();
        $repo->method('findBy')->willReturn([]);
        $em->method('getRepository')->willReturn($repo);
        $em->expects($this->atLeastOnce())->method('remove');
        $em->expects($this->once())->method('flush');
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch')->with($this->isInstanceOf(GameDeletedEvent::class));
        $service = new CalendarEventService(
            $em,
            $this->createMock(ValidatorInterface::class),
            $dispatcher,
            $this->createMock(TaskEventGeneratorService::class),
            $this->createMock(Security::class)
        );

        $service->deleteCalendarEventWithDependencies($calendarEvent);
    }

    public function testDeleteCalendarEventWithDependenciesNoGame(): void
    {
        $calendarEvent = $this->createMock(CalendarEvent::class);
        $calendarEvent->method('getGame')->willReturn(null);
        $em = $this->createMock(EntityManagerInterface::class);
        $connection = $this->getMockBuilder(\Doctrine\DBAL\Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['executeStatement'])
            ->getMock();
        $connection->method('executeStatement')->willReturn(true);
        $em->method('getConnection')->willReturn($connection);
        $repo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();
        $repo->method('findBy')->willReturn([]);
        $em->method('getRepository')->willReturn($repo);
        $em->expects($this->atLeastOnce())->method('remove');
        $em->expects($this->once())->method('flush');
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())->method('dispatch');
        $service = new CalendarEventService(
            $em,
            $this->createMock(ValidatorInterface::class),
            $dispatcher,
            $this->createMock(TaskEventGeneratorService::class),
            $this->createMock(Security::class)
        );

        $service->deleteCalendarEventWithDependencies($calendarEvent);
    }

    public function testUpdateEventFromDataValidDataCreatesAndFlushes(): void
    {
        $calendarEvent = $this->getMockBuilder(CalendarEvent::class)->onlyMethods(
            [
                'getId',
                'setTitle',
                'setDescription',
                'setStartDate',
                'setCreatedBy',
                'setCalendarEventType',
                'setEndDate',
                'setLocation',
                'getGame',
                'setGame',
                'getCalendarEventType'
            ]
        )->getMock();

        $calendarEvent->method('getId')->willReturn(null);
        $calendarEventType = $this->createMock(CalendarEventType::class);
        $calendarEventType->method('getId')->willReturn(1);
        $calendarEventType->method('getName')->willReturn('Spiel');
        $calendarEvent->method('getCalendarEventType')->willReturn($calendarEventType);
        $repoType = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneBy'])
            ->getMock();
        $repoType->method('findOneBy')->willReturnCallback(function (array $criteria) use ($calendarEventType) {
            if (isset($criteria['name']) && $criteria['name'] === 'Spiel') {
                return $calendarEventType;
            }

            return null;
        });
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repoType);
        $em->method('getReference')->willReturn($calendarEventType);
        $em->expects($this->atLeastOnce())->method('flush');
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->createMock(User::class));
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());
        $service = new CalendarEventService(
            $em,
            $validator,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(TaskEventGeneratorService::class),
            $security
        );

        $data = [
            'title' => 'Test',
            'description' => 'Desc',
            'startDate' => '2025-01-01',
            'eventTypeId' => 1
        ];
        $result = $service->updateEventFromData($calendarEvent, $data);
        $this->assertInstanceOf(ConstraintViolationList::class, $result);
    }

    public function testUpdateEventFromDataWithErrorsReturnsViolations(): void
    {
        $calendarEvent = $this->getMockBuilder(CalendarEvent::class)->onlyMethods(
            [
                'getId',
                'setTitle',
                'setDescription',
                'setStartDate',
                'setCreatedBy',
                'setCalendarEventType',
                'setEndDate',
                'setLocation',
                'getGame',
                'setGame'
            ]
        )->getMock();

        $calendarEvent->method('getId')->willReturn(null);
        $calendarEventType = $this->createMock(CalendarEventType::class);
        $calendarEventType->method('getId')->willReturn(1);
        $calendarEventType->method('getName')->willReturn('Spiel');
        $repoType = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneBy'])
            ->getMock();
        $repoType->method('findOneBy')->willReturnCallback(function (array $criteria) use ($calendarEventType) {
            if (isset($criteria['name']) && $criteria['name'] === 'Spiel') {
                return $calendarEventType;
            }

            return null;
        });
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repoType);
        $em->method('getReference')->willReturn($calendarEventType);
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->createMock(User::class));
        $violation = $this->getMockBuilder('Symfony\Component\Validator\ConstraintViolationInterface')->getMock();
        $violations = new ConstraintViolationList([$violation]);
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn($violations);
        $service = new CalendarEventService(
            $em,
            $validator,
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(TaskEventGeneratorService::class),
            $security
        );

        $data = [
            'title' => 'Test',
            'description' => 'Desc',
            'startDate' => '2025-01-01',
            'eventTypeId' => 1
        ];
        $result = $service->updateEventFromData($calendarEvent, $data);
        $this->assertSame($violations, $result);
    }

    public function testFullfillTaskEntitySetsAllFields(): void
    {
        $task = $this->createMock(Task::class);
        $calendarEvent = $this->createMock(CalendarEvent::class);
        $calendarEvent->method('getTitle')->willReturn('T');
        $calendarEvent->method('getDescription')->willReturn('D');
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->createMock(User::class));
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock());
        $task->expects($this->once())->method('setTitle')->with('T');
        $task->expects($this->once())->method('setDescription')->with('D');
        $service = new CalendarEventService(
            $em,
            $this->createMock(ValidatorInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(TaskEventGeneratorService::class),
            $security
        );

        $result = $service->fullfillTaskEntity($task, $calendarEvent, []);
        $this->assertInstanceOf(Task::class, $result);
    }

    public function testLoadEventRecipientsReturnsEmails(): void
    {
        $calendarEvent = $this->createMock(CalendarEvent::class);
        $repo = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)->disableOriginalConstructor()->onlyMethods(['createQueryBuilder'])->getMock();
        $queryBuilder = $this->getMockBuilder(\Doctrine\ORM\QueryBuilder::class)->disableOriginalConstructor()->onlyMethods(['select', 'where', 'getQuery'])->getMock();
        $query = $this->getMockBuilder(\Doctrine\ORM\Query::class)->disableOriginalConstructor()->onlyMethods(['getSingleColumnResult'])->getMock();
        $repo->method('createQueryBuilder')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        $query->method('getSingleColumnResult')->willReturn(['test@example.com']);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);
        $service = new CalendarEventService(
            $em,
            $this->createMock(ValidatorInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(TaskEventGeneratorService::class),
            $this->createMock(Security::class)
        );

        $result = $service->loadEventRecipients($calendarEvent);
        $this->assertEquals(['test@example.com'], $result);
    }
}
