<?php

namespace App\Service;

class XPService
{
    public function retrieveXPForAction(string $action): int
    {
        return match ($action) {
            'create_post' => 10,
            'comment' => 5,
            'like' => 2,
            'share' => 8,
            default => 0,
        };
    }

    public function addXPToUser(int $userId, int $xp): void
    {
        // Logic to add XP to the user in the database
    }

    public function calculateUserXP(int $userId): int
    {
        // Logic to retrieve the user's current XP from the database
        return 0; // Placeholder return value
    }

    public function calculateUserLevel(int $userId): int
    {
        // Logic to retrieve the user's current level from the database
        return 1; // Placeholder return value
    }

    public function levelUpUser(int $userId): bool
    {
        // Logic to check if the user has enough XP to level up and perform the level-up
        return false; // Placeholder return value
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
