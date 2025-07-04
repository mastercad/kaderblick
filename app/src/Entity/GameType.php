<?php

namespace App\Entity;

use App\Repository\GameTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: GameTypeRepository::class)]
class GameType
{
    #[Groups(['game_type:read', 'game_type:write', 'game:read', 'calendar_event:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['game_type:read', 'game_type:write', 'game:read', 'calendar_event:read'])]
    #[ORM\Column(length: 100, unique: true)]
    private string $name;

    #[Groups(['game_type:read', 'game_type:write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Groups(['game_type:read', 'game_type:write'])]
    #[ORM\OneToMany(mappedBy: 'gameType', targetEntity: Game::class)]
    private Collection $games;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games[] = $game;
            $game->setGameType($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->removeElement($game)) {
            // set the owning side to null (unless already changed)
            if ($game->getGameType() === $this) {
                $game->setGameType(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
