<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function findNewMessages(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.recipients', 'r')
            ->where('r.id = :userId')
            ->andWhere('m.readAt IS NULL')
            ->setParameter('userId', $user->getId())
            ->orderBy('m.sentAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>
     */
    public function findLatestForUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.recipients', 'r')
            ->where('r.id = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('m.sentAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
