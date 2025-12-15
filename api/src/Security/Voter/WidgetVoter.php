<?php

namespace App\Security\Voter;

use App\Entity\DashboardWidget;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template-extends Voter<string, DashboardWidget>
 */
final class WidgetVoter extends Voter
{
    public const CREATE = 'WIDGET_CREATE';
    public const EDIT = 'WIDGET_EDIT';
    public const VIEW = 'WIDGET_VIEW';
    public const DELETE = 'WIDGET_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof DashboardWidget;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var ?User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var DashboardWidget $widget */
        $widget = $subject;

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
            case self::DELETE:
                // Nur eigene Widgets
                return $widget->getUser() === $user;
            case self::CREATE:
                return true; // Alle authentifizierten User können Widgets erstellen
        }

        return false;
    }
}
