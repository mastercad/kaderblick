<?php

namespace App\Repository;

use App\Entity\Game;
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
            ->orderBy('g.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Game[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('g')
            ->select('g.id, g.date, g.homeScore, g.awayScore, g.status')
            ->addSelect('ht.id as homeTeam_id, ht.name as homeTeam_name')
            ->addSelect('at.id as awayTeam_id, at.name as awayTeam_name')
            ->addSelect('l.name as location_name')
            ->addSelect('COUNT(ge.id) as eventCount')
            ->leftJoin('g.homeTeam', 'ht')
            ->leftJoin('g.awayTeam', 'at')
            ->leftJoin('g.location', 'l')
            ->leftJoin('g.gameEvents', 'ge')
            ->groupBy('g.id, ht.id, at.id, l.name')
            ->orderBy('g.date', 'DESC')
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
            ->select('g.id, g.date, g.homeScore, g.awayScore, g.status, g.report')
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
