<?php

namespace App\Entity;

use App\Repository\UserRelationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRelationRepository::class)]
#[ORM\Table(name: 'user_relations')]
#[ORM\Index(name: 'idx_user_relations_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_user_relations_player_id', columns: ['player_id'])]
#[ORM\Index(name: 'idx_user_relations_coach_id', columns: ['coach_id'])]
#[ORM\Index(name: 'idx_user_relations_relation_type_id', columns: ['relation_type_id'])]
class UserRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userRelations')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'userRelations')]
    #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'id', nullable: true)]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Coach::class, inversedBy: 'userRelations')]
    #[ORM\JoinColumn(name: 'coach_id', referencedColumnName: 'id', nullable: true)]
    private ?Coach $coach = null;

    #[ORM\ManyToOne(targetEntity: RelationType::class)]
    #[ORM\JoinColumn(name: 'relation_type_id', referencedColumnName: 'id', nullable: false)]
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
