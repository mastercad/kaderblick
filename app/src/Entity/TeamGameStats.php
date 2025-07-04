<?php

namespace App\Entity;

use App\Repository\TeamGameStatsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamGameStatsRepository::class)]
class TeamGameStats
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Game::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Game $game;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Team $team;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $possession = null; // in Prozent

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $corners = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $offsides = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $shots = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $shotsOnTarget = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $fouls = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $yellowCards = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $redCards = null;
}
