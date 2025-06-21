<?php

namespace App\Repository;

use App\Entity\SurfaceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class SurfaceTypeRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurfaceType::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('s')
            ->select('s', 'l')
            ->leftJoin('s.locations', 'l')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.id, s.name')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('s')
            ->select('s', 'l')
            ->leftJoin('s.locations', 'l')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('s')
            ->select('s.id, s.name')
            ->addSelect('COUNT(l.id) as locationCount')
            ->leftJoin('s.locations', 'l')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->groupBy('s.id')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
