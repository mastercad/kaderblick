<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\UserXpEvent;
use Doctrine\ORM\EntityManagerInterface;

class XPEventProcessor
{
    public function __construct(private EntityManagerInterface $entityManager, private XPService $xpService)
    {
    }

    public function processPendingXpEvents(): void
    {
        $pendingXpEvents = $this->entityManager->getRepository(UserXpEvent::class)
            ->findBy(['isProcessed' => false]);

        foreach ($pendingXpEvents as $xpEvent) {
            /** @var User $user */
            $user = $xpEvent->getUser();
            $currentXP = $user->getUserLevel()->getXpTotal();
            $this->xpService->addXpForAction($user, $xpEvent->getActionType(), $xpEvent->getActionId());
            $xpEvent->setIsProcessed(true);

            if ($currentXP !== $user->getUserLevel()->getXpTotal()) {
                $this->entityManager->persist($user);
            }

            $this->entityManager->persist($xpEvent);
        }

        $this->entityManager->flush();
    }
}
