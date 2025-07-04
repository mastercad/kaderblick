<?php

namespace App\Security\Voter;

use App\Entity\GameEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, GameEvent>
 */
class EventVoter extends Voter
{
    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['EVENT_CREATE', 'EVENT_EDIT', 'EVENT_DELETE'])
            && ($subject instanceof GameEvent || null === $subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
