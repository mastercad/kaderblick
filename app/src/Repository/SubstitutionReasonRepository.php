<?php

namespace App\Repository;

use App\Entity\SubstitutionReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class SubstitutionReasonRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubstitutionReason::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('sr')
            ->select('sr', 's', 'g', 't')
            ->leftJoin('sr.substitutions', 's')
            ->leftJoin('s.game', 'g')
            ->leftJoin('s.team', 't')
            ->where('sr.active = true')
            ->orderBy('sr.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('sr')
            ->select('sr.id, sr.name, sr.description')
            ->addSelect('COUNT(s.id) as substitutionCount')
            ->leftJoin('sr.substitutions', 's')
            ->where('sr.active = true')
            ->groupBy('sr.id')
            ->orderBy('sr.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('sr')
            ->select('sr', 's', 'g', 't')
            ->leftJoin('sr.substitutions', 's')
            ->leftJoin('s.game', 'g')
            ->leftJoin('s.team', 't')
            ->where('sr.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('sr')
            ->select('sr.id, sr.name, sr.code')
            ->addSelect('s.id as substitution_id, s.minute')
            ->addSelect('g.date as game_date')
            ->addSelect('t.name as team_name')
            ->leftJoin('sr.substitutions', 's')
            ->leftJoin('s.game', 'g')
            ->leftJoin('s.team', 't')
            ->where('sr.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
