<?php

namespace App\Controller\Api;

use App\Entity\Player;
use App\Entity\TaskAssignment;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserRelation;
use App\Repository\CalendarEventRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Liefert eine Übersichtsseite "Mein Team" für alle Nutzer:
 * - Teams des Nutzers (über UserRelations)
 * - Spieler im Team (Kader)
 * - Nächste Termine des Teams
 * - Offene Aufgaben des Nutzers
 */
#[Route('/api/my-team', name: 'api_my_team_')]
class MyTeamController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'overview', methods: ['GET'])]
    public function overview(CalendarEventRepository $calendarEventRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        // Sammle alle Teams des Benutzers (über UserRelations → Player/Coach → TeamAssignments)
        $teams = [];
        $teamIds = [];
        $myPlayerIds = [];

        foreach ($user->getUserRelations() as $relation) {
            // Über Spieler-Verknüpfungen
            if ($relation->getPlayer()) {
                $player = $relation->getPlayer();
                $myPlayerIds[] = $player->getId();
                foreach ($player->getPlayerTeamAssignments() as $pta) {
                    $team = $pta->getTeam();
                    if (!in_array($team->getId(), $teamIds)) {
                        $teamIds[] = $team->getId();
                        $teams[] = $team;
                    }
                }
            }
            // Über Trainer-Verknüpfungen
            if ($relation->getCoach()) {
                foreach ($relation->getCoach()->getCoachTeamAssignments() as $cta) {
                    $team = $cta->getTeam();
                    if (!in_array($team->getId(), $teamIds)) {
                        $teamIds[] = $team->getId();
                        $teams[] = $team;
                    }
                }
            }
        }

        // Baue Teamdaten mit Spielerliste
        $teamsData = [];
        foreach ($teams as $team) {
            $players = $team->getCurrentPlayers();
            $playersData = [];
            foreach ($players as $player) {
                $shirtNumber = null;
                foreach ($player->getPlayerTeamAssignments() as $pta) {
                    if ($pta->getTeam()->getId() === $team->getId()) {
                        $shirtNumber = $pta->getShirtNumber();
                        break;
                    }
                }
                $playersData[] = [
                    'id' => $player->getId(),
                    'firstName' => $player->getFirstName(),
                    'lastName' => $player->getLastName(),
                    'fullName' => $player->getFullName(),
                    'shirtNumber' => $shirtNumber,
                    'mainPosition' => $player->getMainPosition() ? [ // @phpstan-ignore ternary.alwaysTrue
                        'id' => $player->getMainPosition()->getId(),
                        'name' => $player->getMainPosition()->getName(),
                    ] : null,
                    'isMe' => in_array($player->getId(), $myPlayerIds),
                ];
            }

            // Sortiere nach Trikotnummer
            usort($playersData, function ($a, $b) {
                if (null === $a['shirtNumber'] && null === $b['shirtNumber']) {
                    return 0;
                }
                if (null === $a['shirtNumber']) {
                    return 1;
                }
                if (null === $b['shirtNumber']) {
                    return -1;
                }

                return (int) $a['shirtNumber'] - (int) $b['shirtNumber'];
            });

            $teamsData[] = [
                'id' => $team->getId(),
                'name' => $team->getName(),
                'ageGroup' => $team->getAgeGroup() ? [ // @phpstan-ignore ternary.alwaysTrue
                    'id' => $team->getAgeGroup()->getId(),
                    'name' => $team->getAgeGroup()->getName(),
                ] : null,
                'league' => $team->getLeague() ? [
                    'id' => $team->getLeague()->getId(),
                    'name' => $team->getLeague()->getName(),
                ] : null,
                'players' => $playersData,
                'playerCount' => count($playersData),
            ];
        }

        // Nächste Termine (max. 5, ab jetzt)
        $now = new DateTime();
        $nextMonth = (clone $now)->modify('+30 days');
        $allEvents = $calendarEventRepository->findBetweenDates($now, $nextMonth);

        // Filtere auf Team-relevante Events (über CalendarEventPermissions)
        $upcomingEvents = [];
        foreach ($allEvents as $event) {
            // Prüfe ob Event einem der Teams des Nutzers zugeordnet ist
            $eventTeam = null;
            foreach ($event->getPermissions() as $permission) {
                $permTeam = $permission->getTeam();
                if (null !== $permTeam && in_array($permTeam->getId(), $teamIds)) {
                    $eventTeam = $permTeam;
                    break;
                }
            }

            if (null !== $eventTeam) {
                $upcomingEvents[] = [
                    'id' => $event->getId(),
                    'title' => $event->getTitle(),
                    'startDate' => $event->getStartDate()?->format('c'),
                    'endDate' => $event->getEndDate()?->format('c'),
                    'location' => $event->getLocation()?->getName(),
                    'calendarEventType' => $event->getCalendarEventType() ? [
                        'name' => $event->getCalendarEventType()->getName(),
                        'color' => $event->getCalendarEventType()->getColor(),
                    ] : null,
                    'teamId' => $eventTeam->getId(),
                    'teamName' => $eventTeam->getName(),
                ];
            }

            if (count($upcomingEvents) >= 5) {
                break;
            }
        }

        // Offene Aufgaben des Nutzers
        $taskAssignmentRepo = $this->entityManager->getRepository(TaskAssignment::class);
        $openAssignments = $taskAssignmentRepo->findBy([
            'user' => $user,
            'status' => 'offen',
        ], ['assignedDate' => 'ASC']);

        $tasksData = [];
        foreach ($openAssignments as $assignment) {
            $task = $assignment->getTask();
            $tasksData[] = [
                'id' => $assignment->getId(),
                'taskId' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'assignedDate' => $assignment->getAssignedDate()->format('c'),
                'status' => $assignment->getStatus(),
            ];
        }

        // Ermittle Rollen über UserRelations (wie ProfileController)
        $isCoach = false;
        $isPlayer = false;
        $userRelationRepo = $this->entityManager->getRepository(UserRelation::class);
        $userRelations = $userRelationRepo->findBy(['user' => $user]);
        foreach ($userRelations as $rel) {
            $relType = $rel->getRelationType();
            $category = $relType->getCategory();
            if ('coach' === $category) {
                $isCoach = true;
            }
            if ('player' === $category) {
                $isPlayer = true;
            }
        }

        return $this->json([
            'teams' => $teamsData,
            'upcomingEvents' => $upcomingEvents,
            'openTasks' => $tasksData,
            'isCoach' => $isCoach,
            'isPlayer' => $isPlayer,
        ]);
    }
}
