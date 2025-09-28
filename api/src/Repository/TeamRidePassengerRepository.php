<?php

namespace App\Repository;

use App\Entity\TeamRidePassenger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<TeamRidePassenger>
 */
class TeamRidePassengerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamRidePassenger::class);
    }
}
