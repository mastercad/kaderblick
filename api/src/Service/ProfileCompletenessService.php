<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;

class ProfileCompletenessService
{
    /**
     * Calculate the profile completeness percentage for a user.
     *
     * @return int Percentage from 0 to 100
     */
    public function calculateCompleteness(User $user): int
    {
        $fields = [
            'firstName' => null !== $user->getFirstName() && '' !== $user->getFirstName(),
            'lastName' => null !== $user->getLastName() && '' !== $user->getLastName(),
            'email' => null !== $user->getEmail() && '' !== $user->getEmail(),
            'avatar' => null !== $user->getAvatarFilename(),
            'height' => null !== $user->getHeight(),
            'weight' => null !== $user->getWeight(),
            'shoeSize' => null !== $user->getShoeSize(),
            'shirtSize' => null !== $user->getShirtSize(),
            'pantsSize' => null !== $user->getPantsSize(),
            'hasUserRelations' => $user->getUserRelations()->count() > 0,
        ];

        $completedFields = array_filter($fields);
        $totalFields = count($fields);

        return (int) round((count($completedFields) / $totalFields) * 100);
    }

    /**
     * Get the milestone levels that should trigger XP rewards.
     *
     * @return array<int> Milestone percentages
     */
    public function getMilestones(): array
    {
        return [25, 50, 75, 100];
    }

    /**
     * Get the next milestone for a given completeness percentage.
     */
    public function getNextMilestone(int $currentCompleteness): ?int
    {
        $milestones = $this->getMilestones();

        foreach ($milestones as $milestone) {
            if ($currentCompleteness < $milestone) {
                return $milestone;
            }
        }

        return null;
    }

    /**
     * Check which milestones have been reached between old and new completeness.
     *
     * @return array<int> Array of reached milestones
     */
    public function getReachedMilestones(int $oldCompleteness, int $newCompleteness): array
    {
        $milestones = $this->getMilestones();
        $reachedMilestones = [];

        foreach ($milestones as $milestone) {
            if ($oldCompleteness < $milestone && $newCompleteness >= $milestone) {
                $reachedMilestones[] = $milestone;
            }
        }

        return $reachedMilestones;
    }
}
