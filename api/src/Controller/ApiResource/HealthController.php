<?php

namespace App\Controller\ApiResource;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/health', name: 'api_health_')]
class HealthController extends AbstractController
{
    /**
     * Public health check endpoint – no authentication required.
     * Used by the CI/CD deployment pipeline to verify the API is ready.
     */
    #[Route('', name: 'check', methods: ['GET'])]
    public function check(): JsonResponse
    {
        return $this->json(['status' => 'ok']);
    }
}
