<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->where('u.isEnabled = true')
            ->orderBy('u.lastName', 'ASC')
            ->addOrderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.id, u.email, u.firstName, u.lastName, u.roles')
            ->where('u.isEnabled = true')
            ->orderBy('u.lastName', 'ASC')
            ->addOrderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('u')
            ->select('u.id, u.email, u.firstName, u.lastName, u.roles, u.isVerified, u.isEnabled')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUserByValidationToken(string $token): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.verificationToken = :token')
            ->andWhere('u.verificationExpires > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
