<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
    name: 'formations',
    indexes: [
        new ORM\Index(name: 'idx_formation_user_id', columns: ['user_id']),
        new ORM\Index(name: 'idx_formation_formation_type_id', columns: ['formation_type_id'])
    ]
)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /** @var array<string, mixed> $formationData */
    #[ORM\Column(type: 'json')]
    private array $formationData = [];

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(
        name: 'user_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: FormationType::class, inversedBy: 'formations')]
    #[ORM\JoinColumn(
        name: 'formation_type_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?FormationType $formationType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormationData(): array
    {
        return $this->formationData;
    }

    /**
     * @param array<string, mixed> $formationData
     */
    public function setFormationData(array $formationData): self
    {
        $this->formationData = $formationData;

        return $this;
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

    public function getFormationType(): ?FormationType
    {
        return $this->formationType;
    }

    public function setFormationType(?FormationType $formationType): self
    {
        $this->formationType = $formationType;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name . ' (' . $this->formationType?->getName() . ')';
    }
}
