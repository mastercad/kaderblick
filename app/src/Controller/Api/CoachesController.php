<?php

namespace App\Controller\Api;

use App\Entity\Coach;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/coaches', name: 'api_coaches_')]
class CoachesController extends ApiController
{
    protected string $entityName = 'Coach';
    protected string $entityNamePlural = 'Coaches';
    protected string $entityClass = Coach::class;
    protected array $relations = [
        'coachTeamAssignments' => [
            'type' => 4,
            'entityName' => 'CoachTeamAssignment',
            'label_fields' => ['team.name', 'coach.firstName', 'coach.lastName']
        ],
        'coachClubAssignments' => [
            'type' => 4,
            'entityName' => 'CoachClubAssignment',
            'label_fields' => ['club.name']
        ],
        'coachNationalityAssignments' => ['type' => 4, 'entityName' => 'CoachNationalityAssignment'],
        'coachLicenseAssignments' => [
            'type' => 4,
            'entityName' => 'CoachLicenseAssignment',
            'label_fields' => ['license.name']
        ]
    ];
    protected string $urlPart = 'coaches';
}
