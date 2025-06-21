<?php

namespace App\Repository;

use App\Entity\Substitution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class SubstitutionRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Substitution::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('s')
            ->select('s', 'g', 't', 'pi', 'po', 'sr')
            ->leftJoin('s.game', 'g')
            ->leftJoin('s.team', 't')
            ->leftJoin('s.playerIn', 'pi')
            ->leftJoin('s.playerOut', 'po')
            ->leftJoin('s.substitutionReason', 'sr')
            ->orderBy('g.date', 'DESC')
            ->addOrderBy('s.minute', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.id, s.minute')
            ->addSelect('g.id as game_id, g.date as game_date')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->addSelect('pi.id as player_in_id, pi.firstName as player_in_firstName, pi.lastName as player_in_lastName')
            ->addSelect('po.id as player_out_id, po.firstName as player_out_firstName, po.lastName as player_out_lastName')
            ->addSelect('sr.id as reason_id, sr.name as reason_name')
            ->leftJoin('s.game', 'g')
            ->leftJoin('s.team', 't')
            ->leftJoin('s.playerIn', 'pi')
            ->leftJoin('s.playerOut', 'po')
            ->leftJoin('s.substitutionReason', 'sr')
            ->orderBy('g.date', 'DESC')
            ->addOrderBy('s.minute', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('s')
            ->select('s', 'g', 't', 'pi', 'po', 'sr')
            ->leftJoin('s.game', 'g')
            ->leftJoin('s.team', 't')
            ->leftJoin('s.playerIn', 'pi')
            ->leftJoin('s.playerOut', 'po')
            ->leftJoin('s.substitutionReason', 'sr')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('s')
            ->select('s.id, s.minute')
            ->addSelect('g.id as game_id, g.date as game_date')
            ->addSelect('t.id as team_id, t.name as team_name')
            ->addSelect('pi.id as player_in_id, pi.firstName as player_in_firstName, pi.lastName as player_in_lastName')
            ->addSelect('po.id as player_out_id, po.firstName as player_out_firstName, po.lastName as player_outLastName')
            ->addSelect('sr.id as reason_id, sr.name as reason_name')
            ->leftJoin('s.game', 'g')
            ->leftJoin('s.team', 't')
            ->leftJoin('s.playerIn', 'pi')
            ->leftJoin('s.playerOut', 'po')
            ->leftJoin('s.substitutionReason', 'sr')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
