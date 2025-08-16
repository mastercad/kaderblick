<?php

namespace App\Entity;

use App\Repository\TeamGameStatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamGameStatsRepository::class)]
#[ORM\Table(name: 'team_game_stats')]
class TeamGameStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'id', nullable: false)]
    private Game $game;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: false)]
    private Team $team;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $possession = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $corners = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $offsides = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $shots = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $shotsOnTarget = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $fouls = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $yellowCards = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $redCards = null;

    public function __construct(Game $game, Team $team)
    {
        $this->game = $game;
        $this->team = $team;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getPossession(): ?int
    {
        return $this->possession;
    }

    public function setPossession(?int $possession): self
    {
        $this->possession = $possession;

        return $this;
    }

    public function getCorners(): ?int
    {
        return $this->corners;
    }

    public function setCorners(?int $corners): self
    {
        $this->corners = $corners;

        return $this;
    }

    public function getOffsides(): ?int
    {
        return $this->offsides;
    }

    public function setOffsides(?int $offsides): self
    {
        $this->offsides = $offsides;

        return $this;
    }

    public function getShots(): ?int
    {
        return $this->shots;
    }

    public function setShots(?int $shots): self
    {
        $this->shots = $shots;

        return $this;
    }

    public function getShotsOnTarget(): ?int
    {
        return $this->shotsOnTarget;
    }

    public function setShotsOnTarget(?int $shotsOnTarget): self
    {
        $this->shotsOnTarget = $shotsOnTarget;

        return $this;
    }

    public function getFouls(): ?int
    {
        return $this->fouls;
    }

    public function setFouls(?int $fouls): self
    {
        $this->fouls = $fouls;

        return $this;
    }

    public function getYellowCards(): ?int
    {
        return $this->yellowCards;
    }

    public function setYellowCards(?int $yellowCards): self
    {
        $this->yellowCards = $yellowCards;

        return $this;
    }

    public function getRedCards(): ?int
    {
        return $this->redCards;
    }

    public function setRedCards(?int $redCards): self
    {
        $this->redCards = $redCards;

        return $this;
    }
}
