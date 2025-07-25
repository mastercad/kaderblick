<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\Table(name: 'games')]
#[ORM\HasLifecycleCallbacks]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['game:read', 'game:write', 'team:read', 'player:read', 'game_event:read', 'calendar_event:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private int $id;

    #[Groups(['game:read', 'game:write', 'game_event:read', 'calendar_event:read'])]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    private ?Team $homeTeam = null;

    #[Groups(['game:read', 'game:write', 'game_event:read', 'calendar_event:read'])]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    private ?Team $awayTeam = null;

    #[Groups(['game:read', 'game:write', 'game_event:read', 'calendar_event:read'])]
    #[ORM\ManyToOne(targetEntity: GameType::class, inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: false)]
    private GameType $gameType;

    /** @var Collection<int, Goal> */
    #[Groups(['game:read', 'game:write'])]
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Goal::class)]
    private Collection $goals;

    /** @var Collection<int, GameEvent> */
    #[Groups(['game:read', 'game:write'])]
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: GameEvent::class)]
    private Collection $gameEvents;

    /** @var Collection<int, Substitution> */
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
    #[ORM\OneToOne(targetEntity: CalendarEvent::class, inversedBy: 'game', cascade: ['persist', 'remove'])]
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

    /** @return Collection<int, GameEvent> */
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

    /** @return Collection<int, Goal> */
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

    /** @return Collection<int, Substitution> */
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

    public function __toString()
    {
        return $this->getHomeTeam()?->getName() . ' : ' . $this->getAwayTeam()?->getName();
    }
}
