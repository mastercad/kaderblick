<?php

namespace App\Controller\Api;

use App\Repository\SubstitutionReasonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/substitution-reasons', name: 'api_substitution_reasons_')]
class SubstitutionReasonsController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(SubstitutionReasonRepository $repo): JsonResponse
    {
        $reasons = $repo->fetchFullList();

        return $this->json($reasons);
    }
}
