<?php

namespace App\Repository;

use App\Entity\CoachNationalityAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class CoachNationalityAssignmentRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoachNationalityAssignment::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('cna')
            ->select('cna', 'c', 'n')
            ->leftJoin('cna.coach', 'c')
            ->leftJoin('cna.nationality', 'n')
            ->orderBy('c.lastName', 'ASC')
            ->addOrderBy('c.firstName', 'ASC')
            ->addOrderBy('cna.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('cna')
            ->select('cna.id, cna.startDate, cna.endDate, cna.active')
            ->addSelect('c.id as coach_id, c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->addSelect('n.id as nationality_id, n.name as nationality_name, n.isoCode')
            ->leftJoin('cna.coach', 'c')
            ->leftJoin('cna.nationality', 'n')
            ->orderBy('c.lastName', 'ASC')
            ->addOrderBy('c.firstName', 'ASC')
            ->addOrderBy('cna.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('cna')
            ->select('cna', 'c', 'n')
            ->leftJoin('cna.coach', 'c')
            ->leftJoin('cna.nationality', 'n')
            ->where('cna.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('cna')
            ->select('cna.id, cna.startDate, cna.endDate, cna.active')
            ->addSelect('c.id as coach_id, c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->addSelect('n.id as nationality_id, n.name as nationality_name, n.isoCode')
            ->leftJoin('cna.coach', 'c')
            ->leftJoin('cna.nationality', 'n')
            ->where('cna.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
