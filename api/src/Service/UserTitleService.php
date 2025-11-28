<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\UserTitle;
use App\Repository\UserTitleRepository;

class UserTitleService
{
    public function __construct(
        private UserTitleRepository $userTitleRepository
    ) {
    }

    /**
     * Get the highest priority title for a user
     * This is the title that should be displayed (e.g., for avatar frame)
     */
    public function loadDisplayTitle(User $user): ?UserTitle
    {
        return $this->userTitleRepository->findHighestPriorityTitle($user);
    }

    /**
     * Get all active titles for a user
     * 
     * @return UserTitle[]
     */
    public function loadAllTitles(User $user): array
    {
        return $this->userTitleRepository->findActiveByUser($user);
    }

    /**
     * Get title data formatted for frontend
     * 
     * @return array<string, mixed>
     */
    public function retrieveTitleDataForUser(User $user): array
    {
        $displayTitle = $this->loadDisplayTitle($user);
        
        if (!$displayTitle) {
            return [
                'hasTitle' => false,
                'displayTitle' => null,
                'avatarFrame' => null,
                'allTitles' => [],
            ];
        }

        return [
            'hasTitle' => true,
            'displayTitle' => $this->formatTitle($displayTitle),
            'avatarFrame' => $this->retrieveAvatarFrameIdentifier($displayTitle),
            'allTitles' => array_map(
                fn(UserTitle $title) => $this->formatTitle($title),
                $this->loadAllTitles($user)
            ),
        ];
    }

    /**
     * Format title for API response
     * 
     * @return array<string, mixed>
     */
    private function formatTitle(UserTitle $title): array
    {
        return [
            'id' => $title->getId(),
            'category' => $title->getTitleCategory(),
            'scope' => $title->getTitleScope(),
            'rank' => $title->getTitleRank(),
            'value' => $title->getValue(),
            'teamId' => $title->getTeam()?->getId(),
            'teamName' => $title->getTeam()?->getName(),
            'season' => $title->getSeason(),
            'awardedAt' => $title->getAwardedAt()->format('Y-m-d H:i:s'),
            'displayName' => $this->retrieveTitleDisplayName($title),
            'priority' => $title->getPriority(),
        ];
    }

    /**
     * Get human-readable title name
     */
    private function retrieveTitleDisplayName(UserTitle $title): string
    {
        $categoryNames = [
            'top_scorer' => 'Torschützenkönig',
            'top_assist' => 'Vorlagenkönig',
        ];

        $categoryName = $categoryNames[$title->getTitleCategory()] ?? $title->getTitleCategory();
        $rank = ucfirst($title->getTitleRank());
        $scope = $title->getTitleScope() === 'platform' ? 'Platform' : 'Team';

        return "{$scope} {$categoryName} - {$rank}";
    }

    /**
     * Get avatar frame identifier for CSS/image selection
     * Format: {scope}_{rank} (e.g., "platform_gold", "team_silver")
     */
    private function retrieveAvatarFrameIdentifier(UserTitle $title): string
    {
        return sprintf(
            '%s_%s_%s',
            $title->getTitleScope(),
            $title->getTitleCategory(),
            $title->getTitleRank()
        );
    }

    /**
     * Check if user has a specific title
     */
    public function hasTitle(User $user, string $category, string $scope, string $rank): bool
    {
        $titles = $this->loadAllTitles($user);
        
        foreach ($titles as $title) {
            if ($title->getTitleCategory() === $category 
                && $title->getTitleScope() === $scope 
                && $title->getTitleRank() === $rank
            ) {
                return true;
            }
        }
        
        return false;
    }   
    
    /**
     * Returns grouped title stats for admin overview (category, scope, rank, count)
     *
     * @return array<int, array<string, mixed>>
     */
    public function retrieveTitleStats(): array
    {
        $qb = $this->userTitleRepository->createQueryBuilder('t');
        $qb->select('t.titleCategory, t.titleScope, t.titleRank, COUNT(t.id) AS userCount')
            ->groupBy('t.titleCategory, t.titleScope, t.titleRank')
            ->orderBy('userCount', 'DESC');
        return $qb->getQuery()->getArrayResult();
    }
}
