<?php

namespace App\Entity;

use App\Repository\AgeGroupRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * id   name        max_age min_age active
 * 1    U13         13      0       true
 * 2    U15         15      14      true
 * 3    U17         17      16      true
 * 4    U19         19      18      true
 * 5    Senioren    NULL    20      true.
 */
#[ORM\Entity(repositoryClass: AgeGroupRepository::class)]
#[ORM\Table(name: 'age_groups')]
class AgeGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['age_group:read', 'team:read', 'club:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    #[Groups(['age_group:read', 'age_group:write', 'team:read', 'club:read'])]
    private string $code; // z.B. "A-Junioren", "U17"

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Groups(['age_group:read', 'age_group:write', 'team:read', 'club:read'])]
    private ?string $name = null; // z.B. "A-Junioren"

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Groups(['age_group:read', 'age_group:write', 'team:read'])]
    private string $englishName; // z.B. "U17"

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['age_group:read', 'age_group:write', 'team:read'])]
    private int $minAge; // z.B. 16

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['age_group:read', 'age_group:write', 'team:read'])]
    private int $maxAge; // z.B. 17

    #[ORM\Column(type: Types::STRING, length: 5)]
    #[Groups(['age_group:read', 'age_group:write', 'team:read'])]
    private string $referenceDate; // z.B. "01-01" (Monat-Tag, als String)

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['age_group:read', 'age_group:write', 'team:read'])]
    private ?string $description = null;

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

    public function getEnglishName(): string
    {
        return $this->englishName;
    }

    public function setEnglishName(string $englishName): self
    {
        $this->englishName = $englishName;

        return $this;
    }

    public function getMinAge(): int
    {
        return $this->minAge;
    }

    public function setMinAge(int $minAge): self
    {
        $this->minAge = $minAge;

        return $this;
    }

    public function getMaxAge(): int
    {
        return $this->maxAge;
    }

    public function setMaxAge(int $maxAge): self
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    public function getReferenceDate(): string
    {
        return $this->referenceDate;
    }

    public function setReferenceDate(string $referenceDate): self
    {
        $this->referenceDate = $referenceDate;

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

    public function __toString(): string
    {
        return $this->name;
    }
}
