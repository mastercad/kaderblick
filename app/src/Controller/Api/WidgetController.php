<?php

namespace App\Controller\Api;

use App\Entity\DashboardWidget;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/widget')]
#[IsGranted('ROLE_USER')]
class WidgetController extends AbstractController
{
    #[Route('/add', name: 'api_widget_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $type = $data['type'] ?? null;

        if (!$type) {
            return $this->json(['error' => 'Widget type is required'], 400);
        }

        /** @var User $user */
        $user = $this->getUser();
        $widget = new DashboardWidget();
        $widget->setUser($user);
        $widget->setType($type);
        $widget->setPosition(count($user->getWidgets()));

        // Default-Konfiguration je nach Widget-Typ
        $config = match ($type) {
            'upcoming_events' => ['limit' => 5],
            default => []
        };
        $widget->setConfig($config);

        $em->persist($widget);
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    #[Route('/{id}', name: 'api_widget_remove', methods: ['DELETE'])]
    public function remove(
        DashboardWidget $widget,
        EntityManagerInterface $em
    ): JsonResponse {
        // Prüfen ob Widget dem aktuellen User gehört
        if ($widget->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($widget);
        $em->flush();

        return $this->json(['status' => 'success']);
    }
}
