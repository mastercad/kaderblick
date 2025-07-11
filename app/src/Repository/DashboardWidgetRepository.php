<?php

namespace App\Repository;

use App\Entity\DashboardWidget;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DashboardWidget>
 */
class DashboardWidgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DashboardWidget::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.user = :user')
            ->andWhere('w.enabled = :enabled')
            ->setParameter('user', $user)
            ->setParameter('enabled', true)
            ->orderBy('w.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(DashboardWidget $widget, bool $flush = false): void
    {
        $this->getEntityManager()->persist($widget);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DashboardWidget $widget, bool $flush = false): void
    {
        $this->getEntityManager()->remove($widget);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
