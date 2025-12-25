<?php

namespace App\Security\Voter;

use App\Entity\TeamRide;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template-extends Voter<string, TeamRide>
 */
final class TeamRideVoter extends Voter
{
    public const CREATE = 'TEAM_RIDE_CREATE';
    public const EDIT = 'TEAM_RIDE_EDIT';
    public const VIEW = 'TEAM_RIDE_VIEW';
    public const DELETE = 'TEAM_RIDE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof TeamRide;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var ?User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var TeamRide $teamRide */
        $teamRide = $subject;

        switch ($attribute) {
            case self::VIEW:
                // Alle Team-Mitglieder können Fahrgemeinschaften sehen
                return true;
            case self::EDIT:
            case self::DELETE:
                // Nur Ersteller oder Admins
                return $teamRide->getDriver() === $user
                    || in_array('ROLE_ADMIN', $user->getRoles())
                    || in_array('ROLE_SUPERADMIN', $user->getRoles());
            case self::CREATE:
                return true; // Alle authentifizierten User können Fahrgemeinschaften erstellen
        }

        return false;
    }
}
