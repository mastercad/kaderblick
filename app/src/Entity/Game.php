<?php

namespace App\Entity;

use App\Repository\GameRepository;
use App\Validator\DifferentTeams;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\Table(
    name: 'games',
    indexes: [
        new ORM\Index(name: 'idx_games_home_team_id', columns: ['home_team_id']),
        new ORM\Index(name: 'idx_games_away_team_id', columns: ['away_team_id']),
        new ORM\Index(name: 'idx_games_game_type_id', columns: ['game_type_id']),
        new ORM\Index(name: 'idx_games_location_id', columns: ['location_id'])
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_games_calendar_event_id', columns: ['calendar_event_id'])
    ]
)]
#[ORM\HasLifecycleCallbacks]
#[DifferentTeams]
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
    #[Assert\NotNull(message: 'Das Heimspiel-Team muss gesetzt sein.')]
    #[ORM\JoinColumn(
        name: 'home_team_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'RESTRICT'
    )]
    private ?Team $homeTeam = null;

    #[Groups(['game:read', 'game:write', 'game_event:read', 'calendar_event:read'])]
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[Assert\NotNull(message: 'Das Auswärts-Team muss gesetzt sein.')]
    #[ORM\JoinColumn(
        name: 'away_team_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'RESTRICT'
    )]
    private ?Team $awayTeam = null;

    #[Groups(['game:read', 'game:write', 'game_event:read', 'calendar_event:read'])]
    #[ORM\ManyToOne(targetEntity: GameType::class, inversedBy: 'games')]
    #[Assert\NotNull(message: 'Der Spieltyp muss gesetzt sein.')]
    #[ORM\JoinColumn(
        name: 'game_type_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
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
    #[ORM\JoinColumn(
        name: 'location_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?Location $location = null;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\OneToOne(targetEntity: CalendarEvent::class, inversedBy: 'game', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(
        name: 'calendar_event_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: 'CASCADE'
    )]
    private ?CalendarEvent $calendarEvent;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\Column(type: 'string', nullable: true, unique: true, name: 'fussball_de_id')]
    private ?string $fussballDeId = null;

    #[Groups(['game:read', 'game:write'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true, name: 'fussball_de_url')]
    private ?string $fussballDeUrl = null;

    /**
     * @var Collection<int, Video>
     */
    #[ORM\OneToMany(targetEntity: Video::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $videos;

    public function __construct()
    {
        $this->goals = new ArrayCollection();
        $this->gameEvents = new ArrayCollection();
        $this->substitutions = new ArrayCollection();
        $this->videos = new ArrayCollection();
    }

    public function getFussballDeId(): ?string
    {
        return $this->fussballDeId;
    }

    public function setFussballDeId(?string $fussballDeId): self
    {
        $this->fussballDeId = $fussballDeId;

        return $this;
    }

    public function getFussballDeUrl(): ?string
    {
        return $this->fussballDeUrl;
    }

    public function setFussballDeUrl(?string $fussballDeUrl): self
    {
        $this->fussballDeUrl = $fussballDeUrl;

        return $this;
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

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setGame($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): static
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getGame() === $this) {
                $video->setGame(null);
            }
        }

        return $this;
    }
}
