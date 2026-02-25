<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tournament_matches')]
class TournamentMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null; // @phpstan-ignore property.unusedType

    #[ORM\ManyToOne(targetEntity: Tournament::class, inversedBy: 'matches')]
    #[ORM\JoinColumn(name: 'tournament_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Tournament $tournament = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $round = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $slot = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'home_team_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Team $homeTeam = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'away_team_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Team $awayTeam = null;

    #[ORM\OneToOne(targetEntity: Game::class, inversedBy: 'tournamentMatch', cascade: ['persist', 'remove'])]
    private ?Game $game = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'next_match_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?self $nextMatch = null;

    #[ORM\Column(length: 50)]
    private string $status = 'scheduled';

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $stage = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $scheduledAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(
        name: 'location_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?Location $location = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): self
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getRound(): ?int
    {
        return $this->round;
    }

    public function setRound(?int $round): self
    {
        $this->round = $round;

        return $this;
    }

    public function getSlot(): ?int
    {
        return $this->slot;
    }

    public function setSlot(?int $slot): self
    {
        $this->slot = $slot;

        return $this;
    }

    public function getHomeTeam(): ?Team
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(?Team $homeTeam): self
    {
        $this->homeTeam = $homeTeam;

        return $this;
    }

    public function getAwayTeam(): ?Team
    {
        return $this->awayTeam;
    }

    public function setAwayTeam(?Team $awayTeam): self
    {
        $this->awayTeam = $awayTeam;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getNextMatch(): ?self
    {
        return $this->nextMatch;
    }

    public function setNextMatch(?self $nextMatch): self
    {
        $this->nextMatch = $nextMatch;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getScheduledAt(): ?DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?DateTimeInterface $scheduledAt): self
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    public function getStage(): ?string
    {
        return $this->stage;
    }

    public function setStage(?string $stage): self
    {
        $this->stage = $stage;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }
}
