<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\PlayerNationalityAssignmentRepository;

#[ORM\Entity(repositoryClass: PlayerNationalityAssignmentRepository::class)]
class PlayerNationalityAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(['player_nationality_assignment:read', 'player_nationality_assignment:write', 'player:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'playerNationalityAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['player_nationality_assignment:read', 'player_nationality_assignment:write'])]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Nationality::class, inversedBy: 'playerNationalityAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['player_nationality_assignment:read', 'player_nationality_assignment:write', 'player:read'])]
    private ?Nationality $nationality = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['player_nationality_assignment:read', 'player_nationality_assignment:write'])]
    private DateTimeInterface $startDate;

    #[Groups(['player_nationality_assignment:read', 'player_nationality_assignment:write'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $endDate = null;

    #[Groups(['player_nationality_assignment:read', 'player_nationality_assignment:write'])]
    #[ORM\Column(type: "boolean", options: ["default" => true])]
    private bool $active = true; // Falls mal aus historischen Gründen etwas deaktiviert werden soll

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNationality(): ?Nationality
    {
        return $this->nationality;
    }

    public function setNationality(?Nationality $nationality): self
    {
        $this->nationality = $nationality;
        return $this;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function __toString(): string
    {
        return $this->nationality?->getName() ?? 'UNKNOWN NATIONALITY';
    }
}
