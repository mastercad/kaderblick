<?php

namespace App\Entity;

use App\Repository\CalendarEventTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CalendarEventTypeRepository::class)]
#[ORM\Table(name: 'calendar_event_types')] // Tabellenname anpassen
class CalendarEventType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['calendar_event_type:read', 'calendar_event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['calendar_event_type:read', 'calendar_event:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 7)]
    #[Groups(['calendar_event_type:read', 'calendar_event:read'])]
    private ?string $color = '#1a4789';  // Standard Blau

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
