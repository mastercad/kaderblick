<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\LocationRepository;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_location_name', columns: ['name'])]
#[UniqueEntity(fields: ['name'], message: 'Dieser Name ist bereits vergeben.')]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['location:read', 'location:write', 'team:read', 'club:read', 'game:read', 'event:read', 'calendar_event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['location:read', 'location:write', 'team:read', 'club:read', 'game:read', 'event:read', 'calendar_event:read'])]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['location:read', 'location:write'])]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['location:read', 'location:write'])]
    private ?string $city = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['location:read', 'location:write'])]
    private ?int $capacity = null;

    #[ORM\ManyToOne(targetEntity: SurfaceType::class, inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['location:read', 'location:write'])]
    private ?SurfaceType $surfaceType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['location:read', 'location:write'])]
    private ?bool $hasFloodlight = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['location:read', 'location:write'])]
    private ?string $facilities = null; // z.B. "Umkleiden, Duschen, Vereinsheim"

    #[ORM\Column(nullable: true)]
    #[Groups(['location:read', 'location:write'])]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['location:read', 'location:write'])]
    private ?float $longitude = null;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): self
    {
        $this->capacity = $capacity;
        return $this;
    }

    public function getSurfaceType(): ?SurfaceType
    {
        return $this->surfaceType;
    }

    public function setSurfaceType(?SurfaceType $surfaceType): self
    {
        $this->surfaceType = $surfaceType;
        return $this;
    }

    public function getHasFloodlight(): ?bool
    {
        return $this->hasFloodlight;
    }

    public function setHasFloodlight(?bool $hasFloodlight): self
    {
        $this->hasFloodlight = $hasFloodlight;
        return $this;
    }

    public function getFacilities(): ?string
    {
        return $this->facilities;
    }

    public function setFacilities(?string $facilities): self
    {
        $this->facilities = $facilities;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
