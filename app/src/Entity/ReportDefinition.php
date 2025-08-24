<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'report_definitions')]
class ReportDefinition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /** @var array<string, mixed> $config */
    #[ORM\Column(type: 'json')]
    private array $config = [];

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isTemplate = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    /** @var Collection<int, DashboardWidget> */
    #[ORM\OneToMany(mappedBy: 'reportDefinition', targetEntity: DashboardWidget::class, fetch: 'EXTRA_LAZY')]
    private Collection $widgets;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->name = '';
        $this->description = null;
        $this->config = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function isTemplate(): bool
    {
        return $this->isTemplate;
    }

    public function setIsTemplate(bool $isTemplate): self
    {
        $this->isTemplate = $isTemplate;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, DashboardWidget>
     */
    public function getWidgets(): Collection
    {
        return $this->widgets;
    }

    public function addDashboardWidget(DashboardWidget $dashboardWidget): self
    {
        if (!$this->widgets->contains($dashboardWidget)) {
            $this->widgets[] = $dashboardWidget;
            $dashboardWidget->setReportDefinition($this);
        }

        return $this;
    }

    public function removeDashboardWidget(DashboardWidget $dashboardWidget): self
    {
        if ($this->widgets->removeElement($dashboardWidget)) {
            // set the owning side to null (unless already changed)
            if ($dashboardWidget->getReportDefinition() === $this) {
                $dashboardWidget->setReportDefinition(null);
            }
        }

        return $this;
    }
}
