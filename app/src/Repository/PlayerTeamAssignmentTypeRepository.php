<?php

namespace App\Repository;

use App\Entity\PlayerTeamAssignmentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<PlayerTeamAssignmentType>
 *
 * @implements OptimizedRepositoryInterface<PlayerTeamAssignmentType>
 */
class PlayerTeamAssignmentTypeRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerTeamAssignmentType::class);
    }

    /**
     * @return PlayerTeamAssignmentType[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('ptat')
            ->select('ptat', 'a')
            ->leftJoin('ptat.playerTeamAssignments', 'a')
            ->where('ptat.active = true')
            ->orderBy('ptat.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return PlayerTeamAssignmentType[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('ptat')
            ->select('ptat.id, ptat.name, ptat.description')
            ->addSelect('COUNT(a.id) as assignmentCount')
            ->leftJoin('ptat.playerTeamAssignments', 'a')
            ->where('ptat.active = true')
            ->groupBy('ptat.id')
            ->orderBy('ptat.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('ptat')
            ->select('ptat', 'a', 'p', 't')
            ->leftJoin('ptat.playerTeamAssignments', 'a')
            ->leftJoin('a.player', 'p')
            ->leftJoin('a.team', 't')
            ->where('ptat.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('ptat')
            ->select('ptat.id, ptat.name, ptat.description, ptat.active')
            ->addSelect('a.id as assignment_id, a.startDate, a.endDate')
            ->addSelect('p.id as player_id, p.firstName as player_firstName, p.lastName as player_lastName')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->leftJoin('ptat.playerTeamAssignments', 'a')
            ->leftJoin('a.player', 'p')
            ->leftJoin('a.team', 't')
            ->where('ptat.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
