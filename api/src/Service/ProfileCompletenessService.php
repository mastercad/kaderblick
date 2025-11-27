<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;

class ProfileCompletenessService
{
    /**
     * Calculate the profile completeness percentage for a user
     * 
     * @return int Percentage from 0 to 100
     */
    public function calculateCompleteness(User $user): int
    {
        $fields = [
            'firstName' => $user->getFirstName() !== null && $user->getFirstName() !== '',
            'lastName' => $user->getLastName() !== null && $user->getLastName() !== '',
            'email' => $user->getEmail() !== null && $user->getEmail() !== '',
            'avatar' => $user->getAvatarFilename() !== null,
            'height' => $user->getHeight() !== null,
            'weight' => $user->getWeight() !== null,
            'shoeSize' => $user->getShoeSize() !== null,
            'shirtSize' => $user->getShirtSize() !== null,
            'pantsSize' => $user->getPantsSize() !== null,
            'hasUserRelations' => $user->getUserRelations()->count() > 0,
        ];

        $completedFields = array_filter($fields);
        $totalFields = count($fields);
        
        return (int) round((count($completedFields) / $totalFields) * 100);
    }

    /**
     * Get the milestone levels that should trigger XP rewards
     * 
     * @return array<int> Milestone percentages
     */
    public function getMilestones(): array
    {
        return [25, 50, 75, 100];
    }

    /**
     * Get the next milestone for a given completeness percentage
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
     * Check which milestones have been reached between old and new completeness
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
