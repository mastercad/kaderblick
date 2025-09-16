<?php

namespace App\Repository;

use App\Entity\CalendarEvent;
use App\Entity\Participation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participation>
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    /**
     * @return Participation[]
     */
    public function findByEvent(CalendarEvent $event): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.status', 's')
            ->where('p.event = :event')
            ->setParameter('event', $event)
            ->orderBy('s.sortOrder', 'ASC')
            ->addOrderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Participation[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.event', 'e')
            ->leftJoin('p.status', 's')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndEvent(User $user, CalendarEvent $event): ?Participation
    {
        return $this->findOneBy(['user' => $user, 'event' => $event]);
    }

    /**
     * Get participation count by status for an event.
     *
     * @return array<string, int>
     */
    public function getParticipationCountsByStatus(CalendarEvent $event): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('s.name as status_name, COUNT(p.id) as count')
            ->leftJoin('p.status', 's')
            ->where('p.event = :event')
            ->setParameter('event', $event)
            ->groupBy('s.id')
            ->orderBy('s.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['status_name']] = (int) $row['count'];
        }

        return $counts;
    }
}
