<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
#[ORM\Table(
    name: 'participations',
    indexes: [
        new ORM\Index(name: 'idx_participations_user_id', columns: ['user_id']),
        new ORM\Index(name: 'idx_participations_event_id', columns: ['event_id']),
        new ORM\Index(name: 'idx_participations_status_id', columns: ['status_id'])
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_user_event', columns: ['user_id', 'event_id'])
    ]
)]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['participation:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(
        name: 'user_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    #[Groups(['participation:read'])]
    #[Assert\NotNull(message: 'Ein Benutzer muss ausgewählt werden.')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: CalendarEvent::class)]
    #[ORM\JoinColumn(
        name: 'event_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    #[Groups(['participation:read'])]
    #[Assert\NotNull(message: 'Ein Event muss ausgewählt werden.')]
    private ?CalendarEvent $event = null;

    #[ORM\ManyToOne(targetEntity: ParticipationStatus::class, inversedBy: 'participations')]
    #[ORM\JoinColumn(
        name: 'status_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'RESTRICT'
    )]
    #[Groups(['participation:read'])]
    #[Assert\NotNull(message: 'Ein Status muss ausgewählt werden.')]
    private ?ParticipationStatus $status = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['participation:read', 'participation:write'])]
    private ?string $note = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['participation:read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['participation:read'])]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEvent(): ?CalendarEvent
    {
        return $this->event;
    }

    public function setEvent(?CalendarEvent $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getStatus(): ?ParticipationStatus
    {
        return $this->status;
    }

    public function setStatus(?ParticipationStatus $status): static
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;
        $this->updatedAt = new DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s (%s)',
            $this->user?->getEmail() ?? 'Unknown User',
            $this->event?->getTitle() ?? 'Unknown Event',
            $this->status?->getName() ?? 'Unknown Status'
        );
    }
}
