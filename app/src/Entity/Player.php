<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\Table(name: 'players')]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['player:read', 'player:write', 'team:read', 'goal:read', 'player_team_assignment:read', 'player_club_assignment:read', 'club:read', 'game_event:read'])]
    private ?int $id = null;

    #[Groups(['player:read', 'player:write', 'team:read', 'goal:read', 'player_team_assignment:read', 'player_club_assignment:read', 'club:read', 'game_event:read'])]
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Der Vorname darf nicht leer sein.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Der Vorname muss mindestens {{ limit }} Zeichen lang sein.',
        maxMessage: 'Der Vorname darf nicht länger als {{ limit }} Zeichen sein.'
    )]
    private string $firstName;

    #[Groups(['player:read', 'player:write', 'team:read', 'goal:read', 'player_team_assignment:read', 'player_club_assignment:read', 'club:read', 'game_event:read'])]
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Der Nachname darf nicht leer sein.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Der Nachname muss mindestens {{ limit }} Zeichen lang sein.',
        maxMessage: 'Der Nachname darf nicht länger als {{ limit }} Zeichen sein.'
    )]
    private string $lastName;

    #[Groups(['player:read', 'player:write'])]
    #[Context(normalizationContext: ['datetime_format' => 'd.m.Y'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $birthdate = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Range(
        min: 50,
        max: 250,
        notInRangeMessage: 'Die Größe muss zwischen {{ min }} und {{ max }} cm liegen.'
    )]
    private ?int $height = null; // in cm
    
    #[Groups(['player:read', 'player:write'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Range(
        min: 20,
        max: 150,
        notInRangeMessage: 'Das Gewicht muss zwischen {{ min }} und {{ max }} kg liegen.'
    )]
    private ?int $weight = null; // in kg
    
    #[Groups(['player:read', 'player:write'])]
    #[ORM\ManyToOne(targetEntity: StrongFoot::class, inversedBy: 'players')]
    #[ORM\JoinColumn(nullable: true)]
    private ?StrongFoot $strongFoot = null;
    
    #[Groups(['player:read', 'player:write'])]
    #[ORM\ManyToOne(targetEntity: Position::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Position $mainPosition;
    
    #[Groups(['player:read', 'player:write'])]
    #[ORM\ManyToMany(targetEntity: Position::class)]
    #[ORM\JoinTable(name: 'player_alternative_positions')]
    private Collection $alternativePositions;

    #[Groups(['player:read'])]
    #[ORM\OneToMany(targetEntity: Goal::class, mappedBy: 'scorer')]
    private Collection $goals;

    #[Groups(['player:read'])]
    #[ORM\OneToMany(targetEntity: Goal::class, mappedBy: 'assistBy')]
    private Collection $assistsProvided;

    #[Groups(['player:read'])]
    #[ORM\OneToMany(targetEntity: GameEvent::class, mappedBy: 'player')]
    private Collection $gameEvents;

    #[Groups(['player:read'])]
    #[ORM\OneToMany(targetEntity: PlayerTeamAssignment::class, mappedBy: 'player', cascade: ['persist'])]
    private Collection $playerTeamAssignments;

    #[Groups(['player:read'])]
    #[ORM\OneToMany(targetEntity: PlayerClubAssignment::class, mappedBy: 'player', cascade: ['persist'])]
    private Collection $playerClubAssignments;

    #[Groups(['player:read'])]
    #[ORM\OneToMany(targetEntity: PlayerNationalityAssignment::class, mappedBy: 'player')]
    private Collection $playerNationalityAssignments;

    #[Groups(['player:read', 'player:write'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email(message: 'Die E-Mail-Adresse {{ value }} ist nicht gültig.')]
    private ?string $email = null;

    public function __construct()
    {
        $this->goals = new ArrayCollection();
        $this->gameEvents = new ArrayCollection();
        $this->assistsProvided = new ArrayCollection();
        $this->alternativePositions = new ArrayCollection();
        $this->playerTeamAssignments = new ArrayCollection();
        $this->playerClubAssignments = new ArrayCollection();
        $this->playerNationalityAssignments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getBirthdate(): DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;
        return $this;
    }

    public function getPlayerTeamAssignments(): Collection
    {
        return $this->playerTeamAssignments;
    }

    public function addPlayerTeamAssignment(PlayerTeamAssignment $assignment): self
    {
        if (!$this->playerTeamAssignments->contains($assignment)) {
            $this->playerTeamAssignments->add($assignment);
            $assignment->setPlayer($this);
        }
        return $this;
    }

    public function removePlayerTeamAssignment(PlayerTeamAssignment $playerTeamAssignment): self
    {
        if ($this->playerTeamAssignments->removeElement($playerTeamAssignment)) {
            if ($playerTeamAssignment->getPlayer() === $this) {
                $playerTeamAssignment->setPlayer(null);
            }
        }
        return $this;
    }

    public function getPlayerClubAssignments(): Collection
    {
        return $this->playerClubAssignments;
    }

    public function addPlayerClubAssignment(PlayerClubAssignment $playerClubAssignment): self
    {
        if (!$this->playerClubAssignments->contains($playerClubAssignment)) {
            $this->playerClubAssignments->add($playerClubAssignment);
            $playerClubAssignment->setPlayer($this);
        }
        return $this;
    }

    public function removePlayerClubAssignment(PlayerClubAssignment $playerClubAssignment): self
    {
        if ($this->playerClubAssignments->removeElement($playerClubAssignment)) {
            if ($playerClubAssignment->getPlayer() === $this) {
                $playerClubAssignment->setPlayer(null);
            }
        }
        return $this;
    }

    public function addGoalScored(Goal $goal): self
    {
        if (!$this->goals->contains($goal)) {
            $this->goals->add($goal);
            $goal->setScorer($this);
        }
        return $this;
    }

    public function removeGoal(Goal $goal): self
    {
        if ($this->goals->removeElement($goal)) {
            if ($goal->getScorer() === $this) {
                $goal->setScorer(null);
            }
        }
        return $this;
    }

    public function getAssistsProvided(): Collection
    {
        return $this->assistsProvided;
    }

    public function addAssistProvided(Goal $goal): self
    {
        if (!$this->assistsProvided->contains($goal)) {
            $this->assistsProvided->add($goal);
            $goal->setAssistBy($this);
        }
        return $this;
    }

    public function removeAssistProvided(Goal $goal): self
    {
        if ($this->assistsProvided->removeElement($goal)) {
            if ($goal->getAssistBy() === $this) {
                $goal->setAssistBy(null);
            }
        }
        return $this;
    }

    public function getPlayerNationalityAssignments(): Collection
    {
        return $this->playerNationalityAssignments;
    }

    public function addPlayerNationalityAssignment(PlayerNationalityAssignment $playerNationalityAssignment): self
    {
        if (!$this->playerNationalityAssignments->contains($playerNationalityAssignment)) {
            $this->playerNationalityAssignments->add($playerNationalityAssignment);
            $playerNationalityAssignment->setPlayer($this);
        }
        return $this;
    }

    public function removePlayerNationalityAssignment(PlayerNationalityAssignment $playerNationalityAssignment): self
    {
        if ($this->playerNationalityAssignments->removeElement($playerNationalityAssignment)) {
            if ($playerNationalityAssignment->getPlayer() === $this) {
                $playerNationalityAssignment->setPlayer(null);
            }
        }
        return $this;
    }

    public function getStrongFoot(): ?StrongFoot
    {
        return $this->strongFoot;
    }

    public function setStrongFoot(?StrongFoot $strongFoot): self
    {
        $this->strongFoot = $strongFoot;
        return $this;
    }

    public function getMainPosition(): Position
    {
        return $this->mainPosition;
    }

    public function setMainPosition(Position $position): self
    {
        $this->mainPosition = $position;
        return $this;
    }

    public function getAlternativePositions(): Collection
    {
        return $this->alternativePositions;
    }

    public function addAlternativePosition(Position $position): self
    {
        if (!$this->alternativePositions->contains($position)) {
            $this->alternativePositions->add($position);
        }
        return $this;
    }

    public function removeAlternativePosition(Position $position): self
    {
        $this->alternativePositions->removeElement($position);
        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getGameEvents(): Collection
    {
        return $this->gameEvents;
    }

    public function addGameEvent(GameEvent $gameEvent): self
    {
        if (!$this->gameEvents->contains($gameEvent)) {
            $this->gameEvents->add($gameEvent);
            $gameEvent->setPlayer($this);
        }
        return $this;
    }

    public function removeGameEvent(GameEvent $gameEvent): self
    {
        if ($this->gameEvents->removeElement($gameEvent)) {
            if ($gameEvent->getPlayer() === $this) {
                $gameEvent->setPlayer(null);
            }
        }
        return $this;
    }

    public function getEventsByType(string $eventTypeCode): array
    {
        return $this->gameEvents
            ->filter(function(GameEvent $event) use ($eventTypeCode) {
                return $event->getGameEventType()->getCode() === $eventTypeCode;
            })
            ->toArray();
    }

    public function getYellowCards(): array
    {
        return $this->getEventsByType('yellow_card');
    }

    public function getRedCards(): array
    {
        return $this->getEventsByType('red_card');
    }

    public function getGoals(): array
    {
        return $this->getEventsByType('goal');
    }

    public function getAge(): ?int
    {
        if (!$this->birthdate) {
            return null;
        }

        return $this->birthdate->diff(new DateTime())->y;
    }

    public function getCurrentTeam(): ?Team
    {
        $now = new DateTime();
        
        foreach ($this->playerTeamAssignments as $assignment) {
            if ($assignment->getStartDate() <= $now && 
                ($assignment->getEndDate() === null || $assignment->getEndDate() >= $now)) {
                return $assignment->getTeam();
            }
        }
        
        return null;
    }

    public function getCurrentClub(): ?Club
    {
        $now = new DateTime();
        
        foreach ($this->playerClubAssignments as $assignment) {
            if ($assignment->getStartDate() <= $now && 
                ($assignment->getEndDate() === null || $assignment->getEndDate() >= $now)) {
                return $assignment->getClub();
            }
        }
        
        return null;
    }

    public function getCurrentNationalities(): array
    {
        $now = new DateTime();
        $nationalities = [];
        
        foreach ($this->playerNationalityAssignments as $assignment) {
            if ($assignment->isActive() && 
                $assignment->getStartDate() <= $now && 
                ($assignment->getEndDate() === null || $assignment->getEndDate() >= $now)) {
                $nationalities[] = $assignment->getNationality();
            }
        }
        
        return $nationalities;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function __toString()
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
