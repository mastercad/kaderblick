<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserTitle;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserTitle>
 */
class UserTitleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTitle::class);
    }

    /**
     * Get active titles for a user, sorted by priority.
     *
     * @return UserTitle[]
     */
    public function findActiveByUser(User $user): array
    {
        $titles = $this->createQueryBuilder('ut')
            ->where('ut.user = :user')
            ->andWhere('ut.isActive = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        // Sort by priority (platform gold first, then team gold, etc.)
        usort($titles, fn (UserTitle $a, UserTitle $b) => $a->getPriority() <=> $b->getPriority());

        return $titles;
    }

    /**
     * Get the highest priority active title for a user.
     */
    public function findHighestPriorityTitle(User $user): ?UserTitle
    {
        $titles = $this->findActiveByUser($user);

        return $titles[0] ?? null;
    }

    /**
     * Deactivate all titles of a specific category and scope for a user.
     */
    public function deactivateTitles(User $user, string $titleCategory, string $titleScope, ?int $teamId = null): void
    {
        $qb = $this->createQueryBuilder('ut')
            ->update()
            ->set('ut.isActive', 'false')
            ->set('ut.revokedAt', ':now')
            ->where('ut.user = :user')
            ->andWhere('ut.titleCategory = :category')
            ->andWhere('ut.titleScope = :scope')
            ->andWhere('ut.isActive = true')
            ->setParameter('user', $user)
            ->setParameter('category', $titleCategory)
            ->setParameter('scope', $titleScope)
            ->setParameter('now', new DateTimeImmutable());

        if (null !== $teamId) {
            $qb->andWhere('ut.team = :teamId')
               ->setParameter('teamId', $teamId);
        } else {
            $qb->andWhere('ut.team IS NULL');
        }

        $qb->getQuery()->execute();
    }
}
