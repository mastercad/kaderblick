<?php

namespace App\Repository;

use App\Entity\CalendarEvent;
use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

class CalendarEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarEvent::class);
    }

    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin(Game::class, 'g', 'WITH', 'g.calendarEvent = e')
            ->where('e.startDate >= :now')
            ->setParameter('now', new DateTime())
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBetweenDates(DateTime $start, DateTime $end): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin(Game::class, 'g', 'WITH', 'g.calendarEvent = e')
            ->where('e.startDate BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
