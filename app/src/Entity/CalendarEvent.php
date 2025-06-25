<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\CalendarEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CalendarEventRepository::class)]
#[ORM\Table(name: 'calendar_events')]
class CalendarEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['calendar_event:read', 'game:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['calendar_event:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['calendar_event:read'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['calendar_event:read'])]
    private ?DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['calendar_event:read'])]
    private ?DateTimeInterface $endDate = null;

    #[ORM\ManyToOne]
    #[Groups(['calendar_event:read'])]
    private ?CalendarEventType $calendarEventType = null;

    #[ORM\ManyToOne]
    #[Groups(['calendar_event:read'])]
    private ?Location $location = null;

    #[ORM\ManyToOne]
    #[Groups(['calendar_event:read'])]
    private ?CalendarEventType $type = null;

    #[Groups(['calendar_event:read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: "calendarEvent", cascade: ['persist', 'remove'])]
    private Collection $games;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }


    #[ORM\Column]
    private bool $notificationSent = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
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

    public function getEventType(): ?CalendarEventType
    {
        return $this->calendarEventType;
    }

    public function setEventType(?CalendarEventType $calendarEventType): self
    {
        $this->calendarEventType = $calendarEventType;
        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function isNotificationSent(): bool
    {
        return $this->notificationSent;
    }

    public function setNotificationSent(bool $notificationSent): self
    {
        $this->notificationSent = $notificationSent;
        return $this;
    }

    public function getType(): ?CalendarEventType
    {
        return $this->type;
    }

    public function setType(?CalendarEventType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }

    public function setGame(Collection $games): self
    {
        $this->games = $games;
        return $this;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games[] = $game;
            $game->setCalendarEvent($this);
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->contains($game)) {
            $this->games->removeElement($game);
            if ($game->getCalendarEvent() === $this) {
                $game->setCalendarEvent(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? "UNKNOWN CALENDAR EVENT";
    }
}
