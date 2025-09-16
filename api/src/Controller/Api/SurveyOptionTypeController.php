<?php

namespace App\Controller\Api;

use App\Entity\SurveyOptionType;
use App\Repository\SurveyOptionTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/survey-option-types', name: 'api_survey_option_types_')]
class SurveyOptionTypeController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(SurveyOptionTypeRepository $repo): JsonResponse
    {
        $types = $repo->findAll();
        $data = array_map(fn ($type) => [
            'id' => $type->getId(),
            'name' => $type->getName(),
            'typeKey' => $type->getTypeKey(),
        ], $types);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(SurveyOptionType $type): JsonResponse
    {
        $data = [
            'id' => $type->getId(),
            'typeKey' => $type->getTypeKey(),
        ];

        return $this->json($data);
    }
}
