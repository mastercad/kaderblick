<?php

namespace App\Repository;

use App\Entity\AgeGroup;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<AgeGroup>
 */
class AgeGroupRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgeGroup::class);
    }

    public function findAgeGroup(DateTimeInterface $birthDate, ?DateTimeInterface $referenceDate = null): ?AgeGroup
    {
        $age = $birthDate->diff($referenceDate)->y;

        return $this->createQueryBuilder('a')
            ->where('a.active = true')
            ->andWhere('a.minAge <= :age')
            ->andWhere('a.maxAge IS NULL OR a.maxAge >= :age')
            ->setParameter('age', $age)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return AgeGroup[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('ag')
            ->select('ag')
            ->orderBy('ag.minAge', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return AgeGroup[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('ag')
            ->select('ag.id, ag.name, ag.minAge, ag.maxAge')
            ->addSelect('COUNT(t.id) as teamCount')
            ->leftJoin('ag.teams', 't')
            ->groupBy('ag.id')
            ->orderBy('ag.minAge', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('ag')
            ->select('ag')
            ->where('ag.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('ag')
            ->select('ag.id, ag.name, ag.minAge, ag.maxAge')
            ->where('ag.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
