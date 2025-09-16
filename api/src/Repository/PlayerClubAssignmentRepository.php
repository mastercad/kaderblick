<?php

namespace App\Repository;

use App\Entity\PlayerClubAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<PlayerClubAssignment>
 *
 * @implements OptimizedRepositoryInterface<PlayerClubAssignment>
 */
class PlayerClubAssignmentRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerClubAssignment::class);
    }

    /**
     * @return PlayerClubAssignment[]
     */
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

    /**
     * @return PlayerClubAssignment[]
     */
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

    /**
     * @return array<string, mixed>|null
     */
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

    /**
     * @return array<string, mixed>|null
     */
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

    /**
     * @param array<int> $playerClubAssignmentIds
     */
    public function deleteByIds(array $playerClubAssignmentIds): void
    {
        if (0 === count($playerClubAssignmentIds)) {
            return;
        }

        $this->createQueryBuilder('pca')
            ->delete()
            ->where('pca.id IN (:ids)')
            ->setParameter('ids', $playerClubAssignmentIds)
            ->getQuery()
            ->execute();
    }
}
