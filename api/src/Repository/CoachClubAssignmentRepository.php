<?php

namespace App\Repository;

use App\Entity\CoachClubAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<CoachClubAssignment>
 *
 * @implements OptimizedRepositoryInterface<CoachClubAssignment>
 */
class CoachClubAssignmentRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoachClubAssignment::class);
    }

    /**
     * @return CoachClubAssignment[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('cca')
            ->select('cca', 'c', 'cl')
            ->leftJoin('cca.coach', 'c')
            ->leftJoin('cca.club', 'cl')
            ->orderBy('cca.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CoachClubAssignment[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('cca')
            ->select('cca.id, cca.startDate, cca.endDate')
            ->addSelect('c.id as coach_id, c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->addSelect('cl.id as club_id, cl.name as club_name')
            ->leftJoin('cca.coach', 'c')
            ->leftJoin('cca.club', 'cl')
            ->orderBy('cca.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('cca')
            ->select('cca', 'c', 'cl')
            ->leftJoin('cca.coach', 'c')
            ->leftJoin('cca.club', 'cl')
            ->where('cca.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('cca')
            ->select('cca.id, cca.startDate, cca.endDate')
            ->addSelect('c.id as coach_id, c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->addSelect('cl.id as club_id, cl.name as club_name')
            ->leftJoin('cca.coach', 'c')
            ->leftJoin('cca.club', 'cl')
            ->where('cca.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array<int> $coachClubAssignmentIds
     */
    public function deleteByIds(array $coachClubAssignmentIds): void
    {
        if (0 === count($coachClubAssignmentIds)) {
            return;
        }

        $this->createQueryBuilder('cca')
            ->delete()
            ->where('cca.id IN (:ids)')
            ->setParameter('ids', $coachClubAssignmentIds)
            ->getQuery()
            ->execute();
    }
}
