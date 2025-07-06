<?php

namespace App\Repository;

use App\Entity\Nationality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<Nationality>
 *
 * @implements OptimizedRepositoryInterface<Nationality>
 */
class NationalityRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nationality::class);
    }

    /**
     * @return Nationality[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('n')
            ->select('n', 'pna', 'cna', 'p', 'c')
            ->leftJoin('n.playerNationalityAssignments', 'pna')
            ->leftJoin('n.coachNationalityAssignments', 'cna')
            ->leftJoin('pna.player', 'p')
            ->leftJoin('cna.coach', 'c')
            ->orderBy('n.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Nationality[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('n')
            ->select('n.id, n.name, n.isoCode')
            ->addSelect('COUNT(DISTINCT pna.id) as player_count')
            ->addSelect('COUNT(DISTINCT cna.id) as coach_count')
            ->leftJoin('n.playerNationalityAssignments', 'pna')
            ->leftJoin('n.coachNationalityAssignments', 'cna')
            ->groupBy('n.id')
            ->orderBy('n.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('n')
            ->select('n', 'pna', 'cna', 'p', 'c')
            ->leftJoin('n.playerNationalityAssignments', 'pna')
            ->leftJoin('n.coachNationalityAssignments', 'cna')
            ->leftJoin('pna.player', 'p')
            ->leftJoin('cna.coach', 'c')
            ->where('n.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('n')
            ->select('n.id, n.name, n.isoCode')
            ->addSelect('pna.id as player_assignment_id, pna.active as player_active')
            ->addSelect('p.id as player_id, p.firstName as player_firstName, p.lastName as player_lastName')
            ->addSelect('cna.id as coach_assignment_id, cna.active as coach_active')
            ->addSelect('c.id as coach_id, c.firstName as coach_firstName, c.lastName as coach_lastName')
            ->leftJoin('n.playerNationalityAssignments', 'pna')
            ->leftJoin('n.coachNationalityAssignments', 'cna')
            ->leftJoin('pna.player', 'p')
            ->leftJoin('cna.coach', 'c')
            ->where('n.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
