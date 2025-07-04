<?php

namespace App\Controller;

use App\Entity\Goal;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/goals', name: 'api_goals_')]
class GoalsController extends ApiController
{
    protected string $entityName = 'Goal';
    protected string $entityNamePlural = 'Goals';
    protected string $entityClass = Goal::class;
    protected array $relations = [
        'scorer' => [
            'entityName' => 'Player',
            'fieldName' => 'scorer',
            'type' => 2
        ],
        'game' => [
            'entityName' => 'Game',
            'type' => 2
        ]
    ];
    protected array $relationEntries = [];
    protected string $urlPart = 'goals';
}
