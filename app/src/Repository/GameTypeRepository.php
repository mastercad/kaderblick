<?php

namespace App\Repository;

use App\Entity\GameType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class GameTypeRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameType::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('gt')
            ->select('gt', 'g', 'ht', 'at')
            ->leftJoin('gt.games', 'g')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->orderBy('gt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('gt')
            ->select('gt.id, gt.name, gt.description')
            ->addSelect('COUNT(g.id) as gameCount')
            ->leftJoin('gt.games', 'g')
            ->groupBy('gt.id')
            ->orderBy('gt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('gt')
            ->select('gt', 'g', 'ht', 'at')
            ->leftJoin('gt.games', 'g')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->where('gt.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('gt')
            ->select('gt.id, gt.name, gt.description')
            ->addSelect('g.id as game_id, g.date')
            ->addSelect('ht.name as home_team_name')
            ->addSelect('at.name as away_team_name')
            ->leftJoin('gt.games', 'g')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->where('gt.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
