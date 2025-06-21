<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\GoalRepository;

#[ORM\Entity(repositoryClass: GoalRepository::class)]
#[ORM\Table(name: 'goals')]
class Goal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(['goal:read', 'game:read', 'player:read', 'team:read'])]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $minute;

    // Ob das Eigentor war
    #[ORM\Column(type: 'boolean')]
    private bool $ownGoal = false;

    // Optional: Ob das Tor per Elfmeter gefallen ist
    #[ORM\Column(type: 'boolean')]
    private bool $penalty = false;

    #[Groups(['goal:read'])]
    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'goals')]
    #[ORM\JoinColumn(nullable: false)]
    private Player $scorer;

    #[Groups(['goal:read'])]
    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'goals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'assistsProvided')]
    private ?Player $assistBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScorer(): Player
    {
        return $this->scorer;
    }

    public function setScorer(?Player $scorer): self
    {
        $this->scorer = $scorer;
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

    public function getMinute(): int
    {
        return $this->minute;
    }

    public function setMinuten(int $minute): self
    {
        $this->minute = $minute;
        return $this;
    }

    public function getAssistBy(): ?Player
    {
        return $this->assistBy;
    }

    public function setAssistBy(?Player $assistBy): self
    {
        $this->assistBy = $assistBy;
        return $this;
    }

    public function isOwnGoal(): bool
    {
        return $this->ownGoal;
    }

    public function setOwnGoal(bool $ownGoal): self
    {
        $this->ownGoal = $ownGoal;
        return $this;
    }

    public function isPenalty(): bool
    {
        return $this->penalty;
    }

    public function setPenalty(bool $penalty): self
    {
        $this->penalty = $penalty;
        return $this;
    }
}
