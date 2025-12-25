<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'tasks')]
#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['task:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['task:read'])]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['task:read'])]
    private ?string $description = null;

    // Datum für einzelnes Vorkommen (Occurrence)
    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['task:read'])]
    private ?DateTimeInterface $assignedDate = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['task:read'])]
    private bool $isRecurring = false;

    // recurrenceMode: classic (Regel), per_match (an Spielplan gebunden)
    #[ORM\Column(type: 'string', length: 32, options: ['default' => 'classic'])]
    #[Groups(['task:read'])]
    private string $recurrenceMode = 'classic';

    // Recurrence-Rule als iCal-String oder JSON (z.B. {"freq":"WEEKLY","interval":2,"byday":["MO"]})
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['task:read'])]
    private ?string $recurrenceRule = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['task:read'])]
    private User $createdBy;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['task:read'])]
    private DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, TaskAssignment>
     */
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskAssignment::class, orphanRemoval: true)]
    #[Groups(['task:read'])]
    private Collection $assignments;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'task_rotation_users')]
    #[Groups(['task:read'])]
    private Collection $rotationUsers;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['task:read'])]
    private ?int $rotationCount = 1;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['task:read'])]
    private ?int $offsetDays = null;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
        $this->rotationUsers = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function isRecurring(): bool
    {
        return $this->isRecurring;
    }

    public function setIsRecurring(bool $isRecurring): self
    {
        $this->isRecurring = $isRecurring;

        return $this;
    }

    public function getRecurrenceMode(): string
    {
        return $this->recurrenceMode;
    }

    public function setRecurrenceMode(string $recurrenceMode): self
    {
        $this->recurrenceMode = $recurrenceMode;

        return $this;
    }

    public function getRecurrenceRule(): ?string
    {
        return $this->recurrenceRule;
    }

    public function setRecurrenceRule(?string $recurrenceRule): self
    {
        $this->recurrenceRule = $recurrenceRule;

        return $this;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    /**
     * @return Collection<int, TaskAssignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(TaskAssignment $assignment): self
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments[] = $assignment;
            $assignment->setTask($this);
        }

        return $this;
    }

    public function removeAssignment(TaskAssignment $assignment): self
    {
        if ($this->assignments->removeElement($assignment)) {
            // set the owning side to null (unless already changed)
            if ($assignment->getTask() === $this) {
                $assignment->setTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getRotationUsers(): Collection
    {
        return $this->rotationUsers;
    }

    /**
     * @param Collection<int, User> $users
     */
    public function setRotationUsers(Collection $users): self
    {
        $this->rotationUsers = $users;

        return $this;
    }

    public function addRotationUser(User $user): self
    {
        if (!$this->rotationUsers->contains($user)) {
            $this->rotationUsers[] = $user;
        }

        return $this;
    }

    public function removeRotationUser(User $user): self
    {
        $this->rotationUsers->removeElement($user);

        return $this;
    }

    public function getRotationCount(): ?int
    {
        return $this->rotationCount;
    }

    public function setRotationCount(?int $count): self
    {
        $this->rotationCount = $count;

        return $this;
    }

    public function getAssignedDate(): ?DateTimeInterface
    {
        return $this->assignedDate;
    }

    public function setOffsetDays(?int $offsetDays): self
    {
        $this->offsetDays = $offsetDays;

        return $this;
    }

    public function getOffsetDays(): ?int
    {
        return $this->offsetDays;
    }

    public function setAssignedDate(?DateTimeInterface $assignedDate): self
    {
        $this->assignedDate = $assignedDate;

        return $this;
    }
}
