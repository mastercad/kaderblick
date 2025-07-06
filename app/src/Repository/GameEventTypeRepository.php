<?php

namespace App\Repository;

use App\Entity\GameEventType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<GameEventType>
 */
class GameEventTypeRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameEventType::class);
    }

    /**
     * @return GameEventType[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('get')
            ->select('get', 'ge')
            ->leftJoin('get.gameEvents', 'ge')
            ->orderBy('get.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return GameEventType[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('get')
            ->select('get.id, get.name, get.code, get.icon')
            ->addSelect('COUNT(ge.id) as eventCount')
            ->leftJoin('get.gameEvents', 'ge')
            ->groupBy('get.id')
            ->orderBy('get.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('get')
            ->select('get', 'ge', 'g', 'p', 't')
            ->leftJoin('get.gameEvents', 'ge')
            ->leftJoin('ge.game', 'g')
            ->leftJoin('ge.player', 'p')
            ->leftJoin('ge.team', 't')
            ->where('get.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('get')
            ->select('get.id, get.name, get.code, get.icon, get.active')
            ->leftJoin('get.gameEvents', 'ge')
            ->leftJoin('ge.game', 'g')
            ->leftJoin('ge.player', 'p')
            ->leftJoin('ge.team', 't')
            ->where('get.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
