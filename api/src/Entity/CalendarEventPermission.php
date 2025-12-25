<?php

namespace App\Entity;

use App\Enum\CalendarEventPermissionType;
use App\Repository\CalendarEventPermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CalendarEventPermissionRepository::class)]
#[ORM\Table(name: 'calendar_event_permissions')]
#[ORM\Index(name: 'idx_calendar_event_permissions_calendar_event_id', columns: ['calendar_event_id'])]
#[ORM\Index(name: 'idx_calendar_event_permissions_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_calendar_event_permissions_team_id', columns: ['team_id'])]
#[ORM\Index(name: 'idx_calendar_event_permissions_club_id', columns: ['club_id'])]
class CalendarEventPermission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CalendarEvent::class, inversedBy: 'permissions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CalendarEvent $calendarEvent = null;

    #[ORM\Column(type: 'string', length: 32, enumType: CalendarEventPermissionType::class)]
    private CalendarEventPermissionType $permissionType;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Team $team = null;

    #[ORM\ManyToOne(targetEntity: Club::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Club $club = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPermissionType(): CalendarEventPermissionType
    {
        return $this->permissionType;
    }

    public function setPermissionType(CalendarEventPermissionType $permissionType): self
    {
        $this->permissionType = $permissionType;

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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): self
    {
        $this->club = $club;

        return $this;
    }
}
