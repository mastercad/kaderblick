<?php

namespace App\Entity;

use App\Repository\PlayerClubAssignmentRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PlayerClubAssignmentRepository::class)]
class PlayerClubAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['player_club_assignment:read', 'player_club_assignment:write', 'club:read', 'player:read'])]
    private ?int $id = null;

    #[Groups(['player_club_assignment:read', 'player_club_assignment:write', 'club:read'])]
    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'playerClubAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Club::class, inversedBy: 'playerClubAssignments')]
    #[Groups(['player_club_assignment:read', 'player_club_assignment:write', 'player:read'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Club $club = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['player_club_assignment:read', 'player_club_assignment:write', 'club:read'])]
    #[Context(normalizationContext: ['datetime_format' => 'd.m.Y'])]
    private DateTimeInterface $startDate;

    #[Groups(['player_club_assignment:read', 'player_club_assignment:write'])]
    #[Context(normalizationContext: ['datetime_format' => 'd.m.Y'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $endDate = null;

    #[Groups(['player_club_assignment:read', 'player_club_assignment:write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $active = true;

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

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): self
    {
        $this->club = $club;

        return $this;
    }

    public function getStartDate(): DateTimeInterface
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
        $end = $this->endDate ? ' bis ' . $this->endDate->format('d.m.Y') : '';

        return sprintf(
            '%s bei %s (seit %s%s)',
            $this->player?->getFullName() ?? 'Unbekannter Spieler',
            $this->club?->getName() ?? 'Unbekannter Verein',
            $this->startDate?->format('d.m.Y') ?? 'unbekannt',
            $end
        );
    }
}
