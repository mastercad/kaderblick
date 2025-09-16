<?php

namespace App\Security\Voter;

use App\Entity\Coach;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template-extends Voter<string, Coach>
 */
final class CoachVoter extends Voter
{
    public const CREATE = 'COACH_CREATE';
    public const EDIT = 'COACH_EDIT';
    public const VIEW = 'COACH_VIEW';
    public const DELETE = 'COACH_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Coach;
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
            case self::EDIT:
            case self::DELETE:
                if (
                    in_array('ROLE_ADMIN', $user->getRoles())
                    || in_array('ROLE_SUPERADMIN', $user->getRoles())
                ) {
                    return true;
                }

                break;
            case self::VIEW:
                return true;
        }

        return false;
    }
}
