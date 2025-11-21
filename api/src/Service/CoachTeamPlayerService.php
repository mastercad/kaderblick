<?php

namespace App\Service;

use App\Entity\Coach;
use App\Entity\Team;
use App\Entity\User;
use DateTime;
use DateTimeInterface;

class CoachTeamPlayerService
{
    /**
     * Ermittelt alle Teams, die einem User als Coach zugeordnet sind.
     *
     * @return array<Team>
     */
    public function collectCoachTeams(User $user): array
    {
        $teams = [];

        // Alle UserRelations des Users durchgehen, wo er als Coach verknüpft ist
        foreach ($user->getUserRelations() as $relation) {
            $coach = $relation->getCoach();
            if ($coach && 'coach' === $relation->getRelationType()->getCategory()) {
                // Alle aktuellen Team-Zuordnungen des Coaches ermitteln
                foreach ($coach->getCoachTeamAssignments() as $assignment) {
                    if ($this->isCurrentAssignment($assignment->getStartDate(), $assignment->getEndDate())) {
                        $team = $assignment->getTeam();
                        if (!isset($teams[$team->getId()])) {
                            $teams[$team->getId()] = $team;
                        }
                    }
                }
            }
        }

        return $teams;
    }

    /**
     * Ermittelt alle Spieler eines Teams, die aktuell aktiv sind.
     *
     * @return list<array{player: array{id: int|null, name: string}, shirtNumber: int|null}>
     */
    public function collectTeamPlayers(Team $team): array
    {
        $players = [];

        foreach ($team->getPlayerTeamAssignments() as $assignment) {
            if ($this->isCurrentAssignment($assignment->getStartDate(), $assignment->getEndDate())) {
                $player = $assignment->getPlayer();
                $players[] = [
                    'player' => ['id' => $player->getId(), 'name' => $player->getFullName()],
                    'shirtNumber' => $assignment->getShirtNumber()
                ];
            }
        }

        return $players;
    }

    /**
     * Ermittelt alle verfügbaren Spieler für einen User basierend auf seinen Coach-Beziehungen.
     * Wenn der User nur ein Team hat, werden direkt die Spieler zurückgegeben.
     * Wenn mehrere Teams vorhanden sind, wird ein Array mit Teams als Keys und Spielern als Values zurückgegeben.
     *
     * @return array<mixed>
     */
    public function resolveAvailablePlayersForCoach(User $user): array
    {
        $teams = $this->collectCoachTeams($user);

        if (0 === count($teams)) {
            return [
                'singleTeam' => true,
                'teams' => [],
                'players' => []
            ];
        }

        if (1 === count($teams)) {
            // Nur ein Team - direkt die Spieler zurückgeben
            $team = reset($teams);

            return [
                'singleTeam' => true,
                'teams' => array_map(fn (Team $team) => [
                    'id' => $team->getId(),
                    'name' => $team->getName()
                ], $teams),
                'players' => $this->collectTeamPlayers($team)
            ];
        }

        // Mehrere Teams - gruppierte Struktur zurückgeben
        $teamPlayers = [];
        foreach ($teams as $team) {
            $teamPlayers[] = [
                'team' => ['id' => $team->getId(), 'name' => $team->getName()],
                'players' => $this->collectTeamPlayers($team)
            ];
        }

        return [
            'singleTeam' => false,
            'teams' => array_map(
                fn (Team $team) => [
                    'id' => $team->getId(),
                    'name' => $team->getName()
                ],
                $teams
            ),
            'players' => $teamPlayers
        ];
    }

    /**
     * Prüft ob eine Zuordnung aktuell aktiv ist.
     */
    private function isCurrentAssignment(?DateTimeInterface $startDate, ?DateTimeInterface $endDate): bool
    {
        $now = new DateTime();

        // Wenn kein Startdatum gesetzt ist, als aktiv betrachten
        if (!$startDate) {
            return true;
        }

        // Zuordnung ist aktiv wenn:
        // - Startdatum <= heute
        // - und (kein Enddatum gesetzt ODER Enddatum >= heute)
        return $startDate <= $now && (!$endDate || $endDate >= $now);
    }

    /**
     * Ermittelt die Coach-Entität eines Users, falls vorhanden.
     */
    public function resolveUserCoach(User $user): ?Coach
    {
        foreach ($user->getUserRelations() as $relation) {
            if (
                $relation->getCoach()
                && 'coach' === $relation->getRelationType()->getCategory()
                && 'self_coach' === $relation->getRelationType()->getIdentifier()
            ) {
                return $relation->getCoach();
            }
        }

        return null;
    }
}
