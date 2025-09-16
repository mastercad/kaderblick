<?php

namespace App\Repository;

use App\Entity\ParticipationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParticipationStatus>
 */
class ParticipationStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParticipationStatus::class);
    }

    /**
     * @return ParticipationStatus[]
     */
    public function findActiveOrderedBySort(): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.isActive = true')
            ->orderBy('ps.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?ParticipationStatus
    {
        return $this->findOneBy(['code' => $code, 'isActive' => true]);
    }
}
