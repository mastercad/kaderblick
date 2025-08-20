<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Location;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/locations', name: 'api_locations_')]
class LocationsController extends ApiController
{
    protected string $entityName = 'Location';
    protected string $entityNamePlural = 'Locations';
    protected string $entityClass = Location::class;
    protected string $urlPart = 'locations';
    protected array $relations = [
        'surfaceType' => ['type' => 2, 'entityName' => 'SurfaceType']
    ];
}
