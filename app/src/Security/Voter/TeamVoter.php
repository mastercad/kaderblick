<?php

namespace App\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template-extends Voter<string, Team>
 */
final class TeamVoter extends Voter
{
    public const CREATE = 'TEAM_CREATE';
    public const EDIT = 'TEAM_EDIT';
    public const VIEW = 'TEAM_VIEW';
    public const DELETE = 'TEAM_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Team;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var ?User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                if (in_array('ROLE_SUPERADMIN', $user->getRoles())) {
                    return true;
                }

                if (
                    !in_array('ROLE_ADMIN', $user->getRoles())
                    && !in_array('ROLE_SUPPORTER', $user->getRoles())
                ) {
                    return false;
                }

                foreach ($user->getRelations() as $userRelation) {
                    if ($userRelation->getPlayer()) {
                        foreach ($userRelation->getPlayer()->getPlayerTeamAssignments() as $assignment) {
                            if ($assignment->getTeam() === $subject) {
                                return true;
                            }
                        }
                    }

                    if ($userRelation->getCoach()) {
                        foreach ($userRelation->getCoach()->getCoachTeamAssignments() as $assignment) {
                            if ($assignment->getTeam() === $subject) {
                                return true;
                            }
                        }
                    }
                }

                break;
            case self::DELETE:
            case self::EDIT:
                if (in_array('ROLE_SUPERADMIN', $user->getRoles())) {
                    return true;
                }

                if (
                    !in_array('ROLE_ADMIN', $user->getRoles())
                    && !in_array('ROLE_SUPPORTER', $user->getRoles())
                ) {
                    return false;
                }

                foreach ($user->getRelations() as $userRelation) {
                    if ($userRelation->getPlayer()) {
                        foreach ($userRelation->getPlayer()->getPlayerTeamAssignments() as $assignment) {
                            if ($assignment->getTeam() === $subject) {
                                return true;
                            }
                        }
                    }

                    if ($userRelation->getCoach()) {
                        foreach ($userRelation->getCoach()->getCoachTeamAssignments() as $assignment) {
                            if ($assignment->getTeam() === $subject) {
                                return true;
                            }
                        }
                    }
                }
                break;
            case self::VIEW:
                return true;
        }

        return false;
    }
}
