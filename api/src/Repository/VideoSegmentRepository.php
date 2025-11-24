<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Video;
use App\Entity\VideoSegment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VideoSegment>
 */
class VideoSegmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoSegment::class);
    }

    /**
     * Findet alle Video-Segmente eines Benutzers für ein bestimmtes Spiel.
     *
     * @return VideoSegment[]
     */
    public function findByUserAndGame(User $user, int $gameId): array
    {
        return $this->createQueryBuilder('vs')
            ->innerJoin('vs.video', 'v')
            ->andWhere('vs.user = :user')
            ->andWhere('v.game = :gameId')
            ->setParameter('user', $user)
            ->setParameter('gameId', $gameId)
            ->orderBy('v.name', 'ASC')
            ->addOrderBy('vs.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Findet alle Video-Segmente eines Benutzers für ein bestimmtes Video.
     *
     * @return VideoSegment[]
     */
    public function findByUserAndVideo(User $user, Video $video): array
    {
        return $this->createQueryBuilder('vs')
            ->andWhere('vs.user = :user')
            ->andWhere('vs.video = :video')
            ->setParameter('user', $user)
            ->setParameter('video', $video)
            ->orderBy('vs.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Löscht alle Segmente eines Benutzers für ein bestimmtes Video.
     */
    public function deleteByUserAndVideo(User $user, Video $video): int
    {
        return $this->createQueryBuilder('vs')
            ->delete()
            ->andWhere('vs.user = :user')
            ->andWhere('vs.video = :video')
            ->setParameter('user', $user)
            ->setParameter('video', $video)
            ->getQuery()
            ->execute();
    }
}
