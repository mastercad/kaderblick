<?php

namespace App\Repository;

use App\Entity\PlayerTeamAssignment;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<PlayerTeamAssignment>
 *
 * @implements OptimizedRepositoryInterface<PlayerTeamAssignment>
 */
class PlayerTeamAssignmentRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerTeamAssignment::class);
    }

    /**
     * @return PlayerTeamAssignment[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        $now = new DateTime();

        return $this->createQueryBuilder('pta')
            ->select('pta', 'p', 't', 'ag')
            ->innerJoin('pta.player', 'p')
            ->innerJoin('pta.team', 't')
            ->leftJoin('t.ageGroup', 'ag')
//            ->andWhere('pta.endDate IS NULL OR pta.endDate >= :now')
//            ->setParameter('now', $now)
            ->orderBy('pta.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return PlayerTeamAssignment[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        $now = new DateTime();

        return $this->createQueryBuilder('pta')
            ->select('pta', 'p', 't', 'ag')
            ->innerJoin('pta.player', 'p')
            ->innerJoin('pta.team', 't')
            ->leftJoin('t.ageGroup', 'ag')
//            ->andWhere('pta.endDate IS NULL OR pta.endDate >= :now')
//            ->setParameter('now', $now)
            ->orderBy('pta.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('pta')
            ->select('pta', 'p', 't', 'ag')
            ->leftJoin('pta.player', 'p')
            ->leftJoin('pta.team', 't')
            ->leftJoin('t.ageGroup', 'ag')
            ->where('pta.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('pta')
            ->select('pta.id, pta.startDate, pta.endDate')
            ->addSelect('p.id as player_id, p.firstName as player_firstName, p.lastName as player_lastName')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->addSelect('ag.name as age_group')
            ->leftJoin('pta.player', 'p')
            ->leftJoin('pta.team', 't')
            ->leftJoin('t.ageGroup', 'ag')
            ->where('pta.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
