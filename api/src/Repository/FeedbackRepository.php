<?php

namespace App\Repository;

use App\Entity\Feedback;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Feedback>
 */
class FeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }

    /**
     * @return Feedback[] Returns an array of unresolved Feedback objects
     */
    public function findUnresolved(): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.resolved = :resolved')
            ->andWhere('f.isRead = :isRead')
            ->setParameter('resolved', false)
            ->setParameter('isRead', false)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Feedback[] Returns an array of Feedback objects for a specific user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, int> Returns feedback counts by type
     */
    public function fetchTypeStatistics(): array
    {
        $results = $this->createQueryBuilder('f')
            ->select('f.type, COUNT(f.id) as count')
            ->groupBy('f.type')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($results as $row) {
            $stats[$row['type']] = (int) $row['count'];
        }

        return $stats;
    }

    /**
     * @return array<string, mixed> Returns feedback statistics for a given time period
     */
    public function fetchStatisticsForPeriod(DateTime $start, DateTime $end): array
    {
        return $this->createQueryBuilder('f')
            ->select('f.type, COUNT(f.id) as count, f.resolved')
            ->where('f.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('f.type, f.resolved')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Feedback[] Returns recent feedback with similar messages
     */
    public function findSimilar(string $message, int $limit = 5): array
    {
        // Einfache Ähnlichkeitssuche basierend auf Wortübereinstimmungen
        $words = explode(' ', trim($message));
        $qb = $this->createQueryBuilder('f');

        foreach ($words as $i => $word) {
            if (strlen($word) > 3) { // Ignoriere zu kurze Wörter
                $qb->orWhere('f.message LIKE :word' . $i)
                   ->setParameter('word' . $i, '%' . $word . '%');
            }
        }

        return $qb->orderBy('f.createdAt', 'DESC')
                 ->setMaxResults($limit)
                 ->getQuery()
                 ->getResult();
    }

    /**
     * @return Feedback[] Returns an array of read but unresolved Feedback objects
     */
    public function findByIsReadAndUnresolved(): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.isRead = :isRead')
            ->andWhere('f.resolved = :resolved')
            ->setParameter('isRead', true)
            ->setParameter('resolved', false)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countUnresolved(): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.resolved = :resolved')
            ->setParameter('resolved', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(Feedback $feedback, bool $flush = false): void
    {
        $this->getEntityManager()->persist($feedback);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Feedback $feedback, bool $flush = false): void
    {
        $this->getEntityManager()->remove($feedback);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Feedback[] Returns an array of resolved Feedback objects
     */
    public function findByResolved(): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.resolved = :resolved')
            ->setParameter('resolved', true)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
