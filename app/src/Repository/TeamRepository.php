<?php

namespace App\Repository;

use App\Entity\Coach;
use App\Entity\CoachTeamAssignment;
use App\Entity\Player;
use App\Entity\PlayerTeamAssignment;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<Team>
 *
 * @implements OptimizedRepositoryInterface<Team>
 */
class TeamRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @param User $user
     *
     * @return Team[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t', 'ag', 'l', 'pta', 'p', 'cta', 'c', 'cl')
            ->leftJoin('t.ageGroup', 'ag')
            ->leftJoin('t.league', 'l')
            ->leftJoin('t.clubs', 'cl')
            ->leftJoin('t.playerTeamAssignments', 'pta', 'WITH', 'pta.team = t')
            ->leftJoin('t.coachTeamAssignments', 'cta', 'WITH', 'cta.team = t')
            ->leftJoin('cta.coach', 'c', 'WITH', 'cta.coach = c')
            ->leftJoin('pta.player', 'p', 'WITH', 'pta.player = p')
            ->orderBy('t.name', 'ASC');
/*
        if ($user && !in_array('ROLE_ADMIN', $user->getRoles())) {
            if ($user->getClub()) {
                $qb->andWhere('cl = :club')
                   ->setParameter('club', $user->getClub());
            }

            if ($user->getCoach()) {
                $qb->andWhere('c = :coach')
                   ->setParameter('coach', $user->getCoach());
            }

            if ($user->getPlayer()) {
                $qb->andWhere('p = :player')
                   ->setParameter('player', $user->getPlayer());
            }
        }
*/
        return $qb->getQuery()->getResult();
    }

    /**
     * @return Team[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.id, t.name')
            ->addSelect('ag.id as age_group_id, ag.name as age_group_name')
            ->addSelect('l.id as league_id, l.name as league_name')
            ->addSelect('COUNT(DISTINCT pta.id) as player_count')
            ->addSelect('COUNT(DISTINCT cta.id) as coach_count')
            ->leftJoin('t.ageGroup', 'ag')
            ->leftJoin('t.league', 'l')
            ->leftJoin('t.playerTeamAssignments', 'pta')
            ->leftJoin('t.coachTeamAssignments', 'cta')
            ->groupBy('t.id')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        // Für Admin-Detailansicht: Alle Details mit allen Relationen
        return $this->createQueryBuilder('t')
            ->select('t', 'p', 'g', 'l', 'pta', 'cta')
            ->leftJoin('t.players', 'p')
            ->leftJoin('t.games', 'g')
            ->leftJoin('t.league', 'l')
            ->leftJoin('t.playerTeamAssignments', 'pta', 'WITH', 'pta.team = t')
            ->leftJoin('t.coachTeamAssignments', 'cta', 'WITH', 'cta.team = t')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        // Für Frontend/API: Nur die benötigten Details
        return $this->createQueryBuilder('t')
            ->select('t.id, t.name')
            ->addSelect('p.id as player_id, p.firstName, p.lastName')
            ->leftJoin('t.players', 'p')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
