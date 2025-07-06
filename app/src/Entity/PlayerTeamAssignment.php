<?php

namespace App\Entity;

use App\Repository\PlayerTeamAssignmentRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PlayerTeamAssignmentRepository::class)]
class PlayerTeamAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['player_team_assignment:read', 'player_team_assignment:write', 'team:read', 'player:read', 'club:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private int $id;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'playerTeamAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['player_team_assignment:read', 'team:read'])]
    private ?Player $player;

    #[ORM\ManyToOne(targetEntity: PlayerTeamAssignmentType::class, inversedBy: 'playerTeamAssignments', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['player_team_assignment:read'])]
    private ?PlayerTeamAssignmentType $playerTeamAssignmentType = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'playerTeamAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    // !!! NO team:read right to prevent circular reference in serialization !!!
    #[Groups(['player_team_assignment:read', 'player:read'])]
    private ?Team $team;

    #[ORM\Column(nullable: true)]
    #[Groups(['player_team_assignment:read', 'player_team_assignment:write'])]
    private ?int $shirtNumber = null;

    #[Groups(['player_team_assignment:read', 'player_team_assignment:write'])]
    #[Context(normalizationContext: ['datetime_format' => 'd.m.Y'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $startDate = null;

    #[Groups(['player_team_assignment:read', 'player_team_assignment:write'])]
    #[Context(normalizationContext: ['datetime_format' => 'd.m.Y'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $endDate = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): void
    {
        $this->player = $player;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    public function getShirtNumber(): ?int
    {
        return $this->shirtNumber;
    }

    public function setShirtNumber(?int $shirtNumber): void
    {
        $this->shirtNumber = $shirtNumber;
    }

    public function getPlayerTeamAssignmentType(): ?PlayerTeamAssignmentType
    {
        return $this->playerTeamAssignmentType;
    }

    public function setPlayerTeamAssignmentType(?PlayerTeamAssignmentType $playerTeamAssignmentType): void
    {
        $this->playerTeamAssignmentType = $playerTeamAssignmentType;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeInterface $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function __toString(): string
    {
        $end = $this->endDate ? ' bis ' . $this->endDate->format('d.m.Y') : '';

        return sprintf(
            '%s bei %s (seit %s%s)',
            $this->player?->getFullName() ?? 'Unbekannter Spieler',
            $this->team?->getName() ?? 'Unbekanntes Team',
            $this->startDate?->format('d.m.Y') ?? 'unbekannt',
            $end
        );
    }
}
