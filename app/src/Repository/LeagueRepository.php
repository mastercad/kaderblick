<?php

namespace App\Repository;

use App\Entity\League;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class LeagueRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, League::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('l')
            ->select('l', 't')
            ->leftJoin('l.teams', 't')
            ->addOrderBy('l.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('l')
            ->select('l.id, l.name, t')
            ->addSelect('COUNT(t.id) as teamCount')
            ->leftJoin('l.teams', 't')
            ->groupBy('l.id')
            ->addOrderBy('l.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('l')
            ->select('l', 't')
            ->leftJoin('l.teams', 't')
            ->where('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('l')
            ->select('l.id, l.name')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->leftJoin('l.teams', 't')
            ->where('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
