<?php

namespace App\Entity;

use App\Repository\TeamRidePassengerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRidePassengerRepository::class)]
class TeamRidePassenger
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TeamRide::class, inversedBy: 'passengers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TeamRide $teamRide = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeamRide(): ?TeamRide
    {
        return $this->teamRide;
    }

    public function setTeamRide(?TeamRide $teamRide): self
    {
        $this->teamRide = $teamRide;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
