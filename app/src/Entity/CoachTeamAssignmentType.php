<?php

namespace App\Entity;

use App\Repository\CoachTeamAssignmentTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * regular      Vertragsspieler             Fester Kader-Spieler (dauerhaft im Team)
 * loan         Leihgabe                    Temporär von einem anderen Verein ausgeliehen
 * guest        Gastspieler                 Spielt gelegentlich mit, z. B. in Freundschaftsspielen
 * test         Testspieler                 Spieler auf Probe, evtl. für Transfer oder Neuzugang
 * youth        Jugendspieler               Spieler aus dem Jugendbereich, der z. B. Aushilft in der 1. Mannschaft
 * dual         Doppelte Spielberechtigung  Spielt z. B. in zwei Mannschaften, oft bei Jugend/Herren-Kombis
 * cooperation  Kooperationsspieler         Kommt z. B. aus einem Partnerverein temporär
 * external     Externer Spieler            Gehört nicht zum Verein, aber wird für ein Turnier eingesetzt
 * suspended    Gesperrter Spieler          Aktuell nicht spielberechtigt (Disziplin, Formalien, etc.)
 * injured      Verletzter Spieler          Aktuell nicht einsatzfähig.
 */
#[ORM\Entity(repositoryClass: CoachTeamAssignmentTypeRepository::class)]
class CoachTeamAssignmentType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['coach_team_assignment_type:read', 'coach_team_assignment:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private int $id;

    #[Groups(['coach_team_assignment_type:read', 'coach_team_assignment:read'])]
    #[ORM\Column(type: 'string', length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $description = null;

    // Optional: z.B. für Sortierung oder Sichtbarkeit, dient einem "softdelete"
    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    /** @var Collection<int, CoachTeamAssignment> */
    #[ORM\OneToMany(targetEntity: CoachTeamAssignment::class, mappedBy: 'coachTeamAssignmentType')]
    private Collection $coachTeamAssignments;

    public function __construct()
    {
        $this->coachTeamAssignments = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /** @return Collection<int, CoachTeamAssignment> */
    public function getCoachTeamAssignments(): Collection
    {
        return $this->coachTeamAssignments;
    }

    public function addCoachTeamAssignment(CoachTeamAssignment $coachTeamAssignment): void
    {
        if (!$this->coachTeamAssignments->contains($coachTeamAssignment)) {
            $this->coachTeamAssignments[] = $coachTeamAssignment;
            $coachTeamAssignment->setCoachTeamAssignmentType($this);
        }
    }

    public function removeCoachTeamAssignment(CoachTeamAssignment $coachTeamAssignment): void
    {
        if ($this->coachTeamAssignments->contains($coachTeamAssignment)) {
            $this->coachTeamAssignments->removeElement($coachTeamAssignment);
            if ($coachTeamAssignment->getCoachTeamAssignmentType() === $this) {
                $coachTeamAssignment->setCoachTeamAssignmentType(null);
            }
        }
    }

    public function __toString()
    {
        return $this->name ?? 'UKNOWN PLAYER TEAM ASSIGNMENT TYPE';
    }
}
