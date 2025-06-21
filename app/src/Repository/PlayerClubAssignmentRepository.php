<?php

namespace App\Repository;

use App\Entity\PlayerClubAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class PlayerClubAssignmentRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerClubAssignment::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('pca')
            ->select('pca', 'p', 'c')
            ->leftJoin('pca.player', 'p')
            ->leftJoin('pca.club', 'c')
            ->leftJoin('p.playerTeamAssignments', 'pta')
            ->leftJoin('pta.team', 't')
            ->orderBy('pca.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('pca')
            ->select('pca.id, pca.startDate, pca.endDate, pca.active')
            ->addSelect('p.id as player_id, p.firstName as player_firstName, p.lastName as player_lastName')
            ->addSelect('c.id as club_id, c.name as club_name')
            ->leftJoin('pca.player', 'p')
            ->leftJoin('pca.club', 'c')
            ->orderBy('pca.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('pca')
            ->select('pca', 'p', 'c')
            ->leftJoin('pca.player', 'p')
            ->leftJoin('pca.club', 'c')
            ->where('pca.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('pca')
            ->select('pca.id, pca.startDate, pca.endDate, pca.membershipNumber')
            ->addSelect('p.id as player_id, p.firstName as player_firstName, p.lastName as player_lastName')
            ->addSelect('c.id as club_id, c.name as club_name')
            ->leftJoin('pca.player', 'p')
            ->leftJoin('pca.club', 'c')
            ->where('pca.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
