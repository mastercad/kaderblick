<?php

namespace App\Entity;

use App\Repository\CalendarEventRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CalendarEventRepository::class)]
#[ORM\Table(
    name: 'calendar_events',
    indexes: [
        new ORM\Index(name: 'idx_calendar_events_calendar_event_type_id', columns: ['calendar_event_type_id']),
        new ORM\Index(name: 'idx_calendar_events_location_id', columns: ['location_id'])
    ]
)]
class CalendarEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['calendar_event:read', 'game:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
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
    #[ORM\JoinColumn(
        name: 'calendar_event_type_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    #[Groups(['calendar_event:read'])]
    private ?CalendarEventType $calendarEventType = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(
        name: 'location_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    #[Groups(['calendar_event:read'])]
    private ?Location $location = null;

    #[ORM\OneToOne(targetEntity: WeatherData::class, mappedBy: 'calendarEvent', cascade: ['persist', 'remove'])]
    private ?WeatherData $weatherData = null;

    #[Groups(['calendar_event:read'])]
    #[ORM\OneToOne(targetEntity: Game::class, mappedBy: 'calendarEvent', cascade: ['persist', 'remove'])]
    private ?Game $game = null;

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

    public function getCalendarEventType(): ?CalendarEventType
    {
        return $this->calendarEventType;
    }
    
    public function getWeatherData(): ?WeatherData
    {
        return $this->weatherData;
    }

    public function setWeatherData(?WeatherData $weatherData): self
    {
        $this->weatherData = $weatherData;
        // set the owning side of the relation if necessary
        if ($weatherData && $weatherData->getCalendarEvent() !== $this) {
            $weatherData->setCalendarEvent($this);
        }
        return $this;
    }

    public function setCalendarEventType(?CalendarEventType $calendarEventType): self
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

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'UNKNOWN CALENDAR EVENT';
    }
}
