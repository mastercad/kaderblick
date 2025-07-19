<?php

namespace App\Repository;

use App\Entity\Coach;
use App\Entity\CoachTeamAssignment;
use App\Entity\Player;
use App\Entity\PlayerTeamAssignment;
use App\Entity\User;
use App\Entity\UserRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<UserRelation>
 */
class UserRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRelation::class);
    }

    public function findRelevantTeams(UserInterface $user)
    {
        return $this->createQueryBuilder('ur')
            ->select(['IDENTITY(pta.team) as playerTeamId', 'IDENTITY(cta.team) as coachTeamId'])
            ->leftJoin(Player::class, 'p', 'WITH', 'ur.player = p')
            ->leftJoin(Coach::class, 'c', 'WITH', 'ur.coach = c')
            ->leftJoin(PlayerTeamAssignment::class, 'pta', 'WITH', 'pta.player = p AND pta.startDate <= CURRENT_TIMESTAMP() AND (pta.endDate >= CURRENT_TIMESTAMP() OR pta.endDate IS NULL)')
            ->leftJoin(CoachTeamAssignment::class, 'cta', 'WITH', 'cta.coach = c AND cta.startDate <= CURRENT_TIMESTAMP() AND (cta.endDate >= CURRENT_TIMESTAMP() OR cta.endDate IS NULL)')
            ->where('ur.user = :user AND cta.id IS NOT NULL OR pta.id IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();
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
