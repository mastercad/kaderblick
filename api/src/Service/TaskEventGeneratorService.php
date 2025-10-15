<?php

namespace App\Service;

use App\Entity\CalendarEvent;
use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\Task;
use App\Repository\CalendarEventRepository;
use App\Repository\CalendarEventTypeRepository;
use App\Repository\UserRelationRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class TaskEventGeneratorService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CalendarEventTypeRepository $calendarEventTypeRepository,
        private CalendarEventRepository $calendarEventRepository,
        private UserRelationRepository $userRelationRepository
    ) {
    }

    /**
     * Zentrale Methode: Erzeugt alle relevanten Events für einen Task, egal ob classic, per_match oder beides.
     */
    public function generateEvents(Task $task): void
    {
        // Classic-Events (wiederkehrend nach Regel)
        if ($task->isRecurring() && 'classic' === $task->getRecurrenceMode() && $task->getRecurrenceRule()) {
            $this->generateClassicEvents($task);
        }
        // Spielplan-gebundene Events
        if ('per_match' === $task->getRecurrenceMode()) {
            $this->generatePerMatchEvents($task);
        }
    }

    public function generatePerMatchEvents(Task $task): void
    {
        if ('per_match' !== $task->getRecurrenceMode()) {
            return;
        }
        $aufgabeType = $this->calendarEventTypeRepository->findOneBy(['name' => 'Aufgabe']);
        $spielType = $this->calendarEventTypeRepository->findOneBy(['name' => 'Spiel']);

        if (!$aufgabeType || !$spielType) {
            return;
        }

        $spielEvents = $this->calendarEventRepository->createQueryBuilder('ce')
            ->where('ce.calendarEventType = :spielType')
            ->setParameter('spielType', $spielType)
            ->getQuery()->getResult();

        foreach ($task->getRotationUsers() as $user) {
            foreach ($spielEvents as $spielEvent) {
                $game = $spielEvent->getGame();
                if (!$game) {
                    continue;
                }
                $userRelations = $this->userRelationRepository->findBy(['user' => $user]);
                $userTeams = [];
                foreach ($userRelations as $rel) {
                    if ($rel->getPlayer() instanceof Player) {
                        foreach ($rel->getPlayer()->getPlayerTeamAssignments() as $assignment) {
                            $userTeams[$assignment->getTeam()->getId()] = $assignment->getTeam();
                        }
                    }
                    if ($rel->getCoach() instanceof Coach) {
                        foreach ($rel->getCoach()->getCoachTeamAssignments() as $assignment) {
                            $userTeams[$assignment->getTeam()->getId()] = $assignment->getTeam();
                        }
                    }
                }
                if (in_array($game->getHomeTeam(), $userTeams, true) || in_array($game->getAwayTeam(), $userTeams, true)) {
                    $exists = $this->calendarEventRepository->findOneBy([
                        'calendarEventType' => $aufgabeType,
                        'startDate' => $spielEvent->getStartDate(),
                        'title' => $task->getTitle(),
                        'location' => $spielEvent->getLocation(),
                    ]);
                    if (!$exists) {
                        $aufgabeEvent = new CalendarEvent();
                        $aufgabeEvent->setTitle($task->getTitle());
                        $aufgabeEvent->setDescription($task->getDescription());
                        $aufgabeEvent->setStartDate($spielEvent->getStartDate());
                        $aufgabeEvent->setEndDate($spielEvent->getEndDate());
                        $aufgabeEvent->setCalendarEventType($aufgabeType);
                        $aufgabeEvent->setLocation($spielEvent->getLocation());
                        $this->em->persist($aufgabeEvent);
                    }
                }
            }
        }
        $this->em->flush();
    }

    public function generateClassicEvents(Task $task): void
    {
        if ('classic' !== $task->getRecurrenceMode() || !$task->isRecurring()) {
            return;
        }
        $aufgabeType = $this->calendarEventTypeRepository->findOneBy(['name' => 'Aufgabe']);
        if (!$aufgabeType) {
            return;
        }
        // Recurrence-Rule parsen (angenommen als JSON: {freq, interval, byday, bymonthday})
        $rule = json_decode($task->getRecurrenceRule() ?? '', true);
        if (!$rule || !isset($rule['freq']) || !isset($rule['interval'])) {
            return;
        }
        $startDate = new DateTimeImmutable();
        $dates = [];
        // Nur ein einfaches Beispiel für die nächsten 12 Vorkommen
        for ($i = 0; $i < 12; ++$i) {
            if ('WEEKLY' === $rule['freq']) {
                $date = $startDate->modify('+' . ($i * $rule['interval']) . ' week');
                if (isset($rule['byday'])) {
                    // byday als z.B. 'MO', 'TU', ...
                    $weekday = $rule['byday'];
                    $date = $date->modify('next ' . $this->weekdayToString($weekday[0]));
                }
                $dates[] = $date;
            } elseif ('MONTHLY' === $rule['freq']) {
                $date = $startDate->modify('+' . ($i * $rule['interval']) . ' month');
                if (isset($rule['bymonthday'])) {
                    $date = $date->setDate((int) $date->format('Y'), (int) $date->format('m'), (int) $rule['bymonthday']);
                }
                $dates[] = $date;
            } elseif ('DAILY' === $rule['freq']) {
                $dates[] = $startDate->modify('+' . ($i * $rule['interval']) . ' day');
            }
        }
        $users = $task->getRotationUsers()->toArray();
        $rotationCount = $task->getRotationCount() ?? 1;
        $userIndex = 0;
        foreach ($dates as $date) {
            // Für jede Wiederholung: Benutzer im Rotationsprinzip zuweisen
            for ($j = 0; $j < $rotationCount; ++$j) {
                if (empty($users)) {
                    break;
                }
                $user = $users[($userIndex++) % count($users)];
                $exists = $this->calendarEventRepository->findOneBy([
                    'calendarEventType' => $aufgabeType,
                    'startDate' => $date,
                    'title' => $task->getTitle(),
                ]);
                if (!$exists) {
                    $aufgabeEvent = new CalendarEvent();
                    $aufgabeEvent->setTitle($task->getTitle());
                    $aufgabeEvent->setDescription($task->getDescription());
                    $aufgabeEvent->setStartDate($date);
                    $aufgabeEvent->setCalendarEventType($aufgabeType);
                    $this->em->persist($aufgabeEvent);
                }
            }
        }
        $this->em->flush();
    }

    private function weekdayToString(string $weekday): string
    {
        $map = [
            'MO' => 'Monday',
            'TU' => 'Tuesday',
            'WE' => 'Wednesday',
            'TH' => 'Thursday',
            'FR' => 'Friday',
            'SA' => 'Saturday',
            'SU' => 'Sunday',
        ];

        return $map[$weekday] ?? 'Monday';
    }
}
