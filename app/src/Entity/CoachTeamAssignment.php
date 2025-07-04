<?php

namespace App\Entity;

use App\Repository\CoachTeamAssignmentRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CoachTeamAssignmentRepository::class)]
class CoachTeamAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['coach_team_assignment:read', 'coach_team_assignment:write', 'team:read', 'coach:read', 'club:read'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Coach::class, inversedBy: 'coachTeamAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['coach_team_assignment:read', 'team:read'])]
    private ?Coach $coach;

    #[ORM\ManyToOne(targetEntity: CoachTeamAssignmentType::class, inversedBy: 'coachTeamAssignments', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['coach_team_assignment:read'])]
    private ?CoachTeamAssignmentType $coachTeamAssignmentType = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'coachTeamAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    // !!! NO team:read right to prevent circular reference in serialization !!!
    #[Groups(['coach_team_assignment:read', 'coach:read'])]
    private ?Team $team;

    #[Groups(['coach_team_assignment:read', 'coach_team_assignment:write'])]
    #[Context(normalizationContext: ['datetime_format' => 'd.m.Y'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $startDate = null;

    #[Groups(['coach_team_assignment:read', 'coach_team_assignment:write'])]
    #[Context(normalizationContext: ['datetime_format' => 'd.m.Y'])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $endDate = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCoach(): Coach
    {
        return $this->coach;
    }

    public function setCoach(?Coach $coach): void
    {
        $this->coach = $coach;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    public function getCoachTeamAssignmentType(): ?CoachTeamAssignmentType
    {
        return $this->coachTeamAssignmentType;
    }

    public function setCoachTeamAssignmentType(?CoachTeamAssignmentType $coachTeamAssignmentType): void
    {
        $this->coachTeamAssignmentType = $coachTeamAssignmentType;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeInterface $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function __toString(): string
    {
        $end = $this->endDate ? ' bis ' . $this->endDate->format('d.m.Y') : '';

        return sprintf(
            '%s bei %s (seit %s%s)',
            $this->coach?->getFullName() ?? 'Unbekannter Spieler',
            $this->team?->getName() ?? 'Unbekanntes Team',
            $this->startDate?->format('d.m.Y') ?? 'unbekannt',
            $end
        );
    }
}
