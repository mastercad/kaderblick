<?php

namespace App\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\Video;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template-extends Voter<string, Video>
 */
final class VideoVoter extends Voter
{
    public const CREATE = 'VIDEO_CREATE';
    public const EDIT = 'VIDEO_EDIT';
    public const VIEW = 'VIDEO_VIEW';
    public const DELETE = 'VIDEO_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return (
            in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE])
                && $subject instanceof Video)
            || (self::CREATE === $attribute && $subject instanceof Team)
        ;
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

                foreach ($user->getUserRelations() as $userRelation) {
                    if ($userRelation->getPlayer()) {
                        foreach ($userRelation->getPlayer()->getPlayerTeamAssignments() as $assignment) {
                            /** @var Team $subject */
                            if ($assignment->getTeam() === $subject) {
                                return true;
                            }
                        }
                    }

                    if ($userRelation->getCoach()) {
                        foreach ($userRelation->getCoach()->getCoachTeamAssignments() as $assignment) {
                            /** @var Team $subject */
                            if ($assignment->getTeam() === $subject) {
                                return true;
                            }
                        }
                    }
                }

                // hier ist subject team, nicht video!
                break;
            case self::DELETE:
            case self::EDIT:
                if (in_array('ROLE_SUPERADMIN', $user->getRoles())) {
                    return true;
                }

                if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                    return false;
                }

                foreach ($user->getUserRelations() as $userRelation) {
                    if ($userRelation->getPlayer()) {
                        foreach ($userRelation->getPlayer()->getPlayerTeamAssignments() as $assignment) {
                            /** @var Video $subject */
                            if ($assignment->getTeam() === $subject->getGame()->getHomeTeam()) {
                                return true;
                            }
                        }
                    }

                    if ($userRelation->getCoach()) {
                        foreach ($userRelation->getCoach()->getCoachTeamAssignments() as $assignment) {
                            /** @var Video $subject */
                            if ($assignment->getTeam() === $subject->getGame()->getHomeTeam()) {
                                return true;
                            }
                        }
                    }
                }
                break;
            case self::VIEW:
                if (in_array('ROLE_SUPERADMIN', $user->getRoles())) {
                    return true;
                }

                foreach ($user->getUserRelations() as $userRelation) {
                    if ($userRelation->getPlayer()) {
                        foreach ($userRelation->getPlayer()->getPlayerTeamAssignments() as $assignment) {
                            /** @var Video $subject */
                            if ($assignment->getTeam() === $subject->getGame()->getHomeTeam()) {
                                return true;
                            }
                        }
                    }

                    if ($userRelation->getCoach()) {
                        foreach ($userRelation->getCoach()->getCoachTeamAssignments() as $assignment) {
                            /** @var Video $subject */
                            if ($assignment->getTeam() === $subject->getGame()->getHomeTeam()) {
                                return true;
                            }
                        }
                    }
                }
                break;
        }

        return false;
    }
}
