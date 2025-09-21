<?php

namespace App\Security\Voter;

use App\Entity\Survey;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template-extends Voter<string, Survey>
 */
final class SurveyVoter extends Voter
{
    public const CREATE = 'SURVEY_CREATE';
    public const EDIT = 'SURVEY_EDIT';
    public const VIEW = 'SURVEY_VIEW';
    public const DELETE = 'SURVEY_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Survey;
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
