<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\SubstitutionReasonRepository;

/**
 * tactical	            Taktische Gründe (z. B. Systemwechsel, Zeitspiel, frischer Spieler)
 * injury	            Spieler verletzt sich oder ist angeschlagen
 * performance	        Schlechte Leistung, zu viele Fehler
 * resting / rotation	Schonung für andere Spiele
 * card_risk	        Risiko auf Gelb-Rot oder Platzverweis
 * debut	            Einwechslung für einen Jugend-/Debütspieler
 * comeback	            Rückkehr nach Verletzung
 * time_wasting	        Auswechslung in der Nachspielzeit zum Zeitspiel
 * fan_favor / farewell	Spieler bekommt Applaus beim Abschied
 */

#[ORM\Entity(repositoryClass: SubstitutionReasonRepository::class)]
class SubstitutionReason
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['substitution_reason:read', 'substitution_reason:write', 'game:read', 'game_event:read'])]
    private ?int $id = null;

    #[Groups(['substitution_reason:read', 'substitution_reason:write', 'game:read', 'game_event:read'])]
    #[ORM\Column(length: 255)]
    private ?string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    #[Groups(['substitution_reason:read'])]
    #[ORM\OneToMany(targetEntity: Substitution::class, mappedBy: 'substitutionReason')]
    private Collection $substitutions;

    public function __construct()
    {
        $this->substitutions = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getSubstitutions(): Collection
    {
        return $this->substitutions;
    }

    public function addSubstitution(Substitution $substitution): self
    {
        if (!$this->substitutions->contains($substitution)) {
            $this->substitutions[] = $substitution;
            $substitution->setSubstitutionReason($this);
        }
        return $this;
    }

    public function removeSubstitution(Substitution $substitution): self
    {
        if ($this->substitutions->removeElement($substitution)) {
            if ($substitution->getSubstitutionReason() === $this) {
                $substitution->setSubstitutionReason(null);
            }
        }
        return $this;
    }

    public function __toString()
    {
        return $this->name ?? "UNKNOWN SUSTITUTION REASON";
    }
}
