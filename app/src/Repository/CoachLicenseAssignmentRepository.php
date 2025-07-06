<?php

namespace App\Repository;

use App\Entity\CoachLicenseAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<CoachLicenseAssignment>
 */
class CoachLicenseAssignmentRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoachLicenseAssignment::class);
    }

    /**
     * @return CoachLicenseAssignment[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('cla')
            ->select('cla', 'c', 'l')
            ->leftJoin('cla.coach', 'c')
            ->leftJoin('cla.license', 'l')
            ->orderBy('cla.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CoachLicenseAssignment[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('cla')
            ->select('cla.id, cla.startDate, cla.endDate')
            ->addSelect('c.id as coach_id, c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->addSelect('l.id as license_id, l.name as license_name, l.countryCode')
            ->leftJoin('cla.coach', 'c')
            ->leftJoin('cla.license', 'l')
            ->orderBy('cla.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('cla')
            ->select('cla', 'c', 'l')
            ->leftJoin('cla.coach', 'c')
            ->leftJoin('cla.license', 'l')
            ->where('cla.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('cla')
            ->select('cla.id, cla.startDate, cla.endDate')
            ->addSelect('c.id as coach_id, c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->addSelect('l.id as license_id, l.name as license_name, l.countryCode')
            ->leftJoin('cla.coach', 'c')
            ->leftJoin('cla.license', 'l')
            ->where('cla.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
