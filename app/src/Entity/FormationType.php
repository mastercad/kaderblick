<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'formation_types')]
class FormationType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $backgroundPath = 'default-field.jpg';

    /** @var array<string, mixed>|null $defaultPositions */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $defaultPositions = null;

    /** @var Collection<int, Formation> $formations */
    #[ORM\OneToMany(mappedBy: 'formationType', targetEntity: Formation::class)]
    private Collection $formations;

    public function __construct()
    {
        $this->formations = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getBackgroundPath(): string
    {
        return $this->backgroundPath;
    }

    public function setBackgroundPath(string $backgroundPath): self
    {
        $this->backgroundPath = $backgroundPath;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDefaultPositions(): ?array
    {
        return $this->defaultPositions;
    }

    /**
     * @param array<string, mixed>|null $defaultPositions
     *
     * @return $this
     */
    public function setDefaultPositions(?array $defaultPositions): self
    {
        $this->defaultPositions = $defaultPositions;

        return $this;
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    public function addFormation(Formation $formation): self
    {
        if (!$this->formations->contains($formation)) {
            $this->formations->add($formation);
            $formation->setFormationType($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): self
    {
        if ($this->formations->removeElement($formation)) {
            if ($formation->getFormationType() === $this) {
                $formation->setFormationType(null);
            }
        }

        return $this;
    }
}
