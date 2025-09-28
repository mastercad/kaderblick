<?php

namespace App\Entity;

use App\Repository\TeamRideRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRideRepository::class)]
class TeamRide
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CalendarEvent::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?CalendarEvent $event = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $driver = null;

    #[ORM\Column(type: 'integer')]
    private int $seats;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    /**
     * @var Collection<int, TeamRidePassenger>
     */
    #[ORM\OneToMany(mappedBy: 'teamRide', targetEntity: TeamRidePassenger::class, cascade: ['persist', 'remove'])]
    private Collection $passengers;

    public function __construct()
    {
        $this->passengers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?CalendarEvent
    {
        return $this->event;
    }

    public function setEvent(?CalendarEvent $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getSeats(): int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return Collection<int, TeamRidePassenger>
     */
    public function getPassengers(): Collection
    {
        return $this->passengers;
    }

    public function addPassenger(TeamRidePassenger $passenger): self
    {
        if (!$this->passengers->contains($passenger)) {
            $this->passengers[] = $passenger;
            $passenger->setTeamRide($this);
        }

        return $this;
    }

    public function removePassenger(TeamRidePassenger $passenger): self
    {
        if ($this->passengers->removeElement($passenger)) {
            if ($passenger->getTeamRide() === $this) {
                $passenger->setTeamRide(null);
            }
        }

        return $this;
    }
}
