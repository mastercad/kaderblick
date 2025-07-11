<?php

namespace App\Entity;

use App\Repository\UserRelationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRelationRepository::class)]
#[ORM\Table(name: 'user_relations')]
class UserRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'relatedTo')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'relatedFrom')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $relatedUser = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Coach::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Coach $coach = null;

    #[ORM\ManyToOne(targetEntity: RelationType::class)]
    #[ORM\JoinColumn(nullable: false)]
    private RelationType $relationType;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $permissions = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRelatedUser(): ?User
    {
        return $this->relatedUser;
    }

    public function setRelatedUser(?User $relatedUser): self
    {
        $this->relatedUser = $relatedUser;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getCoach(): ?Coach
    {
        return $this->coach;
    }

    public function setCoach(?Coach $coach): self
    {
        $this->coach = $coach;

        return $this;
    }

    public function getRelationType(): RelationType
    {
        return $this->relationType;
    }

    public function setRelationType(RelationType $relationType): self
    {
        $this->relationType = $relationType;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param list<string> $permissions
     */
    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    public function addPermission(string $permission): self
    {
        if (!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission;
        }

        return $this;
    }

    public function removePermission(string $permission): self
    {
        $this->permissions = array_filter(
            $this->permissions,
            fn ($p) => $p !== $permission
        );

        return $this;
    }
}
