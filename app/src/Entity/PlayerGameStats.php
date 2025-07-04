<?php

namespace App\Entity;

use App\Repository\PlayerGameStatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerGameStatsRepository::class)]
class PlayerGameStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Game $game;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Player $player;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $minutesPlayed = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $shots = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $shotsOnTarget = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $passes = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $passesCompleted = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $tackles = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $interceptions = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $foulsCommitted = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $foulsSuffered = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $distanceCovered = null; // in Metern
}
