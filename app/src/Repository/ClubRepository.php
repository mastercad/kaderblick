<?php

namespace App\Repository;

use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class ClubRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'pca', 'cca')
            ->leftJoin('c.playerClubAssignments', 'pca', 'WITH', 'pca.club = c')
            ->leftJoin('c.coachClubAssignments', 'cca', 'WITH', 'cca.club = c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('c')
        ->select('c', 'pca', 'cca')
            ->leftJoin('c.playerClubAssignments', 'pca', 'WITH', 'pca.club = c')
            ->leftJoin('c.coachClubAssignments', 'cca', 'WITH', 'cca.club = c')
            ->groupBy('c.id')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('c')
        ->select('c', 'pca', 'cca')
            ->leftJoin('c.playerClubAssignments', 'pca', 'WITH', 'pca.club = c')
            ->leftJoin('c.coachClubAssignments', 'cca', 'WITH', 'cca.club = c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('c')
        ->select('c', 'pca', 'cca')
        ->leftJoin('c.playerClubAssignments', 'pca', 'WITH', 'pca.club = c')
        ->leftJoin('c.coachClubAssignments', 'cca', 'WITH', 'cca.club = c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
