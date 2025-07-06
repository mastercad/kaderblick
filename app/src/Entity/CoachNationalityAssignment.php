<?php

namespace App\Entity;

use App\Repository\CoachNationalityAssignmentRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CoachNationalityAssignmentRepository::class)]
class CoachNationalityAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Coach::class, inversedBy: 'coachNationalityAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coach $coach = null;

    #[ORM\ManyToOne(targetEntity: Nationality::class, inversedBy: 'coachNationalityAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Nationality $nationality = null;

    #[Groups(['coach_nationality_assignment:read', 'coach_nationality_assignment:write'])]
    #[ORM\Column(type: 'date')]
    private DateTimeInterface $startDate;

    #[Groups(['coach_nationality_assignment:read', 'coach_nationality_assignment:write'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $active = true; // Falls mal aus historischen Gründen etwas deaktiviert werden soll

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoach(): ?Coach
    {
        return $this->coach;
    }

    public function setCoach(?Coach $coach): self
    {
        $this->coach = $coach;

        return $this;
    }

    public function getNationality(): ?Nationality
    {
        return $this->nationality;
    }

    public function setNationality(?Nationality $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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
}
