<?php

namespace App\Repository;

use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\User;
use App\Entity\UserRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserRelation>
 */
class UserRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRelation::class);
    }

    /**
     * Findet alle Benutzer, die Zugriff auf einen bestimmten Spieler haben.
     *
     * @return array<string, mixed>
     */
    public function findAllWithAccessToPlayer(Player $player): array
    {
        return $this->createQueryBuilder('r')
            ->select('r', 'u')
            ->join('r.user', 'u')
            ->where('r.player = :player')
            ->setParameter('player', $player)
            ->getQuery()
            ->getResult();
    }

    /**
     * Findet alle Benutzer, die Zugriff auf einen bestimmten Coach haben.
     *
     * @return array<string, mixed>
     */
    public function findAllWithAccessToCoach(Coach $coach): array
    {
        return $this->createQueryBuilder('r')
            ->select('r', 'u')
            ->join('r.user', 'u')
            ->where('r.coach = :coach')
            ->setParameter('coach', $coach)
            ->getQuery()
            ->getResult();
    }

    /**
     * Prüft ob ein Benutzer Zugriff auf einen Spieler hat.
     */
    public function hasAccessToPlayer(User $user, Player $player): bool
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.user = :user')
            ->andWhere('r.player = :player')
            ->setParameter('user', $user)
            ->setParameter('player', $player)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
