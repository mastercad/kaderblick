<?php

namespace App\Repository;

use App\Entity\StrongFoot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class StrongFootRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StrongFoot::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('sf')
            ->select('sf', 'p')
            ->leftJoin('sf.players', 'p')
            ->orderBy('sf.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('sf')
            ->select('sf.id, sf.name, sf.code')
            ->addSelect('COUNT(p.id) as playerCount')
            ->leftJoin('sf.players', 'p')
            ->groupBy('sf.id')
            ->orderBy('sf.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('sf')
            ->select('sf', 'p')
            ->leftJoin('sf.players', 'p')
            ->where('sf.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('sf')
            ->select('sf.id, sf.name')
            ->addSelect('p.id as player_id, p.firstName as player_firstName, p.lastName as player_lastName')
            ->leftJoin('sf.players', 'p')
            ->where('sf.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
