<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ClubRepository::class)]
#[ORM\Table(name: 'clubs')]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['club:read', 'club:write', 'team:read', 'coach:read', 'coach_club_assignment:read', 'player_club_assignment:read', 'player:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['club:read', 'club:write', 'team:read', 'coach:read', 'coach_club_assignment:read', 'player_club_assignment:read', 'player:read'])]
    private string $name;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $shortName = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $abbreviation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stadiumName = null;

    /** @deprecated use Location */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    /** @deprecated use Location */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoUrl = null;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?Location $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $phone = null;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    /** @var Collection<int, PlayerClubAssignment> */
    #[Groups(['club:read'])]
    #[ORM\OneToMany(mappedBy: 'club', targetEntity: PlayerClubAssignment::class, cascade: ['persist', 'remove'])]
    private Collection $playerClubAssignments;

    /** @var Collection<int, CoachClubAssignment> */
    #[Groups(['club:read'])]
    #[ORM\OneToMany(mappedBy: 'club', targetEntity: CoachClubAssignment::class, cascade: ['persist', 'remove'])]
    private Collection $coachClubAssignments;

    /** @var Collection<int, Team> */
    #[ORM\ManyToMany(targetEntity: Team::class, mappedBy: 'clubs')]
    #[Groups(['club:read'])]
    private Collection $teams;

    public function __construct()
    {
        $this->playerClubAssignments = new ArrayCollection();
        $this->coachClubAssignments = new ArrayCollection();
        $this->teams = new ArrayCollection();
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

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?string $abbreviation): void
    {
        $this->abbreviation = $abbreviation;
    }

    public function getStadiumName(): ?string
    {
        return $this->stadiumName;
    }

    public function setStadiumName(?string $stadiumName): void
    {
        $this->stadiumName = $stadiumName;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function setLogoUrl(?string $logoUrl): void
    {
        $this->logoUrl = $logoUrl;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /** @return Collection<int, PlayerClubAssignment> */
    public function getPlayerClubAssignments(): Collection
    {
        return $this->playerClubAssignments;
    }

    /** @param Collection<int, PlayerClubAssignment> $playerClubAssignments */
    public function setPlayerClubAssignments(?Collection $playerClubAssignments): self
    {
        $this->playerClubAssignments = $playerClubAssignments ?? new ArrayCollection();

        return $this;
    }

    public function addPlayerClubAssignment(PlayerClubAssignment $playerClubAssignment): self
    {
        if (!$this->playerClubAssignments->contains($playerClubAssignment)) {
            $this->playerClubAssignments[] = $playerClubAssignment;
            $playerClubAssignment->setClub($this);
        }

        return $this;
    }

    public function removePlayerClubAssignment(PlayerClubAssignment $playerClubAssignment): self
    {
        if ($this->playerClubAssignments->removeElement($playerClubAssignment)) {
            // set the owning side to null (unless already changed)
            if ($playerClubAssignment->getClub() === $this) {
                $playerClubAssignment->setClub(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, CoachClubAssignment> */
    public function getCoachClubAssignments(): Collection
    {
        return $this->coachClubAssignments;
    }

    /** @param Collection<int, CoachClubAssignment> $coachClubAssignments */
    public function setCoachClubAssignments(?Collection $coachClubAssignments): self
    {
        $this->coachClubAssignments = $coachClubAssignments ?? new ArrayCollection();

        return $this;
    }

    public function addCoachClubAssignment(CoachClubAssignment $coachClubAssignment): self
    {
        if (!$this->coachClubAssignments->contains($coachClubAssignment)) {
            $this->coachClubAssignments[] = $coachClubAssignment;
            $coachClubAssignment->setClub($this);
        }

        return $this;
    }

    public function removeCoachClubAssignment(CoachClubAssignment $coachClubAssignment): self
    {
        if ($this->coachClubAssignments->removeElement($coachClubAssignment)) {
            // set the owning side to null (unless already changed)
            if ($coachClubAssignment->getClub() === $this) {
                $coachClubAssignment->setClub(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, Team> */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->addClub($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->removeElement($team)) {
            $team->removeClub($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->shortName ? sprintf('%s (%s)', $this->name, $this->shortName) : $this->name;
    }
}
