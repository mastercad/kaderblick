<?php

namespace App\Service;

use App\Entity\Club;
use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\Team;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class DataConsistencyService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Prüft die Datenkonsistenz und gibt gefundene Probleme zurück.
     *
     * @return array{
     *     'coachTeamWithoutClub': array<array{coach: Coach, team: Team, missingClubs: array<Club>}>,
     *     'playerTeamWithoutClub': array<array{player: Player, team: Team, missingClubs: array<Club>}>,
     *     'summary': array{totalIssues: int, categories: array<string, int>}
     * }
     */
    public function checkConsistency(): array
    {
        $issues = [
            'coachTeamWithoutClub' => [],
            'playerTeamWithoutClub' => [],
            'summary' => [
                'totalIssues' => 0,
                'categories' => []
            ]
        ];

        // 1. Coaches die Teams aber nicht den entsprechenden Verein zugeordnet sind
        $issues['coachTeamWithoutClub'] = $this->findCoachesWithTeamButMissingClub();

        // 2. Spieler die Teams aber nicht den entsprechenden Verein zugeordnet sind
        $issues['playerTeamWithoutClub'] = $this->findPlayersWithTeamButMissingClub();

        // Summary berechnen
        $issues['summary']['categories']['coachTeamWithoutClub'] = count($issues['coachTeamWithoutClub']);
        $issues['summary']['categories']['playerTeamWithoutClub'] = count($issues['playerTeamWithoutClub']);
        $issues['summary']['totalIssues'] = array_sum($issues['summary']['categories']);

        return $issues;
    }

    /**
     * @return array<array{coach: Coach, team: Team, missingClubs: array<Club>}>
     */
    private function findCoachesWithTeamButMissingClub(): array
    {
        $issues = [];

        $coaches = $this->entityManager->getRepository(Coach::class)->findAll();

        foreach ($coaches as $coach) {
            // Alle Teams des Coaches sammeln
            $assignedTeams = [];
            foreach ($coach->getCoachTeamAssignments() as $assignment) {
                $assignedTeams[] = $assignment->getTeam();
            }

            // Alle Vereine des Coaches sammeln
            $assignedClubIds = [];
            foreach ($coach->getCoachClubAssignments() as $assignment) {
                $assignedClubIds[] = $assignment->getClub()->getId();
            }

            // Prüfen welche Vereine über Teams zugeordnet sein sollten
            foreach ($assignedTeams as $team) {
                $missingClubs = [];
                foreach ($team->getClubs() as $club) {
                    if (!in_array($club->getId(), $assignedClubIds)) {
                        $missingClubs[] = $club;
                    }
                }

                if (!empty($missingClubs)) {
                    $issues[] = [
                        'coach' => $coach,
                        'team' => $team,
                        'missingClubs' => $missingClubs
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * @return array<array{player: Player, team: Team, missingClubs: array<Club>}>
     */
    private function findPlayersWithTeamButMissingClub(): array
    {
        $issues = [];

        $players = $this->entityManager->getRepository(Player::class)->findAll();

        foreach ($players as $player) {
            // Alle Teams des Spielers sammeln
            $assignedTeams = [];
            foreach ($player->getPlayerTeamAssignments() as $assignment) {
                $assignedTeams[] = $assignment->getTeam();
            }

            // Alle Vereine des Spielers sammeln
            $assignedClubIds = [];
            foreach ($player->getPlayerClubAssignments() as $assignment) {
                $assignedClubIds[] = $assignment->getClub()->getId();
            }

            // Prüfen welche Vereine über Teams zugeordnet sein sollten
            foreach ($assignedTeams as $team) {
                $missingClubs = [];
                foreach ($team->getClubs() as $club) {
                    if (!in_array($club->getId(), $assignedClubIds)) {
                        $missingClubs[] = $club;
                    }
                }

                if (!empty($missingClubs)) {
                    $issues[] = [
                        'player' => $player,
                        'team' => $team,
                        'missingClubs' => $missingClubs
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * Automatische Korrektur: Erstellt fehlende Coach-Club-Zuordnungen.
     */
    public function autoFixCoachClubAssignments(): int
    {
        $issues = $this->findCoachesWithTeamButMissingClub();
        $fixed = 0;

        foreach ($issues as $issue) {
            $coach = $issue['coach'];
            foreach ($issue['missingClubs'] as $club) {
                // Neue CoachClubAssignment erstellen
                $assignment = new \App\Entity\CoachClubAssignment();
                $assignment->setCoach($coach);
                $assignment->setClub($club);
                $assignment->setStartDate(new DateTime());

                $this->entityManager->persist($assignment);
                ++$fixed;
            }
        }

        $this->entityManager->flush();

        return $fixed;
    }

    /**
     * Automatische Korrektur: Erstellt fehlende Player-Club-Zuordnungen.
     */
    public function autoFixPlayerClubAssignments(): int
    {
        $issues = $this->findPlayersWithTeamButMissingClub();
        $fixed = 0;

        foreach ($issues as $issue) {
            $player = $issue['player'];
            foreach ($issue['missingClubs'] as $club) {
                // Neue PlayerClubAssignment erstellen
                $assignment = new \App\Entity\PlayerClubAssignment();
                $assignment->setPlayer($player);
                $assignment->setClub($club);
                $assignment->setStartDate(new DateTime());

                $this->entityManager->persist($assignment);
                ++$fixed;
            }
        }

        $this->entityManager->flush();

        return $fixed;
    }
}
