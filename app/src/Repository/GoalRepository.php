<?php

namespace App\Repository;

use App\Entity\Goal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Goal>
 */
class GoalRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Goal::class);
    }

    /**
     * @return Goal[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('g')
            ->select('g', 'p', 'a', 'ga', 't')
            ->leftJoin('g.scorer', 'p')
            ->leftJoin('g.assistBy', 'a')
            ->leftJoin('g.game', 'ga')
            ->leftJoin('ga.homeTeam', 't')
            ->orderBy('ga.date', 'DESC')
            ->addOrderBy('g.minute', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Goal[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('g')
            ->select('g.id, g.minute, g.ownGoal, g.penalty')
            ->addSelect('p.id as scorer_id, p.firstName as scorer_firstName, p.lastName as scorer_lastName')
            ->addSelect('a.id as assist_id, a.firstName as assist_firstName, a.lastName as assist_lastName')
            ->addSelect('ga.id as game_id, ga.date as game_date')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->leftJoin('g.scorer', 'p')
            ->leftJoin('g.assistBy', 'a')
            ->leftJoin('g.game', 'ga')
            ->leftJoin('ga.homeTeam', 't')
            ->orderBy('ga.date', 'DESC')
            ->addOrderBy('g.minute', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('g')
            ->select('g', 'p', 'a', 'ga', 't')
            ->leftJoin('g.scorer', 'p')
            ->leftJoin('g.assistBy', 'a')
            ->leftJoin('g.game', 'ga')
            ->leftJoin('ga.homeTeam', 't')
            ->where('g.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('g')
            ->select('g.id, g.minute, g.ownGoal, g.penalty')
            ->addSelect('p.id as scorer_id, p.firstName as scorer_firstName, p.lastName as scorer_lastName')
            ->addSelect('a.id as assist_id, a.firstName as assist_firstName, a.lastName as assist_lastName')
            ->addSelect('ga.id as game_id, ga.date as game_date')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->leftJoin('g.scorer', 'p')
            ->leftJoin('g.assistBy', 'a')
            ->leftJoin('g.game', 'ga')
            ->leftJoin('ga.homeTeam', 't')
            ->where('g.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
