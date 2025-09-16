<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
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
     * @return array<int, Team>
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t.id, t.name')
            ->addSelect('ag.id as age_group_id, ag.name as age_group_name')
            ->addSelect('l.id as league_id, l.name as league_name')
            ->addSelect('COUNT(DISTINCT pta.id) as player_count')
            ->addSelect('COUNT(DISTINCT cta.id) as coach_count')
            ->addSelect("GROUP_CONCAT(DISTINCT CONCAT(c.firstName, ' ', c.lastName) SEPARATOR ', ') AS coach_names")
            ->addSelect("GROUP_CONCAT(DISTINCT cl.name SEPARATOR ', ') AS club_names")
            ->leftJoin('t.ageGroup', 'ag')
            ->leftJoin('t.league', 'l')
            ->leftJoin('t.playerTeamAssignments', 'pta')
            ->leftJoin('t.coachTeamAssignments', 'cta')
            ->leftJoin('cta.coach', 'c')
            ->leftJoin('t.clubs', 'cl')
            ->groupBy('t.id, ag.id, l.id')
            ->orderBy('t.name', 'ASC');

        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && !in_array('ROLE_SUPERADMIN', $user->getRoles(), true)) {
            $playerIds = [];
            $coachIds = [];
            if ($user instanceof User) {
                foreach ($user->getUserRelations() as $relation) {
                    if ($relation->getPlayer()) {
                        $playerIds[] = $relation->getPlayer()->getId();
                    }
                    if ($relation->getCoach()) {
                        $coachIds[] = $relation->getCoach()->getId();
                    }
                }
            }
            if ($playerIds && $coachIds) {
                $qb->andWhere('pta.player IN (:playerIds) OR cta.coach IN (:coachIds)')
                   ->setParameter('playerIds', $playerIds)
                   ->setParameter('coachIds', $coachIds);
            } elseif ($playerIds) {
                $qb->andWhere('pta.player IN (:playerIds)')
                   ->setParameter('playerIds', $playerIds);
            } elseif ($coachIds) {
                $qb->andWhere('cta.coach IN (:coachIds)')
                   ->setParameter('coachIds', $coachIds);
            } else {
                // User hat keine Spieler- oder Trainerrelation, keine Teams anzeigen
                $qb->andWhere('1 = 0');
            }
        }

        return $qb->getQuery()->getResult();
        /*
        // Transformiere die Daten in ein benutzerfreundlicheres Format
        $teams = [];
        foreach ($result as $row) {
            $teams[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'ageGroup' => $row['age_group_id'] ? [
                    'id' => $row['age_group_id'],
                    'name' => $row['age_group_name']
                ] : null,
                'league' => $row['league_id'] ? [
                    'id' => $row['league_id'],
                    'name' => $row['league_name']
                ] : null,
                'playerCount' => (int) $row['player_count'],
                'coachCount' => (int) $row['coach_count'],
                'coachNames' => $row['coach_names'] ? explode(', ', $row['coach_names']) : [],
                'clubNames' => $row['club_names'] ? explode(', ', $row['club_names']) : []
            ];
        }

        return $teams;
        */
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
