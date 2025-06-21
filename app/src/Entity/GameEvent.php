<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\GameEventRepository;
use Symfony\Component\Serializer\Attribute\Context;

#[ORM\Entity(repositoryClass: GameEventRepository::class)]
#[ORM\Table(name: 'game_events')]
class GameEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['game_event:read', 'game_event:write', 'team:read', 'game:read', 'calendar_event:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'gameEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['game_event:read', 'game_event:write', 'calendar_event:read'])]
    private Game $game;

    #[ORM\ManyToOne(targetEntity: GameEventType::class, inversedBy: 'gameEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['game_event:read', 'game_event:write', 'game:read', 'calendar_event:read'])]
    private GameEventType $gameEventType;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'gameEvents')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['game_event:read', 'game_event:write'])]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'gameEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['game_event:read', 'game_event:write'])]
    private Team $team;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['game_event:read', 'game_event:write'])]
    #[Context(normalizationContext: ['datetime_format' => 'd.m.Y H:i:s'])]
    private ?DateTimeInterface $timestamp;

    #[Groups(['game_event:read', 'game_event:write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Groups(['game_event:read', 'game_event:write'])]
    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Player $relatedPlayer = null;

    #[Groups(['game_event:read', 'game_event:write'])]
    #[ORM\ManyToOne(targetEntity: SubstitutionReason::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?SubstitutionReason $substitutionReason = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;
        return $this;
    }

    public function getGameEventType(): GameEventType
    {
        return $this->gameEventType;
    }

    public function setGameEventType(GameEventType $gameEventType): self
    {
        $this->gameEventType = $gameEventType;
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

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getRelatedPlayer(): ?Player
    {
        return $this->relatedPlayer;
    }

    public function setRelatedPlayer(?Player $relatedPlayer): self
    {
        $this->relatedPlayer = $relatedPlayer;
        return $this;
    }

    public function getSubstitutionReason(): ?SubstitutionReason
    {
        return $this->substitutionReason;
    }

    public function setSubstitutionReason(?SubstitutionReason $reason): self
    {
        $this->substitutionReason = $reason;
        return $this;
    }

    // Hilfsmethoden für Ein-/Auswechslungen
    public function isSubstitution(): bool
    {
        return $this->getGameEventType()->getCode() === 'substitution';
    }

    public function isSubstitutionIn(): bool
    {
        return $this->getGameEventType()->getCode() === 'substitution_in';
    }

    public function isSubstitutionOut(): bool
    {
        return $this->getGameEventType()->getCode() === 'substitution_out';
    }

    public function __toString(): string
    {
        $output = $this->gameEventType?->getName() ?? 'Unbekanntes Ereignis';
        
        if ($this->player) {
            $output .= ' - ' . $this->player->__toString();
        }
        
        if ($this->timestamp) {
            $output .= ' (' . $this->timestamp->format('H:i') . ' Uhr)';
        }
        
        return $output;
    }
}
