<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\PositionRepository;

#[ORM\Entity(repositoryClass: PositionRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_position_name', columns: ['name'])]
#[UniqueEntity(fields: ['name'], message: 'Diese Position existiert bereits.')]
class Position
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['position:read', 'player:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['position:read', 'position:write', 'player:read'])]
    private string $name;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['position:read', 'position:write'])]
    private ?string $shortName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['position:read', 'position:write'])]
    private ?string $description = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
