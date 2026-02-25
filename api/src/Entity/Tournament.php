<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'tournaments')]
class Tournament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null; // @phpstan-ignore property.unusedType

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 50)]
    private string $type;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $startAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $endAt = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $settings = null;

    /** @var Collection<int, TournamentTeam> */
    #[Groups(['tournament:read'])]
    #[ORM\OneToMany(mappedBy: 'tournament', targetEntity: TournamentTeam::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $teams;

    /** @var Collection<int, TournamentMatch> */
    #[Groups(['tournament:read', 'calendar_event:read'])]
    #[ORM\OneToMany(mappedBy: 'tournament', targetEntity: TournamentMatch::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $matches;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    #[Groups(['tournament:read', 'calendar_event:read'])]
    #[ORM\OneToOne(targetEntity: CalendarEvent::class, inversedBy: 'tournament', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'calendar_event_id', referencedColumnName: 'id', nullable: false, unique: true, onDelete: 'CASCADE')]
    private CalendarEvent $calendarEvent;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->matches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStartAt(): ?DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    /** @return array<string, mixed>|null */
    public function getSettings(): ?array
    {
        return $this->settings;
    }

    /** @param array<string, mixed>|null $settings */
    public function setSettings(?array $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    /** @return Collection<int, TournamentTeam> */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(TournamentTeam $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->setTournament($this);
        }

        return $this;
    }

    public function removeTeam(TournamentTeam $team): self
    {
        if ($this->teams->removeElement($team)) {
            if ($team->getTournament() === $this) {
                $team->setTournament(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, TournamentMatch> */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    public function addMatch(TournamentMatch $match): self
    {
        if (!$this->matches->contains($match)) {
            $this->matches->add($match);
            $match->setTournament($this);
        }

        return $this;
    }

    public function removeMatch(TournamentMatch $match): self
    {
        if ($this->matches->removeElement($match)) {
            if ($match->getTournament() === $this) {
                $match->setTournament(null);
            }
        }

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCalendarEvent(): ?CalendarEvent
    {
        return $this->calendarEvent ?? null;
    }

    public function setCalendarEvent(CalendarEvent $calendarEvent): self
    {
        $this->calendarEvent = $calendarEvent;

        return $this;
    }
}
