<?php

namespace App\Entity;

use App\Repository\SubstitutionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

// Auswechsel
#[ORM\Entity(repositoryClass: SubstitutionRepository::class)]
#[ORM\Table(name: 'substitutions')]
#[ORM\Index(name: 'idx_substitution_game_id', columns: ['game_id'])]
#[ORM\Index(name: 'idx_substitution_player_in_id', columns: ['player_in_id'])]
#[ORM\Index(name: 'idx_substitution_player_out_id', columns: ['player_out_id'])]
#[ORM\Index(name: 'idx_substitution_team_id', columns: ['team_id'])]
#[ORM\Index(name: 'idx_substitution_substitution_reason_id', columns: ['substitution_reason_id'])]
class Substitution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private int $id;

    #[Groups(['substitution:read', 'substitution:write', 'game:read', 'game_event:read'])]
    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'substitutions')]
    #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'id', nullable: false)]
    private Game $game;

    #[ORM\Column(type: 'integer')]
    private int $minute;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'player_in_id', referencedColumnName: 'id', nullable: false)]
    private Player $playerIn;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'player_out_id', referencedColumnName: 'id', nullable: false)]
    private Player $playerOut;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: false)]
    private Team $team;

    #[Groups(['substitution:read'])]
    #[ORM\ManyToOne(targetEntity: SubstitutionReason::class, inversedBy: 'substitutions')]
    #[ORM\JoinColumn(name: 'substitution_reason_id', referencedColumnName: 'id', nullable: true)]
    private ?SubstitutionReason $substitutionReason = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getMinute(): int
    {
        return $this->minute;
    }

    public function setMinute(int $minute): self
    {
        $this->minute = $minute;

        return $this;
    }

    public function getPlayerIn(): Player
    {
        return $this->playerIn;
    }

    public function setPlayerIn(Player $playerIn): self
    {
        $this->playerIn = $playerIn;

        return $this;
    }

    public function getPlayerOut(): Player
    {
        return $this->playerOut;
    }

    public function setPlayerOut(Player $playerOut): self
    {
        $this->playerOut = $playerOut;

        return $this;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getSubstitutionReason(): ?SubstitutionReason
    {
        return $this->substitutionReason;
    }

    public function setSubstitutionReason(?SubstitutionReason $substitutionReason): self
    {
        $this->substitutionReason = $substitutionReason;

        return $this;
    }
}
