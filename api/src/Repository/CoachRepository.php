<?php

namespace App\Repository;

use App\Entity\Coach;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<Coach>
 *
 * @implements OptimizedRepositoryInterface<Coach>
 */
class CoachRepository extends ServiceEntityRepository implements OptimizedRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coach::class);
    }

    /**
     * @return Coach[]
     */
    public function fetchFullList(?UserInterface $user = null): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'cta', 'cna', 'cca')
            ->leftJoin('c.coachTeamAssignments', 'cta', 'WITH', 'cta.coach = c')
            ->leftJoin('c.coachNationalityAssignments', 'cna', 'WITH', 'cna.coach = c')
            ->leftJoin('c.coachClubAssignments', 'cca', 'WITH', 'cca.coach = c')
            ->orderBy('c.lastName', 'ASC')
            ->addOrderBy('c.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Coach[]
     */
    public function fetchOptimizedList(?UserInterface $user = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c', 'cta', 'cna', 'cca')
            ->leftJoin('c.coachTeamAssignments', 'cta', 'WITH', 'cta.coach = c')
            ->leftJoin('c.coachNationalityAssignments', 'cna', 'WITH', 'cna.coach = c')
            ->leftJoin('c.coachClubAssignments', 'cca', 'WITH', 'cca.coach = c')
            ->orderBy('c.lastName', 'ASC')
            ->addOrderBy('c.firstName', 'ASC');

        if ($user && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $teamIds = [];
            if ($user instanceof User) {
                foreach ($user->getUserRelations() as $relation) {
                    if ($relation->getPlayer()) {
                        foreach ($relation->getPlayer()->getPlayerTeamAssignments() as $pta) {
                            $team = $pta->getTeam();
                            $teamIds[] = $team->getId();
                        }
                    }
                    if ($relation->getCoach()) {
                        foreach ($relation->getCoach()->getCoachTeamAssignments() as $cta) {
                            $team = $cta->getTeam();
                            $teamIds[] = $team->getId();
                        }
                    }
                }
            }
            $teamIds = array_unique($teamIds);
            if ($teamIds) {
                $qb->join('c.coachTeamAssignments', 'cta_filter')
                   ->andWhere('cta_filter.team IN (:teamIds)')
                   ->setParameter('teamIds', $teamIds);
            } else {
                // User hat keine relevante Relation, keine Coaches anzeigen
                $qb->andWhere('1 = 0');
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchFullEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'cta', 'cna', 'cca')
            ->leftJoin('c.coachTeamAssignments', 'cta', 'WITH', 'cta.coach = c')
            ->leftJoin('c.coachNationalityAssignments', 'cna', 'WITH', 'cna.coach = c')
            ->leftJoin('c.coachClubAssignments', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetchOptimizedEntry(int $id, ?UserInterface $user = null): ?array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'cta', 'cna', 'cca')
            ->leftJoin('c.coachTeamAssignments', 'cta', 'WITH', 'cta.coach = c')
            ->leftJoin('c.coachNationalityAssignments', 'cna', 'WITH', 'cna.coach = c')
            ->leftJoin('c.coachClubAssignments', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
