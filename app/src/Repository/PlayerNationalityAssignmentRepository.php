<?php

namespace App\Repository;

use App\Entity\PlayerNationalityAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class PlayerNationalityAssignmentRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerNationalityAssignment::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('pna')
            ->select('pna', 'p', 'n', 'pt', 't')
            ->leftJoin('pna.player', 'p')
            ->leftJoin('pna.nationality', 'n')
            ->leftJoin('p.playerTeamAssignments', 'pt')
            ->leftJoin('pt.team', 't')
            ->orderBy('pna.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('pna')
            ->select('pna.id, pna.startDate, pna.endDate, pna.active')
            ->addSelect('p.id as player_id, p.firstName as player_firstName, p.lastName as player_lastName')
            ->addSelect('n.id as nationality_id, n.name as nationality_name, n.isoCode')
            ->leftJoin('pna.player', 'p')
            ->leftJoin('pna.nationality', 'n')
            ->orderBy('pna.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('pna')
            ->select('pna', 'p', 'n')
            ->leftJoin('pna.player', 'p')
            ->leftJoin('pna.nationality', 'n')
            ->where('pna.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('pna')
            ->select('pna.id, pna.primary')
            ->addSelect('p.id as player_id, p.firstName as player_firstName, p.lastName as player_lastName')
            ->addSelect('n.id as nationality_id, n.name as nationality_name, n.code as nationality_code')
            ->leftJoin('pna.player', 'p')
            ->leftJoin('pna.nationality', 'n')
            ->where('pna.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
