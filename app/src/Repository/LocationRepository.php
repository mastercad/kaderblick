<?php

namespace App\Repository;

use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class LocationRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('l')
            ->select('l', 'st')
            ->leftJoin('l.surfaceType', 'st')
            ->orderBy('l.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('l')
            ->select('l.id, l.name, l.address, l.city, l.capacity, l.hasFloodlight, l.facilities, l.latitude, l.longitude')
            ->addSelect('st.id as surface_type_id, st.name as surface_type_name')
            ->leftJoin('l.surfaceType', 'st')
            ->orderBy('l.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('l')
            ->select('l', 'st')
            ->leftJoin('l.surfaceType', 'st')
            ->where('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('l')
            ->select('l.id, l.name, l.address, l.city, l.capacity, l.hasFloodlight, l.facilities, l.latitude, l.longitude')
            ->addSelect('st.id as surface_type_id, st.name as surface_type_name')
            ->leftJoin('l.surfaceType', 'st')
            ->where('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
