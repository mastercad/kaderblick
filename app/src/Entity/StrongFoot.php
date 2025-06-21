<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\StrongFootRepository;

#[ORM\Entity(repositoryClass: StrongFootRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'Dieser Code existiert bereits.')]
class StrongFoot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['strong_foot:read', 'player:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Groups(['strong_foot:read', 'strong_foot:write'])]
    private string $code;

    #[ORM\Column(length: 50)]
    #[Groups(['strong_foot:read', 'strong_foot:write', 'player:read'])]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'strongFoot', targetEntity: Player::class, fetch: 'EXTRA_LAZY')]
    private Collection $players;

    public function __construct()
    {
        $this->players = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
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

    public function __toString(): string
    {
        return $this->name;
    }
}
