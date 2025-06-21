<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'participations')]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
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

    // Getter/Setter...
}
