<?php

namespace App\Controller\Api;

use App\Entity\DashboardWidget;
use App\Entity\ReportDefinition;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/report-widget')]
#[IsGranted('ROLE_USER')]
class ReportWidgetController extends AbstractController
{
    #[Route('/add', name: 'api_report_widget_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $reportId = $data['reportId'] ?? null;
        $position = $data['position'] ?? 0;
        $width = $data['width'] ?? 6;
        if (!$reportId) {
            return $this->json(['error' => 'Report ID is required'], 400);
        }

        /** @var User $user */
        $user = $this->getUser();
        $report = $em->getRepository(ReportDefinition::class)->find($reportId);
        if (null === $report) {
            return $this->json(['error' => 'Report not found'], 404);
        }

        $widget = new DashboardWidget();
        $widget->setUser($user);
        $widget->setType('report');
        $widget->setPosition($position);
        $widget->setWidth($width);
        $widget->setEnabled(true);
        $widget->setReportDefinition($report);

        $em->persist($widget);
        $em->flush();

        return $this->json(['status' => 'success', 'widgetId' => $widget->getId()]);
    }

    #[Route('/{id}', name: 'api_report_widget_remove', methods: ['DELETE'])]
    public function remove(DashboardWidget $widget, EntityManagerInterface $em): JsonResponse
    {
        if ($widget->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        $em->remove($widget);
        $em->flush();
        return $this->json(['status' => 'success']);
    }
}
