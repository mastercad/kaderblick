<?php

namespace App\Entity;

use App\Repository\TaskAssignmentRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'task_assignments')]
#[ORM\Entity(repositoryClass: TaskAssignmentRepository::class)]
class TaskAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Task $task = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'date')]
    private DateTimeInterface $assignedDate;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'offen';

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $substituteUser = null;

    #[ORM\OneToOne(targetEntity: CalendarEvent::class, cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?CalendarEvent $calendarEvent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;

        return $this;
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

    public function getAssignedDate(): DateTimeInterface
    {
        return $this->assignedDate;
    }

    public function setAssignedDate(DateTimeInterface $assignedDate): self
    {
        $this->assignedDate = $assignedDate;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSubstituteUser(): ?User
    {
        return $this->substituteUser;
    }

    public function setSubstituteUser(?User $substituteUser): self
    {
        $this->substituteUser = $substituteUser;

        return $this;
    }

    public function getCalendarEvent(): ?CalendarEvent
    {
        return $this->calendarEvent;
    }

    public function setCalendarEvent(?CalendarEvent $calendarEvent): self
    {
        $this->calendarEvent = $calendarEvent;

        return $this;
    }
}
