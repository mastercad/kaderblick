<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Goal;
use App\Entity\Team;
use App\Entity\UserTitle;
use App\Repository\UserTitleRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class TitleCalculationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserTitleRepository $userTitleRepository
    ) {
    }

    /**
     * Calculate and award top scorer titles platform-wide
     */
    public function calculatePlatformTopScorers(?string $season = null): array
    {
        $qb = $this->entityManager->getRepository(Goal::class)
            ->createQueryBuilder('g')
            ->select('p.id as player_id, COUNT(g.id) as goal_count')
            ->innerJoin('g.scorer', 'p')
            ->innerJoin('p.userRelations', 'ur')
            ->innerJoin('ur.user', 'u')
            ->where('u.isEnabled = true')
            ->groupBy('p.id')
            ->orderBy('goal_count', 'DESC')
            ->setMaxResults(3);

        $results = $qb->getQuery()->getResult();
        
        return $this->awardTitles($results, 'top_scorer', 'platform', null, $season);
    }

    /**
     * Calculate and award top scorer titles per team
     */
    public function calculateTeamTopScorers(Team $team, ?string $season = null): array
    {
        $qb = $this->entityManager->getRepository(Goal::class)
            ->createQueryBuilder('g')
            ->select('p.id as player_id, COUNT(g.id) as goal_count')
            ->innerJoin('g.scorer', 'p')
            ->innerJoin('p.userRelations', 'ur')
            ->innerJoin('ur.user', 'u')
            ->innerJoin('p.playerTeamAssignments', 'pta')
            ->where('u.isEnabled = true')
            ->andWhere('pta.team = :team')
            ->setParameter('team', $team)
            ->groupBy('p.id')
            ->orderBy('goal_count', 'DESC')
            ->setMaxResults(3);

        $results = $qb->getQuery()->getResult();
        
        return $this->awardTitles($results, 'top_scorer', 'team', $team, $season);
    }

    /**
     * Calculate and award titles for all teams
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
     * Award titles based on results
     */
    private function awardTitles(
        array $results,
        string $titleCategory,
        string $titleScope,
        ?Team $team = null,
        ?string $season = null
    ): array {
        $ranks = ['gold', 'silver', 'bronze'];
        $awarded = [];

        foreach ($results as $index => $result) {
            if (!isset($ranks[$index])) {
                break;
            }

            $rank = $ranks[$index];
            $playerId = $result['player_id'];
            $value = (int) $result['goal_count'];

            if ($value === 0) {
                continue; // No titles for 0 goals
            }

            $player = $this->entityManager->getRepository(\App\Entity\Player::class)->find($playerId);
            if (!$player) {
                continue;
            }

            foreach ($player->getUserRelations() as $userRelation) {
                $user = $userRelation->getUser();
                if (!$user || !$user->isEnabled()) {
                    continue;
                }

                // Deactivate old titles of this category/scope
                $this->userTitleRepository->deactivateTitles(
                    $user,
                    $titleCategory,
                    $titleScope,
                    $team?->getId()
                );

                // Award new title
                $title = new UserTitle();
                $title->setUser($user);
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
        }

        $this->entityManager->flush();

        return $awarded;
    }

    /**
     * Get current season string (e.g., '2024/2025')
     */
    public function retrieveCurrentSeason(): string
    {
        $now = new \DateTime();
        $year = (int) $now->format('Y');
        $month = (int) $now->format('m');

        // Season starts in July
        if ($month >= 7) {
            return sprintf('%d/%d', $year, $year + 1);
        }

        return sprintf('%d/%d', $year - 1, $year);
    }
}
