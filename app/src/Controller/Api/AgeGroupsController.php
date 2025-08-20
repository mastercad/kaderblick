<?php

namespace App\Controller\Api;

use App\Entity\AgeGroup;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/age-groups', name: 'api_age_groups_')]
class AgeGroupsController extends ApiController
{
    protected string $entityName = 'AgeGroup';
    protected string $entityNamePlural = 'AgeGroups';
    protected string $entityClass = AgeGroup::class;
    protected string $urlPart = 'age-groups';
    protected array $relations = [];
    protected array $relationEntries = [];
}
