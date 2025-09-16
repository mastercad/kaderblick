<?php

namespace App\Entity;

use App\Repository\ParticipationStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ParticipationStatusRepository::class)]
#[ORM\Table(name: 'participation_statuses')]
class ParticipationStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['participation_status:read', 'participation:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['participation_status:read', 'participation:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['participation_status:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Groups(['participation_status:read', 'participation:read'])]
    private ?string $color = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['participation_status:read', 'participation:read'])]
    private ?string $icon = null;

    #[ORM\Column]
    #[Groups(['participation_status:read'])]
    private int $sortOrder = 0;

    #[ORM\Column]
    #[Groups(['participation_status:read'])]
    private bool $isActive = true;

    /** @var Collection<int, Participation> */
    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Participation::class)]
    private Collection $participations;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function __toString(): string
    {
        return $this->name ?? 'Unknown Status';
    }
}
