<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class PlayerRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'pta', 'pna')
            ->leftJoin('p.playerTeamAssignments', 'pta', 'WITH', 'pta.player = p')
            ->leftJoin('p.playerNationalityAssignments', 'pna', 'WITH', 'pna.player = p')
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        $now = new \DateTime();
        return $this->createQueryBuilder('p')
            ->select('p', 'pta', 'pna')
            ->leftJoin('p.playerTeamAssignments', 'pta', 'WITH', 'pta.player = p')
            ->leftJoin('p.playerNationalityAssignments', 'pna', 'WITH', 'pna.player = p')
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'pta', 'pna')
            ->leftJoin('p.playerTeamAssignments', 'pta', 'WITH', 'pta.player = p')
            ->leftJoin('p.playerNationalityAssignments', 'pna', 'WITH', 'pna.player = p')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'pta', 'pna')
            ->leftJoin('p.playerTeamAssignments', 'pta', 'WITH', 'pta.player = p')
            ->leftJoin('p.playerNationalityAssignments', 'pna', 'WITH', 'pna.player = p')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
