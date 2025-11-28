<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserLevel;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class XPService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function retrieveXPForAction(string $action): int
    {
        return match ($action) {
            'create_post' => 10,
            'comment' => 5,
            'like' => 2,
            'share' => 8,
            'calendar_event' => 10,
            'profile_update' => 5,
            'game_event' => 15,
            'goal_scored' => 50,
            'goal_assisted' => 30,
            'profile_completion_25' => 25,
            'profile_completion_50' => 50,
            'profile_completion_75' => 75,
            'profile_completion_100' => 100,
            default => 0,
        };
    }

    public function addXPToUser(User $user, int $xp): void
    {
        $userLevel = $user->getUserLevel();
        if (null === $userLevel) {
            $userLevel = new UserLevel();
            $userLevel->setUser($user);
            $userLevel->setXpTotal(0);
            $userLevel->setLevel(1);
            $userLevel->setUpdatedAt(new DateTimeImmutable());
            $user->setUserLevel($userLevel);
            $this->entityManager->persist($userLevel);
        }
        $currentXP = $userLevel->getXpTotal();
        $currentLevel = $userLevel->getLevel();
        // Add XP
        $newXP = $currentXP + $xp;
        $userLevel->setXpTotal($newXP);
        // Check for level up
        $newLevel = $this->retrieveLevelForXP($newXP);
        if ($newLevel > $currentLevel) {
            $userLevel->setLevel($newLevel);
        }
        $userLevel->setUpdatedAt(new DateTimeImmutable());
        $this->entityManager->persist($userLevel);
        $this->entityManager->flush();
    }

    public function addXpForAction(User $user, string $action, ?int $referenceId = null): void
    {
        $xp = $this->retrieveXPForAction($action);
        if ($xp > 0) {
            $this->addXPToUser($user, $xp);
        }
    }

    public function calculateUserXP(User $user): int
    {
        $userLevel = $user->getUserLevel();
        if (null === $userLevel) {
            return 0;
        }

        return $userLevel->getXpTotal();
    }

    public function calculateUserLevel(User $user): int
    {
        $userLevel = $user->getUserLevel();
        if (null === $userLevel) {
            return 1;
        }

        return $userLevel->getLevel();
    }

    public function levelUpUser(User $user): bool
    {
        $currentXP = $this->calculateUserXP($user);
        $currentLevel = $this->calculateUserLevel($user);
        $requiredXP = $this->retrieveXpForLevel($currentLevel + 1);

        if ($currentXP >= $requiredXP) {
            $user->getUserLevel()->setLevel($currentLevel + 1);
            $user->getUserLevel()->setUpdatedAt(new DateTimeImmutable());
            $this->entityManager->persist($user->getUserLevel());
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    public function retrieveXpForLevel(int $level, int $base = 50, float $exponent = 1.5): int
    {
        return (int) round($base * pow($level, $exponent));
    }

    public function retrieveLevelForXP(int $xp, int $base = 50, float $exponent = 1.5): int
    {
        return (int) floor(pow($xp / $base, 1 / $exponent));
    }
}
