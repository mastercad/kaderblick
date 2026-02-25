<?php

namespace App\Entity;

use App\Repository\SurfaceTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SurfaceTypeRepository::class)]
#[ORM\Table(name: 'surface_types')]
#[ORM\UniqueConstraint(name: 'uniq_surface_type_name', columns: ['name'])]
#[UniqueEntity(fields: ['name'], message: 'Dieser Name ist bereits vergeben.')]
class SurfaceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['surface_type:read', 'location:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['surface_type:read', 'surface_type:write', 'location:read'])]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['surface_type:read', 'surface_type:write'])]
    private ?string $description = null;

    /** @var Collection<int, Location> */
    #[ORM\OneToMany(mappedBy: 'surfaceType', targetEntity: Location::class)]
    private Collection $locations;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /** @return Collection<int, Location> */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
