<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'participations')]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Player $player;

    #[ORM\ManyToOne(targetEntity: CalendarEvent::class)]
    #[ORM\JoinColumn(nullable: false)]
    private CalendarEvent $event;

    #[ORM\Column(type: 'boolean')]
    private bool $isParticipating = true;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    public function __construct(Player $player, CalendarEvent $event)
    {
        $this->player = $player;
        $this->event = $event;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getEvent(): CalendarEvent
    {
        return $this->event;
    }

    public function setEvent(CalendarEvent $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function isParticipating(): bool
    {
        return $this->isParticipating;
    }

    public function setIsParticipating(bool $isParticipating): self
    {
        $this->isParticipating = $isParticipating;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }
}
