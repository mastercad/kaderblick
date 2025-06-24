<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PlayerRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(ManagerRegistry $registry, RoleHierarchyInterface $roleHierarchy)
    {
        parent::__construct($registry, Player::class);
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * @param User $user
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        $qb = $this->createQueryBuilder('p');

        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());
        if ($user && !in_array('ROLE_ADMIN', $reachableRoles)) {
            if ($user->getClub()) {
                $qb->join('p.playerClubAssignments', 'pca')
                   ->andWhere('pca.club = :club')
                   ->setParameter('club', $user->getClub());
            }
            
            if ($user->getCoach()) {
                $qb->join('p.playerTeamAssignments', 'pta')
                   ->join('pta.team', 't')
                   ->join('t.coachTeamAssignments', 'cta')
                   ->andWhere('cta.coach = :coach')
                   ->setParameter('coach', $user->getCoach());
            }
            
            if ($user->getPlayer()) {
                $qb->join('p.playerTeamAssignments', 'pta')
                   ->join('pta.team', 't')
                   ->andWhere('pta.player = :player')
                   ->setParameter('player', $user->getCoach());
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        $now = new DateTime();
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

    /**
     * @param User $user
     */
    public function findVisiblePlayers(?UserInterface $user = null): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            if ($user->getClub()) {
                $qb->join('p.playerClubAssignments', 'pca')
                   ->andWhere('pca.club = :club')
                   ->setParameter('club', $user->getClub());
            }
            
            if ($user->getCoach()) {
                $qb->join('p.playerTeamAssignments', 'pta')
                   ->join('pta.team', 't')
                   ->join('t.coachTeamAssignments', 'cta')
                   ->andWhere('cta.coach = :coach')
                   ->setParameter('coach', $user->getCoach());
            }
            
            if ($user->getPlayer()) {
                $qb->join('p.playerTeamAssignments', 'pta')
                   ->join('pta.team', 't')
                   ->andWhere('pta.player = :player')
                   ->setParameter('player', $user->getCoach());
            }
        }

        return $qb->getQuery()->getResult();
    }
}
