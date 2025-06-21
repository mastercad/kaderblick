<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['team:read', 'club:read', 'player:read', 'player_team_assignment:read', 'game_event:read', 'coach:read', 'game:read', 'calendar_event:read'])]
    private int $id;

    #[ORM\Column(length: 100)]
    #[Groups(['team:read', 'team:write', 'player:read', 'club:read', 'player_team_assignment:read', 'game_event:read', 'coach:read', 'game:read', 'calendar_event:read'])]
    private string $name;

    #[ORM\ManyToOne(targetEntity: AgeGroup::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['team:read', 'team:write', 'club:read', 'player_team_assignment:read', 'game_event:read', 'coach:read', 'game:read'])]
    private AgeGroup $ageGroup;

    #[ORM\ManyToOne(targetEntity: League::class, inversedBy: 'teams', cascade: ['persist'])]
    private ?League $league = null;

    #[Groups(['team:read'])]
    #[ORM\OneToMany(targetEntity: GameEvent::class, mappedBy: 'team')]
    private Collection $gameEvents;

    #[ORM\OneToMany(targetEntity: PlayerTeamAssignment::class, mappedBy: 'team')]
    private Collection $playerTeamAssignments;

    #[Groups(['team:read'])]
    #[ORM\OneToMany(targetEntity: CoachTeamAssignment::class, mappedBy: 'team')]
    private Collection $coachTeamAssignments;

    #[Groups(['team:read', 'team:write'])]
    #[ORM\ManyToMany(targetEntity: Club::class, inversedBy: 'teams')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $clubs;

    public function __construct()
    {
        $this->playerTeamAssignments = new ArrayCollection();
        $this->coachTeamAssignments = new ArrayCollection();
        $this->gameEvents = new ArrayCollection();
        $this->clubs = new ArrayCollection();
    }

    public function getId(): int
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

    public function getAgeGroup(): AgeGroup
    {
        return $this->ageGroup;
    }

    public function setAgeGroup(AgeGroup $ageGroup): self
    {
        $this->ageGroup = $ageGroup;
        return $this;
    }

    public function getLeague(): ?League
    {
        return $this->league;
    }

    public function setLeague(?League $league): self
    {
        $this->league = $league;
        return $this;
    }

    public function addGameEvent(GameEvent $gameEvent): self
    {
        if (!$this->gameEvents->contains($gameEvent)) {
            $this->gameEvents[] = $gameEvent;
            $gameEvent->setTeam($this);
        }
        return $this;
    }

    public function removeGameEvent(GameEvent $gameEvent): self
    {
        if ($this->gameEvents->removeElement($gameEvent)) {
            if ($gameEvent->getTeam() === $this) {
                $gameEvent->setTeam(null);
            }
        }
        return $this;
    }

    public function getPlayerTeamAssignments(): Collection
    {
        return $this->playerTeamAssignments;
    }

    public function addPlayerTeamAssignment(PlayerTeamAssignment $assignment): self
    {
        if (!$this->playerTeamAssignments->contains($assignment)) {
            $this->playerTeamAssignments[] = $assignment;
            $assignment->setTeam($this);
        }
        return $this;
    }

    public function removePlayerTeamAssignment(PlayerTeamAssignment $assignment): self
    {
        if ($this->playerTeamAssignments->removeElement($assignment)) {
            if ($assignment->getTeam() === $this) {
                $assignment->setTeam(null);
            }
        }
        return $this;
    }

    public function getCoachTeamAssignments(): Collection
    {
        return $this->coachTeamAssignments;
    }

    public function addCoachTeamAssignment(CoachTeamAssignment $assignment): self
    {
        if (!$this->coachTeamAssignments->contains($assignment)) {
            $this->coachTeamAssignments[] = $assignment;
            $assignment->setTeam($this);
        }
        return $this;
    }

    public function removeCoachTeamAssignment(CoachTeamAssignment $assignment): self
    {
        if ($this->coachTeamAssignments->removeElement($assignment)) {
            if ($assignment->getTeam() === $this) {
                $assignment->setTeam(null);
            }
        }
        return $this;
    }

    public function getCurrentPlayers(): array
    {
        $now = new DateTime();
        return $this->playerTeamAssignments
            ->filter(function(PlayerTeamAssignment $assignment) use ($now) {
                return $assignment->getStartDate() <= $now && 
                    ($assignment->getEndDate() === null || $assignment->getEndDate() >= $now);
            })
            ->map(function(PlayerTeamAssignment $assignment) {
                return $assignment->getPlayer();
            })
            ->toArray();
    }

    public function getClubs(): Collection
    {
        return $this->clubs;
    }

    public function setClubs(?Collection $clubs): self
    {
        $this->clubs = $clubs;
        return $this;
    }

    public function addClub(Club $club): self
    {
        if (!$this->clubs->contains($club)) {
            $this->clubs[] = $club;
            $club->addTeam($this);
        }
        return $this;
    }

    public function removeClub(Club $club): self
    {
        if ($this->clubs->removeElement($club)) {
            // set the owning side to null (unless already changed)
            if ($club->getTeams()->contains($this)) {
                $club->removeTeam($this);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name . ' ' . $this->getAgeGroup()?->getName() ?? 'Unbenanntes Team';
    }
}
