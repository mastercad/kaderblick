<?php

namespace App\Repository;

use App\Entity\CoachLicense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<CoachLicense>
 *
 * @implements OptimizedRepositoryInterface<CoachLicense>
 */
class CoachLicenseRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoachLicense::class);
    }

    /**
     * @return CoachLicense[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('cl')
            ->select('cl', 'a')
            ->leftJoin('cl.assignments', 'a')
            ->orderBy('cl.level', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CoachLicense[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('cl')
            ->select('cl.id, cl.name, cl.level')
            ->addSelect('COUNT(a.id) as assignmentCount')
            ->leftJoin('cl.assignments', 'a')
            ->groupBy('cl.id')
            ->orderBy('cl.level', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('cl')
            ->select('cl', 'a', 'c')
            ->leftJoin('cl.assignments', 'a')
            ->leftJoin('a.coach', 'c')
            ->where('cl.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('cl')
            ->select('cl.id, cl.name, cl.level')
            ->addSelect('a.id as assignment_id, a.validFrom, a.validUntil')
            ->addSelect('c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->leftJoin('cl.assignments', 'a')
            ->leftJoin('a.coach', 'c')
            ->where('cl.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
