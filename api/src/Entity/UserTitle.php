<?php

namespace App\Entity;

use App\Repository\UserTitleRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserTitleRepository::class)]
#[ORM\Table(
    name: 'user_titles',
    indexes: [
        new ORM\Index(name: 'idx_user_titles_user_id', columns: ['user_id']),
        new ORM\Index(name: 'idx_user_titles_team_id', columns: ['team_id']),
        new ORM\Index(name: 'idx_user_titles_is_active', columns: ['is_active']),
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: 'uniq_user_title_active',
            columns: ['user_id', 'title_category', 'title_scope', 'team_id', 'is_active']
        )
    ]
)]
class UserTitle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user;

    #[ORM\Column(type: 'string', length: 50)]
    private string $titleCategory; // 'top_scorer', 'top_assist', etc.

    #[ORM\Column(type: 'string', length: 20)]
    private string $titleScope; // 'platform' or 'team'

    #[ORM\Column(type: 'string', length: 20)]
    private string $titleRank; // 'gold', 'silver', 'bronze'

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Team $team = null;

    #[ORM\Column(type: 'integer')]
    private int $value; // Number of goals, assists, etc.

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $awardedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $revokedAt = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $season = null; // e.g., '2024/2025'

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTitleCategory(): string
    {
        return $this->titleCategory;
    }

    public function setTitleCategory(string $titleCategory): self
    {
        $this->titleCategory = $titleCategory;

        return $this;
    }

    public function getTitleScope(): string
    {
        return $this->titleScope;
    }

    public function setTitleScope(string $titleScope): self
    {
        $this->titleScope = $titleScope;

        return $this;
    }

    public function getTitleRank(): string
    {
        return $this->titleRank;
    }

    public function setTitleRank(string $titleRank): self
    {
        $this->titleRank = $titleRank;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getAwardedAt(): DateTimeImmutable
    {
        return $this->awardedAt;
    }

    public function setAwardedAt(DateTimeImmutable $awardedAt): self
    {
        $this->awardedAt = $awardedAt;

        return $this;
    }

    public function getRevokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function setRevokedAt(?DateTimeImmutable $revokedAt): self
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }

    public function getSeason(): ?string
    {
        return $this->season;
    }

    public function setSeason(?string $season): self
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Get priority for sorting (lower = higher priority).
     */
    public function getPriority(): int
    {
        $scopePriority = match ($this->titleScope) {
            'platform' => 0,
            'team' => 100,
            default => 200,
        };

        $rankPriority = match ($this->titleRank) {
            'gold' => 0,
            'silver' => 10,
            'bronze' => 20,
            default => 30,
        };

        return $scopePriority + $rankPriority;
    }
}
