<?php

namespace App\Entity;

use App\Repository\CoachLicenseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CoachLicenseRepository::class)]
#[ORM\Table(name: 'coach_licenses')]
class CoachLicense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['coach_license:read', 'coach_license:write', 'coach_license_assignment:read', 'coach:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['coach_license:read', 'coach_license:write', 'coach_license_assignment:read', 'coach:read'])]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['coach_license:read', 'coach_license:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['coach_license:read', 'coach_license:write'])]
    private ?string $countryCode = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['coach_license:read', 'coach_license:write'])]
    private bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
