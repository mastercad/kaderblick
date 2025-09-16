<?php

namespace App\Repository;

use App\Entity\TeamGameStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<TeamGameStats>
 *
 * @implements OptimizedRepositoryInterface<TeamGameStats>
 */
class TeamGameStatsRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamGameStats::class);
    }

    /**
     * @return TeamGameStats[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('tgs')
            ->select('tgs', 't', 'g', 'ht', 'at')
            ->leftJoin('tgs.team', 't')
            ->leftJoin('tgs.game', 'g')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->orderBy('g.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TeamGameStats[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('tgs')
            ->select('tgs.id, tgs.possession, tgs.shots, tgs.shotsOnTarget, tgs.corners, tgs.fouls')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->addSelect('g.id as game_id, g.date as game_date')
            ->addSelect('ht.name as home_team_name')
            ->addSelect('at.name as away_team_name')
            ->leftJoin('tgs.team', 't')
            ->leftJoin('tgs.game', 'g')
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
        return $this->createQueryBuilder('tgs')
            ->select('tgs', 't', 'g', 'ht', 'at')
            ->leftJoin('tgs.team', 't')
            ->leftJoin('tgs.game', 'g')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->where('tgs.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('tgs')
            ->select('tgs.id, tgs.possession, tgs.shots, tgs.shotsOnTarget, tgs.corners, tgs.fouls')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->addSelect('g.id as game_id, g.date as game_date, g.homeScore, g.awayScore')
            ->addSelect('ht.name as home_team_name')
            ->addSelect('at.name as away_team_name')
            ->leftJoin('tgs.team', 't')
            ->leftJoin('tgs.game', 'g')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->where('tgs.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
