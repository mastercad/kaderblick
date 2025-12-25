<?php

namespace App\Controller\Api;

use App\Entity\SurveyOption;
use App\Repository\SurveyOptionRepository;
use App\Security\Voter\SurveyOptionVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/survey-options', name: 'api_survey_options_')]
class SurveyOptionController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(SurveyOptionRepository $repo): JsonResponse
    {
        $options = $repo->findAll();

        $options = array_filter($options, fn ($o) => $this->isGranted(SurveyOptionVoter::VIEW, $o));

        $data = array_map(fn ($o) => [
            'id' => $o->getId(),
            'optionText' => $o->getOptionText(),
        ], $options);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(SurveyOption $option): JsonResponse
    {
        if (!$this->isGranted(SurveyOptionVoter::VIEW, $option)) {
            return $this->json(['error' => 'Zugriff verweigert'], 403);
        }

        $data = [
            'id' => $option->getId(),
            'optionText' => $option->getOptionText(),
        ];

        return $this->json($data);
    }
}
