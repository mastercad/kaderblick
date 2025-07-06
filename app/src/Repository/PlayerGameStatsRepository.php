<?php

namespace App\Repository;

use App\Entity\PlayerGameStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<PlayerGameStats>
 */
class PlayerGameStatsRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerGameStats::class);
    }

    /**
     * @return PlayerGameStats[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('pgs')
            ->select('pgs', 'p', 'g', 'ht', 'at')
            ->leftJoin('pgs.player', 'p')
            ->leftJoin('pgs.game', 'g')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->orderBy('g.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return PlayerGameStats[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('pgs')
            ->select('pgs.id, pgs.minutesPlayed, pgs.shots, pgs.shotsOnTarget, pgs.passes, pgs.passesCompleted, pgs.foulsCommitted')
            ->addSelect('p.id as player_id, p.firstName as player_firstName, p.lastName as player_lastName')
            ->addSelect('g.id as game_id, g.date as game_date')
            ->addSelect('ht.name as home_team_name')
            ->addSelect('at.name as away_team_name')
            ->leftJoin('pgs.player', 'p')
            ->leftJoin('pgs.game', 'g')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->orderBy('g.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('pgs')
            ->select('pgs', 'p', 'g', 't')
            ->leftJoin('pgs.player', 'p')
            ->leftJoin('pgs.game', 'g')
            ->leftJoin('pgs.team', 't')
            ->where('pgs.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('pgs')
            ->select('pgs.id, pgs.minutesPlayed, pgs.yellowCards, pgs.redCards')
            ->addSelect('p.id as player_id, p.firstName as player_firstName, p.lastName as player_lastName')
            ->addSelect('g.id as game_id, g.date as game_date, g.homeScore, g.awayScore')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->leftJoin('pgs.player', 'p')
            ->leftJoin('pgs.game', 'g')
            ->leftJoin('pgs.team', 't')
            ->where('pgs.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
