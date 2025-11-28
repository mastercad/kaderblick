<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\GameEvent;
use App\Entity\PlayerTitle;
use App\Entity\Team;
use App\Repository\PlayerTitleRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class TitleCalculationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PlayerTitleRepository $playerTitleRepository
    ) {
    }

    /**
     * Calculate and award top scorer titles platform-wide.
     *
     * @return array<string, mixed>
     */
    public function calculatePlatformTopScorers(?string $season = null): array
    {
        $goals = $this->debugGoalsForSeason($season);
        $playerGoals = [];
        foreach ($goals as $goal) {
            $player = $goal->getPlayer();
            if (!$player) {
                continue;
            }
            $pid = $player->getId();
            if (!isset($playerGoals[$pid])) {
                $playerGoals[$pid] = [
                    'player' => $player,
                    'goal_count' => 0
                ];
            }
            ++$playerGoals[$pid]['goal_count'];
        }
        // Sort by goal_count DESC, then by player name ASC for tie-breaker
        usort($playerGoals, function ($a, $b) {
            if ($b['goal_count'] === $a['goal_count']) {
                return strcmp($a['player']->getLastName(), $b['player']->getLastName());
            }

            return $b['goal_count'] <=> $a['goal_count'];
        });

        // Vor Vergabe: alle alten Titel für diese Saison/Kategorie/Scope deaktivieren
        $this->playerTitleRepository->deactivateAllTitlesForCategoryAndScope('top_scorer', 'platform', null, $season);

        return $this->awardTitlesPerPlayerFromArray($playerGoals, 'top_scorer', 'platform', null, $season);
    }

    /**
     * Calculate and award top scorer titles per team.
     *
     * @return array<string, mixed>
     */
    public function calculateTeamTopScorers(Team $team, ?string $season = null): array
    {
        $goals = $this->debugGoalsForSeason($season, $team);
        $playerGoals = [];
        foreach ($goals as $goal) {
            $player = $goal->getPlayer();
            if (!$player) {
                continue;
            }
            $pid = $player->getId();
            if (!isset($playerGoals[$pid])) {
                $playerGoals[$pid] = [
                    'player' => $player,
                    'goal_count' => 0
                ];
            }
            ++$playerGoals[$pid]['goal_count'];
        }
        usort($playerGoals, function ($a, $b) {
            if ($b['goal_count'] === $a['goal_count']) {
                return strcmp($a['player']->getLastName(), $b['player']->getLastName());
            }

            return $b['goal_count'] <=> $a['goal_count'];
        });

        // Vor Vergabe: alle alten Titel für diese Saison/Kategorie/Scope/Team deaktivieren
        $this->playerTitleRepository->deactivateAllTitlesForCategoryAndScope('top_scorer', 'team', $team->getId(), $season);

        return $this->awardTitlesPerPlayerFromArray($playerGoals, 'top_scorer', 'team', $team, $season);
    }

    /**
     * Vergibt Titel pro Player (nicht pro User) und behandelt Gleichstände korrekt, aus Array mit Player-Objekten.
     *
     * @param array<int, array<string, mixed>> $playerGoals Array mit ['player' => Player, 'goal_count' => int]
     *
     * @return PlayerTitle[]
     */
    private function awardTitlesPerPlayerFromArray(
        array $playerGoals,
        string $titleCategory,
        string $titleScope,
        ?Team $team = null,
        ?string $season = null
    ): array {
        $ranks = ['gold', 'silver', 'bronze'];
        $awarded = [];
        $rankIndex = 0;
        $lastGoalCount = null;
        $currentRank = 0;
        foreach ($playerGoals as $index => $row) {
            if ($rankIndex > 2) {
                break;
            }
            $player = $row['player'];
            $value = $row['goal_count'];
            if (0 === $value) {
                continue;
            }
            if (null === $lastGoalCount || $value < $lastGoalCount) {
                $currentRank = $rankIndex;
            }
            if (!isset($ranks[$currentRank])) {
                break;
            }
            $rank = $ranks[$currentRank];
            $lastGoalCount = $value;
            ++$rankIndex;

            // Prüfe, ob ein identischer aktiver Titel bereits existiert
            $existing = $this->entityManager->getRepository(PlayerTitle::class)->findOneBy([
                'player' => $player,
                'titleCategory' => $titleCategory,
                'titleScope' => $titleScope,
                'titleRank' => $rank,
                'team' => $team,
                'season' => $season,
                'isActive' => true,
            ]);
            if ($existing) {
                $awarded[] = $existing;
                continue;
            }

            $title = new PlayerTitle();
            $title->setPlayer($player);
            $title->setTitleCategory($titleCategory);
            $title->setTitleScope($titleScope);
            $title->setTitleRank($rank);
            $title->setTeam($team);
            $title->setValue($value);
            $title->setIsActive(true);
            $title->setAwardedAt(new DateTimeImmutable());
            $title->setSeason($season);

            $this->entityManager->persist($title);
            $awarded[] = $title;
        }
        $this->entityManager->flush();

        return $awarded;
    }

    /**
     * Calculate and award titles for all teams.
     *
     * @return array<string, mixed>
     */
    public function calculateAllTeamTopScorers(?string $season = null): array
    {
        $teams = $this->entityManager->getRepository(Team::class)->findAll();
        $awarded = [];

        foreach ($teams as $team) {
            $teamAwarded = $this->calculateTeamTopScorers($team, $season);
            $awarded = array_merge($awarded, $teamAwarded);
        }

        return $awarded;
    }

    /**
     * Get current season string (e.g., '2024/2025').
     */
    public function retrieveCurrentSeason(): string
    {
        $now = new DateTime();
        $year = (int) $now->format('Y');
        $month = (int) $now->format('m');

        // Season starts in July
        if ($month >= 7) {
            return sprintf('%d/%d', $year, $year + 1);
        }

        return sprintf('%d/%d', $year - 1, $year);
    }

    /**
     * Debug-Ausgabe: Zeigt alle gezählten Tore (GameEvents mit code 'goal') für Spiele (CalendarEvents mit Typ 'Spiel') in der Saison.
     *
     * @return GameEvent[]
     */
    public function debugGoalsForSeason(?string $season = null, ?Team $team = null): array
    {
        $qb = $this->entityManager->getRepository('App\\Entity\\GameEvent')->createQueryBuilder('ge')
            ->select('ge', 'player', 'game', 'ce', 'cet', 'team')
            ->leftJoin('ge.player', 'player')
            ->leftJoin('ge.game', 'game')
            ->leftJoin('game.calendarEvent', 'ce')
            ->leftJoin('ce.calendarEventType', 'cet')
            ->leftJoin('ge.team', 'team')
            ->leftJoin('ge.gameEventType', 'getype')
            ->where('getype.code = :goalCode')
            ->andWhere('cet.name = :eventTypeName')
            ->setParameter('goalCode', 'goal')
            ->setParameter('eventTypeName', 'Spiel');

        if ($season) {
            [$startYear, $endYear] = explode('/', $season);
            $startDate = sprintf('%d-07-01 00:00:00', (int) $startYear);
            $endDate = sprintf('%d-06-30 23:59:59', (int) $endYear);
            $qb->andWhere('ce.startDate >= :startDate AND ce.startDate <= :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }
        if ($team) {
            $qb->andWhere('team.id = :teamId')->setParameter('teamId', $team->getId());
        }

        $qb->orderBy('ce.startDate', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
