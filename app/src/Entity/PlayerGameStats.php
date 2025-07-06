<?php

namespace App\Entity;

use App\Repository\PlayerGameStatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerGameStatsRepository::class)]
class PlayerGameStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Game $game;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Player $player;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $minutesPlayed = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $shots = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $shotsOnTarget = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $passes = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $passesCompleted = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $tackles = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $interceptions = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $foulsCommitted = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $foulsSuffered = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $distanceCovered = null;

    public function __construct(Game $game, Player $player)
    {
        $this->game = $game;
        $this->player = $player;
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

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getMinutesPlayed(): ?int
    {
        return $this->minutesPlayed;
    }

    public function setMinutesPlayed(?int $minutesPlayed): self
    {
        $this->minutesPlayed = $minutesPlayed;

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

    public function getPasses(): ?int
    {
        return $this->passes;
    }

    public function setPasses(?int $passes): self
    {
        $this->passes = $passes;

        return $this;
    }

    public function getPassesCompleted(): ?int
    {
        return $this->passesCompleted;
    }

    public function setPassesCompleted(?int $passesCompleted): self
    {
        $this->passesCompleted = $passesCompleted;

        return $this;
    }

    public function getTackles(): ?int
    {
        return $this->tackles;
    }

    public function setTackles(?int $tackles): self
    {
        $this->tackles = $tackles;

        return $this;
    }

    public function getInterceptions(): ?int
    {
        return $this->interceptions;
    }

    public function setInterceptions(?int $interceptions): self
    {
        $this->interceptions = $interceptions;

        return $this;
    }

    public function getFoulsCommitted(): ?int
    {
        return $this->foulsCommitted;
    }

    public function setFoulsCommitted(?int $foulsCommitted): self
    {
        $this->foulsCommitted = $foulsCommitted;

        return $this;
    }

    public function getFoulsSuffered(): ?int
    {
        return $this->foulsSuffered;
    }

    public function setFoulsSuffered(?int $foulsSuffered): self
    {
        $this->foulsSuffered = $foulsSuffered;

        return $this;
    }

    public function getDistanceCovered(): ?int
    {
        return $this->distanceCovered;
    }

    public function setDistanceCovered(?int $distanceCovered): self
    {
        $this->distanceCovered = $distanceCovered;

        return $this;
    }
}
