<?php

namespace App\Security\Voter;

use App\Entity\Game;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends Voter<string, Game>
 */
final class GameVoter extends Voter
{
    public const CREATE = 'POST_CREATE';
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';
    public const DELETE = 'POST_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Game;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                break;
        }

        return false;
    }
}
