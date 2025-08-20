<?php

namespace App\Controller\Api;

use App\Entity\SurfaceType;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/surface-types', name: 'api_surface_types_')]
class SurfaceTypesController extends ApiController
{
    protected string $entityName = 'SurfaceType';
    protected string $entityNamePlural = 'SurfaceTypes';
    protected string $entityClass = SurfaceType::class;
    protected string $urlPart = 'surface-types';
    protected array $relations = [];
}
