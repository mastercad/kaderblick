<?php

namespace App\Controller\Api;

use App\Entity\Position;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/positions', name: 'api_positions_')]
class PositionsController extends ApiController
{
    protected string $entityName = 'Position';
    protected string $entityNamePlural = 'Positions';
    protected string $entityClass = Position::class;
    protected string $urlPart = 'positions';
    protected array $relations = [];
}
