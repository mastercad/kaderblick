<?php

namespace App\Security\Voter;

use App\Entity\Formation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template-extends Voter<string, Formation>
 */
final class FormationVoter extends Voter
{
    public const CREATE = 'FORMATION_CREATE';
    public const EDIT = 'FORMATION_EDIT';
    public const VIEW = 'FORMATION_VIEW';
    public const DELETE = 'FORMATION_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Formation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var ?User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Formation $formation */
        $formation = $subject;

        switch ($attribute) {
            case self::VIEW:
                // Eigene Formations oder Admins
                return $formation->getUser()->getId() === $user->getId()
                    || in_array('ROLE_ADMIN', $user->getRoles())
                    || in_array('ROLE_SUPERADMIN', $user->getRoles());
            case self::EDIT:
            case self::DELETE:
                // Nur Ersteller oder Admins
                return $formation->getUser()->getId() === $user->getId()
                    || in_array('ROLE_ADMIN', $user->getRoles())
                    || in_array('ROLE_SUPERADMIN', $user->getRoles());
            case self::CREATE:
                return true; // Alle authentifizierten User können Aufstellungen erstellen
        }

        return false;
    }
}
