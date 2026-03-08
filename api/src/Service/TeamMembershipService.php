<?php

namespace App\Service;

use App\Entity\CalendarEvent;
use App\Entity\Club;
use App\Entity\CoachClubAssignment;
use App\Entity\CoachTeamAssignment;
use App\Entity\PlayerClubAssignment;
use App\Entity\PlayerTeamAssignment;
use App\Entity\Team;
use App\Entity\User;
use App\Enum\CalendarEventPermissionType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Checks whether a user belongs to any team or club associated with a CalendarEvent.
 * Used by TeamRideVoter, CalendarController, and ParticipationController.
 */
class TeamMembershipService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Returns true when $user belongs to any team that is linked to $event
     * (via TEAM-type CalendarEventPermissions or via a game's home/away teams).
     *
     * ROLE_SUPERADMIN is NOT checked here — callers must handle that separately.
     */
    public function isUserTeamMemberForEvent(User $user, CalendarEvent $event): bool
    {
        foreach ($this->getEventTeams($event) as $team) {
            if ($this->isUserInTeam($user, $team)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true when the user is allowed to participate in (and see the participation
     * section of) the given event, based on its visibility permissions.
     *
     * Rules:
     *  - PUBLIC events  → everyone
     *  - Game events    → only members of home/away teams
     *  - TEAM events    → only members of one of the listed teams
     *  - CLUB events    → only members of one of the listed clubs
     *  - USER events    → only the explicitly listed users
     *  - No permissions → fallback: everyone (should not occur for restricted events)
     *
     * ROLE_SUPERADMIN is NOT checked here — callers must handle that separately.
     */
    public function canUserParticipateInEvent(User $user, CalendarEvent $event): bool
    {
        // Tournament events: user may participate if they belong to any registered team
        if ($event->getTournament()) {
            foreach ($event->getTournament()->getTeams() as $tournamentTeam) {
                if ($tournamentTeam->getTeam() && $this->isUserInTeam($user, $tournamentTeam->getTeam())) {
                    return true;
                }
            }

            return false;
        }

        // Game events: only team members of participating teams
        if ($event->getGame()) {
            $homeTeam = $event->getGame()->getHomeTeam();
            $awayTeam = $event->getGame()->getAwayTeam();

            if ($homeTeam && $this->isUserInTeam($user, $homeTeam)) {
                return true;
            }
            if ($awayTeam && $this->isUserInTeam($user, $awayTeam)) {
                return true;
            }

            return false;
        }

        $hasAnyRestriction = false;

        foreach ($event->getPermissions() as $permission) {
            switch ($permission->getPermissionType()) {
                case CalendarEventPermissionType::PUBLIC:
                    return true;

                case CalendarEventPermissionType::TEAM:
                    if ($permission->getTeam()) {
                        $hasAnyRestriction = true;
                        if ($this->isUserInTeam($user, $permission->getTeam())) {
                            return true;
                        }
                    }
                    break;

                case CalendarEventPermissionType::CLUB:
                    if ($permission->getClub()) {
                        $hasAnyRestriction = true;
                        if ($this->isUserInClub($user, $permission->getClub())) {
                            return true;
                        }
                    }
                    break;

                case CalendarEventPermissionType::USER:
                    if ($permission->getUser()) {
                        $hasAnyRestriction = true;
                        if ($permission->getUser()->getId() === $user->getId()) {
                            return true;
                        }
                    }
                    break;
            }
        }

        // If there were explicit restrictions but user matched none → deny
        if ($hasAnyRestriction) {
            return false;
        }

        // No restrictions defined → allow (public fallback)
        return true;
    }

    /**
     * @return Team[]
     */
    public function getEventTeams(CalendarEvent $event): array
    {
        $teams = [];

        if ($event->getGame()) {
            if ($event->getGame()->getHomeTeam()) {
                $teams[] = $event->getGame()->getHomeTeam();
            }
            if ($event->getGame()->getAwayTeam()) {
                $teams[] = $event->getGame()->getAwayTeam();
            }
        }

        foreach ($event->getPermissions() as $permission) {
            if (CalendarEventPermissionType::TEAM === $permission->getPermissionType() && $permission->getTeam()) {
                $teams[] = $permission->getTeam();
            }
        }

        return array_unique($teams, SORT_REGULAR);
    }

    public function isUserInTeam(User $user, Team $team): bool
    {
        $playerAssignment = $this->entityManager->getRepository(PlayerTeamAssignment::class)
            ->createQueryBuilder('pta')
            ->innerJoin('pta.player', 'p')
            ->innerJoin('p.userRelations', 'ur')
            ->where('ur.user = :user')
            ->andWhere('pta.team = :team')
            ->setParameter('user', $user)
            ->setParameter('team', $team)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($playerAssignment) {
            return true;
        }

        $coachAssignment = $this->entityManager->getRepository(CoachTeamAssignment::class)
            ->createQueryBuilder('cta')
            ->innerJoin('cta.coach', 'c')
            ->innerJoin('c.userRelations', 'ur')
            ->where('ur.user = :user')
            ->andWhere('cta.team = :team')
            ->setParameter('user', $user)
            ->setParameter('team', $team)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return null !== $coachAssignment;
    }

    public function isUserInClub(User $user, Club $club): bool
    {
        $playerAssignment = $this->entityManager->getRepository(PlayerClubAssignment::class)
            ->createQueryBuilder('pca')
            ->innerJoin('pca.player', 'p')
            ->innerJoin('p.userRelations', 'ur')
            ->where('ur.user = :user')
            ->andWhere('pca.club = :club')
            ->setParameter('user', $user)
            ->setParameter('club', $club)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($playerAssignment) {
            return true;
        }

        $coachAssignment = $this->entityManager->getRepository(CoachClubAssignment::class)
            ->createQueryBuilder('cca')
            ->innerJoin('cca.coach', 'c')
            ->innerJoin('c.userRelations', 'ur')
            ->where('ur.user = :user')
            ->andWhere('cca.club = :club')
            ->setParameter('user', $user)
            ->setParameter('club', $club)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return null !== $coachAssignment;
    }
}
