<?php

namespace App\Controller;

use App\Entity\StrongFoot;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/strong-feet', name: 'api_strong_feet_')]
class StrongFeetController extends ApiController
{
    protected string $entityName = 'StrongFoot';
    protected string $entityNamePlural = 'StrongFeet';
    protected string $entityClass = StrongFoot::class;
    protected string $urlPart = 'strong-feet';
    protected array $relations = [];
}
