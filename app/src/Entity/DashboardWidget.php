<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dashboard_widgets')]
class DashboardWidget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ReportDefinition::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ReportDefinition $reportDefinition = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'widgets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private string $type;

    /** @var array<string, string> */
    #[ORM\Column]
    private array $config = [];

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private int $width = 6; // Bootstrap columns (1-12)

    #[ORM\Column]
    private bool $enabled = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getReportDefinition(): ?ReportDefinition
    {
        return $this->reportDefinition;
    }

    public function setReportDefinition(?ReportDefinition $reportDefinition): self
    {
        $this->reportDefinition = $reportDefinition;
        return $this;
    }
}
