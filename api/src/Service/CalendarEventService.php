<?php

namespace App\Service;

use App\Entity\CalendarEvent;
use App\Entity\CalendarEventPermission;
use App\Entity\CalendarEventType;
use App\Entity\Club;
use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\GameType;
use App\Entity\League;
use App\Entity\Location;
use App\Entity\Participation;
use App\Entity\Substitution;
use App\Entity\Task;
use App\Entity\TaskAssignment;
use App\Entity\Team;
use App\Entity\TeamRide;
use App\Entity\User;
use App\Entity\Video;
use App\Enum\CalendarEventPermissionType;
use App\Event\GameCreatedEvent;
use App\Event\GameDeletedEvent;
use App\Service\TaskEventGeneratorService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CalendarEventService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TaskEventGeneratorService $taskEventGeneratorService,
        private readonly Security $security,
    ) {
    }

    public function deleteCalendarEventsForTask(Task $task): void
    {
        $taskAssignments = $this->entityManager->getRepository(TaskAssignment::class)
            ->findBy(['task' => $task]);

        foreach ($taskAssignments as $taskAssignment) {
            $calendarEvent = $taskAssignment->getCalendarEvent();
            if ($calendarEvent) {
                $this->deleteCalendarEventWithDependencies($calendarEvent);
            }
        }
    }

    /**
     * Löscht ein CalendarEvent und alle seine Abhängigkeiten
     */
    public function deleteCalendarEventWithDependencies(CalendarEvent $calendarEvent): void
    {
        $game = $calendarEvent->getGame();

        $connection = $this->entityManager->getConnection();
        $connection->executeStatement(
            'DELETE FROM notifications WHERE JSON_EXTRACT(data, "$.eventId") = :eventId',
            ['eventId' => $calendarEvent->getId()]
        );

        $teamRideRepo = $this->entityManager->getRepository(TeamRide::class);
        $teamRides = $teamRideRepo->findBy(['event' => $calendarEvent]);
        foreach ($teamRides as $teamRide) {
            $this->entityManager->remove($teamRide);
        }

        $participationRepo = $this->entityManager->getRepository(Participation::class);
        $participations = $participationRepo->findBy(['event' => $calendarEvent]);
        foreach ($participations as $participation) {
            $this->entityManager->remove($participation);
        }

        if ($game) {
            $gameEventRepo = $this->entityManager->getRepository(GameEvent::class);
            $gameEvents = $gameEventRepo->findBy(['game' => $game]);
            foreach ($gameEvents as $gameEvent) {
                $this->entityManager->remove($gameEvent);
            }

            $substitutionRepo = $this->entityManager->getRepository(Substitution::class);
            $substitutions = $substitutionRepo->findBy(['game' => $game]);
            foreach ($substitutions as $substitution) {
                $this->entityManager->remove($substitution);
            }

            $videoRepo = $this->entityManager->getRepository(Video::class);
            $videos = $videoRepo->findBy(['game' => $game]);
            foreach ($videos as $video) {
                $this->entityManager->remove($video);
            }

            $this->entityManager->remove($game);
        }

        $taskAssignmentRepo = $this->entityManager->getRepository(TaskAssignment::class);
        $taskAssignments = $taskAssignmentRepo->findBy(['calendarEvent' => $calendarEvent]);
        foreach ($taskAssignments as $taskAssignment) {
            $this->entityManager->remove($taskAssignment);
        }

        $this->entityManager->remove($calendarEvent);
        $this->entityManager->flush();

        if ($game) {
            $this->eventDispatcher->dispatch(new GameDeletedEvent($game));
        }
    }

    /**
     * @param array<mixed> $data
     *
     * @return ConstraintViolationList<int, mixed>
     */
    public function updateEventFromData(CalendarEvent $calendarEvent, array $data): ConstraintViolationList
    {
        $calendarEventTypeSpiel = $this->entityManager->getRepository(CalendarEventType::class)->findOneBy(['name' => 'Spiel']);
        $calendarEvent->setTitle($data['title'] ?? $calendarEvent->getTitle());
        $calendarEvent->setDescription($data['description'] ?? null);
        $calendarEvent->setStartDate(new DateTime($data['startDate']));

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if (!$calendarEvent->getId()) {
            $calendarEvent->setCreatedBy($currentUser);
        }

        if ($data['eventTypeId']) {
            $type = $this->entityManager->getReference(CalendarEventType::class, $data['eventTypeId']);
            $calendarEvent->setCalendarEventType($type);
        }

        if (isset($data['endDate'])) {
            $calendarEvent->setEndDate(new DateTime($data['endDate']));
        }

        if (isset($data['locationId']) && $data['locationId']) {
            $location = $this->entityManager->getReference(Location::class, (int) $data['locationId']);
            $calendarEvent->setLocation($location);
        }

        $gameCreated = false;
        $isGameEvent = $data['eventTypeId'] && (int) $data['eventTypeId'] === $calendarEventTypeSpiel->getId();

        if ($isGameEvent) {
            if (null === $calendarEvent->getGame()) {
                $game = new Game();
                $calendarEvent->setGame($game);
                $game->setCalendarEvent($calendarEvent);
                $this->entityManager->persist($game);
                $gameCreated = true;
            }
        }

        if ($isGameEvent) {
            if (isset($data['game']['homeTeamId']) && $data['game']['homeTeamId']) {
                $homeTeam = $this->entityManager->getReference(Team::class, (int) $data['game']['homeTeamId']);
                $calendarEvent->getGame()?->setHomeTeam($homeTeam);
            }

            if (isset($data['game']['awayTeamId']) && $data['game']['awayTeamId']) {
                $awayTeam = $this->entityManager->getReference(Team::class, (int) $data['game']['awayTeamId']);
                $calendarEvent->getGame()?->setAwayTeam($awayTeam);
            }

            if (isset($data['gameTypeId']) && $data['gameTypeId']) {
                $gameType = $this->entityManager->getReference(GameType::class, (int) $data['gameTypeId']);
                $calendarEvent->getGame()?->setGameType($gameType);
            }

            if (isset($data['fussballDeUrl']) && $data['fussballDeUrl']) {
                $calendarEvent->getGame()?->setFussballDeUrl($data['fussballDeUrl']);
            }

            if (isset($data['fussballDeId']) && $data['fussballDeId']) {
                $calendarEvent->getGame()?->setFussballDeId($data['fussballDeId']);
            }

            if (isset($data['leagueId']) && $data['leagueId']) {
                $league = $this->entityManager->getReference(League::class, (int) $data['leagueId']);
                $calendarEvent->getGame()?->setLeague($league);
            }
        }

        if (isset($data['task']) && is_array($data['task'])) {
            $taskData = $data['task'];

            $task = null;
            $taskAssignment = $this->entityManager->getRepository(TaskAssignment::class)
                ->findOneBy(['calendarEvent' => $calendarEvent]);

            if ($taskAssignment && $taskAssignment->getTask()) {
                $task = $taskAssignment->getTask();
            }

            if ($task instanceof Task) {
                $task = $this->fullfillTaskEntity($task, $calendarEvent, $taskData);

                $this->entityManager->persist($task);
                $this->entityManager->flush();

                $this->taskEventGeneratorService->generateEvents($task, $currentUser);
            } else {
                $task = new Task();
                $task = $this->fullfillTaskEntity($task, $calendarEvent, $taskData);

                $this->entityManager->persist($task);
                $this->entityManager->flush();

                $this->taskEventGeneratorService->generateEvents($task, $currentUser);

                // Don't persist the original event - it was just a template (maybe let it in place for creator as reference?)
                $this->entityManager->remove($calendarEvent);
            }
        }

        /** @var ConstraintViolationList $errors */
        $errors = $this->validator->validate($calendarEvent);

        if ($calendarEvent->getGame()) {
            $gameErrors = $this->validator->validate($calendarEvent->getGame());
            foreach ($gameErrors as $gameError) {
                $errors->add($gameError);
            }
        }

        if ($errors->count()) {
            return $errors;
        }

        $this->entityManager->flush();

        if (isset($data['permissionType']) && !isset($data['task'])) {
            $this->updatePermissionsForEvent($calendarEvent, $data['permissionType'], $data);
            $this->entityManager->persist($calendarEvent);
            $this->entityManager->flush();
        } elseif (!$calendarEvent->getId() && !isset($data['task'])) {
            $this->createDefaultPermissionsForEvent($calendarEvent);
            $this->entityManager->persist($calendarEvent);
            $this->entityManager->flush();
        }

        if ($gameCreated && $calendarEvent->getGame()) {
            $this->eventDispatcher->dispatch(new GameCreatedEvent($calendarEvent->getGame()));
        }

        return new ConstraintViolationList();
    }

    /**
     * @param array<string, mixed> $taskData
     */
    public function fullfillTaskEntity(Task $task, CalendarEvent $calendarEvent, array $taskData): Task
    {
        $task->setTitle($calendarEvent->getTitle());
        $task->setDescription($calendarEvent->getDescription());
        $task->setIsRecurring($taskData['isRecurring'] ?? false);
        $task->setRecurrenceMode($taskData['recurrenceMode'] ?? 'classic');
        $task->setOffsetDays($taskData['offset'] ?? 0);

        // Set rotation users
        $rotationUsers = [];
        if (isset($taskData['rotationUsers']) && is_array($taskData['rotationUsers'])) {
            $rotationUsers = $this->entityManager->getRepository(User::class)
                ->findBy(['id' => $taskData['rotationUsers']]);
            $task->setRotationUsers(new ArrayCollection($rotationUsers));
        }

        $task->setRotationCount($taskData['rotationCount'] ?? 1);

        // Set recurrence rule for classic mode
        if ($task->isRecurring() && $task->getRecurrenceMode() === 'classic') {
            if (isset($taskData['recurrenceRule']) && $taskData['recurrenceRule']) {
                $task->setRecurrenceRule($taskData['recurrenceRule']);
            }
        }

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        $task->setCreatedBy($currentUser);

        return $task;
    }

    /**
     * Aktualisiert oder erstellt Permissions für ein Event basierend auf permissionType und IDs
     *
     * @param array<string, mixed> $data
     */
    private function updatePermissionsForEvent(CalendarEvent $calendarEvent, string $permissionType, array $data = []): void
    {
        // Lösche alle existierenden Permissions
        foreach ($calendarEvent->getPermissions() as $permission) {
            $this->entityManager->remove($permission);
        }

        if ($permissionType === 'public') {
            // Public: eine PUBLIC Permission
            $permission = new CalendarEventPermission();
            $permission->setCalendarEvent($calendarEvent);
            $permission->setPermissionType(CalendarEventPermissionType::PUBLIC);
            $this->entityManager->persist($permission);
        } elseif ($permissionType === 'user' && isset($data['permissionUsers'])) {
            // User: eine Permission pro User
            $userIds = $data['permissionUsers'];
            foreach ($userIds as $userId) {
                $user = $this->entityManager->getReference(User::class, (int) $userId);
                $permission = new CalendarEventPermission();
                $permission->setCalendarEvent($calendarEvent);
                $permission->setPermissionType(CalendarEventPermissionType::USER);
                $permission->setUser($user);
                $this->entityManager->persist($permission);
            }
        } elseif ($permissionType === 'team' && isset($data['permissionTeams'])) {
            // Team: eine Permission pro Team
            $teamIds = $data['permissionTeams'];
            foreach ($teamIds as $teamId) {
                $team = $this->entityManager->getReference(Team::class, (int) $teamId);
                $permission = new CalendarEventPermission();
                $permission->setCalendarEvent($calendarEvent);
                $permission->setPermissionType(CalendarEventPermissionType::TEAM);
                $permission->setTeam($team);
                $this->entityManager->persist($permission);
            }
        } elseif ($permissionType === 'club' && isset($data['permissionClubs'])) {
            // Club: eine Permission pro Club
            $clubIds = $data['permissionClubs'];
            foreach ($clubIds as $clubId) {
                $club = $this->entityManager->getReference(Club::class, (int) $clubId);
                $permission = new CalendarEventPermission();
                $permission->setCalendarEvent($calendarEvent);
                $permission->setPermissionType(CalendarEventPermissionType::CLUB);
                $permission->setClub($club);
                $this->entityManager->persist($permission);
            }
        }
    }

    /**
     * Erstellt Standard-Permissions für ein neues Event basierend auf dem Event-Typ
     */
    private function createDefaultPermissionsForEvent(CalendarEvent $calendarEvent): void
    {
        $eventType = $calendarEvent->getCalendarEventType();

        // Für Spiel- und Aufgaben-Events keine Standard-Permissions erstellen
        if (in_array($eventType->getName(), ['Spiel', 'Aufgabe'])) {
            return;
        }

        // Andere Events: standardmäßig öffentlich
        $permission = new CalendarEventPermission();
        $permission->setCalendarEvent($calendarEvent);
        $permission->setPermissionType(CalendarEventPermissionType::PUBLIC);
        $this->entityManager->persist($permission);
    }

    /** @return array<int, string> */
    public function loadEventRecipients(CalendarEvent $calendarEvent): array
    {
        return $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('u.email')
            ->where('u.isVerified = true')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
