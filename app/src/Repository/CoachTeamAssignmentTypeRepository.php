<?php

namespace App\Repository;

use App\Entity\CoachTeamAssignmentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class CoachTeamAssignmentTypeRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoachTeamAssignmentType::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('ctat')
            ->select('ctat', 'a')
            ->leftJoin('ctat.coachTeamAssignments', 'a')
            ->where('ctat.active = true')
            ->orderBy('ctat.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('ctat')
            ->select('ctat.id, ctat.name, ctat.description')
            ->addSelect('COUNT(a.id) as assignmentCount')
            ->leftJoin('ctat.coachTeamAssignments', 'a')
            ->where('ctat.active = true')
            ->groupBy('ctat.id')
            ->orderBy('ctat.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('ctat')
            ->select('ctat', 'a', 'c', 't')
            ->leftJoin('ctat.coachTeamAssignments', 'a')
            ->leftJoin('a.coach', 'c')
            ->leftJoin('a.team', 't')
            ->where('ctat.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('ctat')
            ->select('ctat.id, ctat.name, ctat.description, ctat.active')
            ->addSelect('a.id as assignment_id, a.startDate, a.endDate')
            ->addSelect('c.id as coach_id, c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->leftJoin('ctat.coachTeamAssignments', 'a')
            ->leftJoin('a.coach', 'c')
            ->leftJoin('a.team', 't')
            ->where('ctat.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
