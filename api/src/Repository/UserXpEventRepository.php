<?php

namespace App\Repository;

use App\Entity\UserXpEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserXpEvent>
 */
class UserXpEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserXpEvent::class);
    }

    // Add custom methods here if needed
}
