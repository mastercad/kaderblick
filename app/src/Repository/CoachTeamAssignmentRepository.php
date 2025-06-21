<?php

namespace App\Repository;

use App\Entity\CoachTeamAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class CoachTeamAssignmentRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoachTeamAssignment::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('cta')
            ->select('cta', 'c', 't', 'ct')
            ->leftJoin('cta.coach', 'c')
            ->leftJoin('cta.team', 't')
            ->leftJoin('cta.coachTeamAssignmentType', 'ct')
            ->orderBy('cta.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('cta')
            ->select('cta.id, cta.startDate, cta.endDate')
            ->addSelect('c.id as coach_id, c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->addSelect('ct.id as type_id, ct.name as type_name')
            ->leftJoin('cta.coach', 'c')
            ->leftJoin('cta.team', 't')
            ->leftJoin('cta.coachTeamAssignmentType', 'ct')
            ->orderBy('cta.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('cta')
            ->select('cta', 'c', 't', 'ag')
            ->leftJoin('cta.coach', 'c')
            ->leftJoin('cta.team', 't')
            ->leftJoin('t.ageGroup', 'ag')
            ->where('cta.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('cta')
            ->select('cta.id, cta.startDate, cta.endDate')
            ->addSelect('c.id as coach_id, c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->addSelect('ag.name as age_group')
            ->leftJoin('cta.coach', 'c')
            ->leftJoin('cta.team', 't')
            ->leftJoin('t.ageGroup', 'ag')
            ->where('cta.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
