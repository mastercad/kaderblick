<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\UserXpEvent;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class XPRegistrationService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function registerXpEvent(User $user, string $actionType, int $actionId): void
    {
        // Check if an XP event for this action already exists to prevent duplicates
        $existingEvent = $this->entityManager->getRepository(UserXpEvent::class)
            ->findOneBy([
                'user' => $user,
                'actionType' => $actionType,
                'actionId' => $actionId,
            ]);

        if ($existingEvent) {
            return; // XP event already exists, do not add another
        }

        // Define XP values for different action types
        $xpValues = [
            'calendar_event' => 10,
            'profile_update' => 5,
            'game_event' => 15,
        ];

        if (!array_key_exists($actionType, $xpValues)) {
            throw new InvalidArgumentException("Unknown action type: $actionType");
        }

        $xpEvent = new UserXpEvent();
        $xpEvent->setUser($user);
        $xpEvent->setActionType($actionType);
        $xpEvent->setActionId($actionId);
        $xpEvent->setXpValue($xpValues[$actionType]);
        $xpEvent->setIsProcessed(false);

        $this->entityManager->persist($xpEvent);
        $this->entityManager->flush();
    }
}
