<?php

namespace App\Repository;

use App\Entity\Coach;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class CoachRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coach::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'cta', 'cna', 'cca')
            ->leftJoin('c.coachTeamAssignments', 'cta', 'WITH', 'cta.coach = c')
            ->leftJoin('c.coachNationalityAssignments', 'cna', 'WITH', 'cna.coach = c')
            ->leftJoin('c.coachClubAssignments', 'cca', 'WITH', 'cca.coach = c')
            ->orderBy('c.lastName', 'ASC')
            ->addOrderBy('c.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'cta', 'cna', 'cca')
            ->leftJoin('c.coachTeamAssignments', 'cta', 'WITH', 'cta.coach = c')
            ->leftJoin('c.coachNationalityAssignments', 'cna', 'WITH', 'cna.coach = c')
            ->leftJoin('c.coachClubAssignments', 'c')
            ->orderBy('c.lastName', 'ASC')
            ->addOrderBy('c.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'cta', 'cna', 'cca')
            ->leftJoin('c.coachTeamAssignments', 'cta', 'WITH', 'cta.coach = c')
            ->leftJoin('c.coachNationalityAssignments', 'cna', 'WITH', 'cna.coach = c')
            ->leftJoin('c.coachClubAssignments', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'cta', 'cna', 'cca')
            ->leftJoin('c.coachTeamAssignments', 'cta', 'WITH', 'cta.coach = c')
            ->leftJoin('c.coachNationalityAssignments', 'cna', 'WITH', 'cna.coach = c')
            ->leftJoin('c.coachClubAssignments', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
