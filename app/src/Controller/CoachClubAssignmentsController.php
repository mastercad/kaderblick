<?php

namespace App\Controller;

use App\Entity\CoachClubAssignment;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/coach-club-assignments', name: 'api_coach_club_assignments_')]
class CoachClubAssignmentsController extends ApiController
{
    protected string $entityName = 'CoachClubAssignment';
    protected string $entityNamePlural = 'CoachClubAssignments';
    protected string $entityClass = CoachClubAssignment::class;
    protected string $urlPart = 'coach-club-assignments';
    protected array $relations = [
        'coach' => ['type' => 2, 'entityName' => 'Coach'],
        'club' => ['type' => 2, 'entityName' => 'Club']
    ];
}
