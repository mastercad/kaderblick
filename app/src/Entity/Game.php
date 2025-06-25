<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\GameRepository;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\Table(name: 'game')]
#[ORM\HasLifecycleCallbacks]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['game:read', 'game:write', 'team:read', 'player:read', 'game_event:read', 'calendar_event:read'])]
    private int $id;

    #[Groups(['game:read', 'game:write', 'game_event:read', 'calendar_event:read'])]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    private ?Team $homeTeam = null;

    #[Groups(['game:read', 'game:write', 'game_event:read', 'calendar_event:read'])]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    private ?Team $awayTeam = null;

    #[Groups(['game:read', 'game:write', 'game_event:read'])]
    #[Assert\NotNull(message: 'Das Spieldatum darf nicht leer sein.')]
    #[Context(normalizationContext: ['datetime_format' => 'd.m.Y H:i'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $date;

    #[Groups(['game:read', 'game:write', 'game_event:read', 'calendar_event:read'])]
    #[ORM\ManyToOne(targetEntity: GameType::class, inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: false)]
    private GameType $gameType;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Goal::class)]
    private Collection $goals;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: GameEvent::class)]
    private Collection $gameEvents;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Substitution::class)]
    private Collection $substitutions;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $homeScore = null;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $awayScore = null;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\Column(type: 'boolean')]
    private bool $isFinished = false;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\ManyToOne(targetEntity: Location::class)]
    private ?Location $location = null;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: CalendarEvent::class, inversedBy: "games", cascade: ['persist', 'remove'])]
    private ?CalendarEvent $calendarEvent;

    public function __construct()
    {
        $this->goals = new ArrayCollection();
        $this->gameEvents = new ArrayCollection();
        $this->substitutions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCalendarEvent(): ?CalendarEvent
    {
        return $this->calendarEvent;
    }

    public function setCalendarEvent(?CalendarEvent $calendarEvent): self
    {
        $this->calendarEvent = $calendarEvent;
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

    public function getGameEvents(): Collection
    {
        return $this->gameEvents;
    }

    public function addGameEvent(GameEvent $gameEvent): self
    {
        if (!$this->gameEvents->contains($gameEvent)) {
            $this->gameEvents[] = $gameEvent;
            $gameEvent->setGame($this);
        }
        return $this;
    }

    public function removeGameEvent(GameEvent $gameEvent): self
    {
        if ($this->gameEvents->contains($gameEvent)) {
            $this->gameEvents->removeElement($gameEvent);
            // set the owning side to null (unless already changed)
            if ($gameEvent->getGame() === $this) {
                $gameEvent->setGame(null);
            }
        }
        return $this;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getGoals(): Collection
    {
        return $this->goals;
    }

    public function addGoal(Goal $goal): self
    {
        if (!$this->goals->contains($goal)) {
            $this->goals[] = $goal;
            $goal->setGame($this);
        }
        return $this;
    }

    public function removeGoal(Goal $goal): self
    {
        if ($this->goals->removeElement($goal)) {
            // set the owning side to null (unless already changed)
            if ($goal->getGame() === $this) {
                $goal->setGame(null);
            }
        }
        return $this;
    }

    public function getSubstitutions(): Collection
    {
        return $this->substitutions;
    }

    public function addSubstitution(Substitution $substitution): self
    {
        if (!$this->substitutions->contains($substitution)) {
            $this->substitutions[] = $substitution;
            $substitution->setGame($this);
        }
        return $this;
    }

    public function removeSubstitution(Substitution $substitution): self
    {
        if ($this->substitutions->removeElement($substitution)) {
            // set the owning side to null (unless already changed)
            if ($substitution->getGame() === $this) {
                $substitution->setGame(null);
            }
        }
        return $this;
    }

    public function getGameType(): GameType
    {
        return $this->gameType;
    }

    public function setGameType(?GameType $gameType): self
    {
        $this->gameType = $gameType;
        return $this;
    }

    public function getHomeScore(): ?int
    {
        return $this->homeScore;
    }

    public function setHomeScore(?int $homeScore): self
    {
        $this->homeScore = $homeScore;
        return $this;
    }

    public function getAwayScore(): ?int
    {
        return $this->awayScore;
    }

    public function setAwayScore(?int $awayScore): self
    {
        $this->awayScore = $awayScore;
        return $this;
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function setIsFinished(bool $isFinished): self
    {
        $this->isFinished = $isFinished;
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

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateScores(): void
    {
        $homeScore = 0;
        $awayScore = 0;

        foreach ($this->goals as $goal) {
            if ($goal->getTeam() === $this->homeTeam) {
                if (!$goal->isOwnGoal()) {
                    $homeScore++;
                } else {
                    $awayScore++;
                }
            } else {
                if (!$goal->isOwnGoal()) {
                    $awayScore++;
                } else {
                    $homeScore++;
                }
            }
        }

        $this->homeScore = $homeScore;
        $this->awayScore = $awayScore;
    }

    public function __toString() {
        return $this->getHomeTeam()?->getName() . ' : ' . $this->getAwayTeam()?->getName();
    }
}
