<?php

namespace App\Repository;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetToken>
 */
class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    public function findValidTokenByToken(string $token): ?PasswordResetToken
    {
        return $this->createQueryBuilder('prt')
            ->andWhere('prt.token = :token')
            ->andWhere('prt.expiresAt > :now')
            ->andWhere('prt.used = false')
            ->setParameter('token', $token)
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findValidTokenByUser(User $user): ?PasswordResetToken
    {
        return $this->createQueryBuilder('prt')
            ->andWhere('prt.user = :user')
            ->andWhere('prt.expiresAt > :now')
            ->andWhere('prt.used = false')
            ->setParameter('user', $user)
            ->setParameter('now', new DateTime())
            ->orderBy('prt.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function invalidateAllTokensForUser(User $user): void
    {
        $this->createQueryBuilder('prt')
            ->update()
            ->set('prt.used', 'true')
            ->where('prt.user = :user')
            ->andWhere('prt.used = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function deleteExpiredTokens(): int
    {
        return $this->createQueryBuilder('prt')
            ->delete()
            ->where('prt.expiresAt < :now')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->execute();
    }
}
