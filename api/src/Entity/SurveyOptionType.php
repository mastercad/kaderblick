<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SurveyOptionType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $typeKey;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    /**
     * @var Collection<int, SurveyOption>
     */
    #[ORM\OneToMany(targetEntity: SurveyOption::class, mappedBy: 'type')]
    private Collection $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeKey(): ?string
    {
        return $this->typeKey;
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

    public function setTypeKey(string $typeKey): self
    {
        $this->typeKey = $typeKey;

        return $this;
    }

    /**
     * @return Collection<int, SurveyOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }
}
