<?php

namespace App\Entity;

use App\Repository\NationalityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: NationalityRepository::class)]
#[ORM\Table(name: 'nationalities')]
#[ORM\UniqueConstraint(name: 'uniq_nationality_iso_code', columns: ['iso_code'])]
class Nationality
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['nationality:read', 'nationality:write', 'coach:read', 'player:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['nationality:read', 'nationality:write', 'coach:read', 'player:read'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 3, unique: true)]
    #[Groups(['nationality:read', 'nationality:write'])]
    private string $isoCode; // z. B. "DEU", "FRA"

    /** @var Collection<int, PlayerNationalityAssignment> */
    #[ORM\OneToMany(mappedBy: 'nationality', targetEntity: PlayerNationalityAssignment::class)]
    #[Groups(['nationality:read', 'nationality:write'])]
    private Collection $playerNationalityAssignments;

    /** @var Collection<int, CoachNationalityAssignment> */
    #[ORM\OneToMany(mappedBy: 'nationality', targetEntity: CoachNationalityAssignment::class)]
    #[Groups(['nationality:read', 'nationality:write'])]
    private Collection $coachNationalityAssignments;

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

    public function getIsoCode(): string
    {
        return $this->isoCode;
    }

    public function setIsoCode(string $isoCode): self
    {
        $this->isoCode = $isoCode;

        return $this;
    }

    /** @return Collection<int, PlayerNationalityAssignment> */
    public function getPlayerNationalityAssignments(): Collection
    {
        return $this->playerNationalityAssignments;
    }

    public function addPlayerAssignment(PlayerNationalityAssignment $playerNationaltyAssignment): self
    {
        if (!$this->playerNationalityAssignments->contains($playerNationaltyAssignment)) {
            $this->playerNationalityAssignments[] = $playerNationaltyAssignment;
            $playerNationaltyAssignment->setNationality($this);
        }

        return $this;
    }

    public function removePlayerAssignment(PlayerNationalityAssignment $playerNationaltyAssignment): self
    {
        if ($this->playerNationalityAssignments->removeElement($playerNationaltyAssignment)) {
            // set the owning side to null (unless already changed)
            if ($playerNationaltyAssignment->getNationality() === $this) {
                $playerNationaltyAssignment->setNationality(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, CoachNationalityAssignment> */
    public function getCoachNationalityAssignments(): Collection
    {
        return $this->coachNationalityAssignments;
    }

    public function addCoachAssignment(CoachNationalityAssignment $coachNationaltyAssignment): self
    {
        if (!$this->coachNationalityAssignments->contains($coachNationaltyAssignment)) {
            $this->coachNationalityAssignments[] = $coachNationaltyAssignment;
            $coachNationaltyAssignment->setNationality($this);
        }

        return $this;
    }

    public function removeCoachAssignment(CoachNationalityAssignment $coachNationaltyAssignment): self
    {
        if ($this->coachNationalityAssignments->removeElement($coachNationaltyAssignment)) {
            // set the owning side to null (unless already changed)
            if ($coachNationaltyAssignment->getNationality() === $this) {
                $coachNationaltyAssignment->setNationality(null);
            }
        }

        return $this;
    }
}
