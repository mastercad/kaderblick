<?php

namespace App\Entity;

use App\Repository\StrongFootRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: StrongFootRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'Dieser Code existiert bereits.')]
class StrongFoot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['strong_foot:read', 'player:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Groups(['strong_foot:read', 'strong_foot:write'])]
    private string $code;

    #[ORM\Column(length: 50)]
    #[Groups(['strong_foot:read', 'strong_foot:write', 'player:read'])]
    private string $name;

    /** @var Collection<int, Player> */
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

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
