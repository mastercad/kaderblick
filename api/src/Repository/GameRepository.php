<?php

namespace App\Repository;

use App\Entity\Coach;
use App\Entity\CoachTeamAssignment;
use App\Entity\Game;
use App\Entity\Player;
use App\Entity\PlayerTeamAssignment;
use App\Entity\UserRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<Game>
 *
 * @implements OptimizedRepositoryInterface<Game>
 */
class GameRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
     * @return Game[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('g')
            ->select('g', 'ht', 'at', 'l', 'ge')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->leftJoin('g.location', 'l')
            ->leftJoin('g.gameEvents', 'ge')
            ->orderBy('ge.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Game[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        $availableTeams = $this->createQueryBuilder('t')
            ->select('t')
            ->from(UserRelation::class, 'ur')
            ->leftJoin(Player::class, 'p', 'WITH', 'ur.player = p')
            ->leftJoin(Coach::class, 'c', 'WITH', 'ur.coach = c')
            ->leftJoin(PlayerTeamAssignment::class, 'pta', 'WITH', 'pta.player = p AND pta.start_date <= NOW() AND (pta.end_date >= NOW() OR pta.end_date IS NULL)')
            ->leftJoin(CoachTeamAssignment::class, 'cta', 'WITH', 'cta.coach = c AND cta.start_date <= NOW() AND (cta.end_date >= NOW() OR cta.end_date IS NULL)')
            ->where('ur.user = :user AND cta.id IS NOT NULL AND pta.id IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        return $this->createQueryBuilder('g')
            ->select('g.id, ge.timestamp, g.homeScore, g.awayScore, g.status')
            ->addSelect('ht.id as homeTeam_id, ht.name as homeTeam_name')
            ->addSelect('at.id as awayTeam_id, at.name as awayTeam_name')
            ->addSelect('l.name as location_name')
            ->addSelect('COUNT(ge.id) as eventCount')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->leftJoin('g.location', 'l')
            ->leftJoin('g.gameEvents', 'ge')
            ->where('t IS IN (:availableTeams)')
            ->setParameter('availableTeams', $availableTeams)
            ->groupBy('g.id, ht.id, at.id, l.name')
            ->orderBy('ge.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('g')
            ->select('g', 'ht', 'at', 'l', 'ge', 'p', 't')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->leftJoin('g.location', 'l')
            ->leftJoin('g.gameEvents', 'ge')
            ->leftJoin('ge.player', 'p')
            ->leftJoin('ge.team', 't')
            ->where('g.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('g')
            ->select('g.id, ge.timestamp, g.homeScore, g.awayScore, g.status, g.report')
            ->addSelect('ht.id as homeTeam_id, ht.name as homeTeam_name')
            ->addSelect('at.id as awayTeam_id, at.name as awayTeam_name')
            ->addSelect('l.id as location_id, l.name as location_name')
            ->addSelect('ge.id as event_id, ge.minute, ge.eventType')
            ->addSelect('p.firstName as player_firstName, p.lastName as player_lastName')
            ->addSelect('t.name as event_team')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->leftJoin('g.location', 'l')
            ->leftJoin('g.gameEvents', 'ge')
            ->leftJoin('ge.player', 'p')
            ->leftJoin('ge.team', 't')
            ->where('g.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
