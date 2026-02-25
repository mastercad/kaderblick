<?php

namespace App\Entity;

use App\Repository\CoachLicenseAssignmentRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CoachLicenseAssignmentRepository::class)]
#[ORM\Table(name: 'coach_license_assignments')]
#[ORM\Index(name: 'idx_coach_license_assignment_coach_id', columns: ['coach_id'])]
#[ORM\Index(name: 'idx_coach_license_assignment_license_id', columns: ['license_id'])]
class CoachLicenseAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['coach_license_assignment:read', 'coach_license_assignment:write', 'coach:read', 'coach_license:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'license_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['coach_license_assignment:read', 'coach:read'])]
    private ?CoachLicense $license = null;

    #[ORM\ManyToOne(targetEntity: Coach::class, inversedBy: 'coachLicenseAssignments')]
    #[ORM\JoinColumn(name: 'coach_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['coach_license_assignment:read'])]
    private ?Coach $coach = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['coach_license_assignment:read'])]
    private ?DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['coach_license_assignment:read'])]
    private ?DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['coach_license_assignment:read'])]

    private bool $active = true;

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

    public function getLicense(): ?CoachLicense
    {
        return $this->license;
    }

    public function setLicense(?CoachLicense $license): self
    {
        $this->license = $license;

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

    public function __toString(): string
    {
        $end = $this->endDate ? ' bis ' . $this->endDate->format('d.m.Y') : '';

        return sprintf(
            '%s für %s (seit %s%s)',
            $this->license?->getName() ?? 'Unbekannter Lizenz',
            $this->coach?->getFullName() ?? 'Unbekannter Coach',
            $this->startDate?->format('d.m.Y') ?? 'unbekannt',
            $end
        );
    }
}
