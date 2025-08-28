<?php

namespace App\Controller\Api;

use App\Entity\DashboardWidget;
use App\Entity\ReportDefinition;
use App\Entity\User;
use App\Service\ReportDataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/report')]
#[IsGranted('ROLE_USER')]
class ReportController extends AbstractController
{
    #[Route('/available', name: 'api_report_available', methods: ['GET'])]
    public function available(EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $repo = $em->getRepository(ReportDefinition::class);
        $templates = $repo->findBy(['isTemplate' => true]);
        $userReports = $repo->findBy(['user' => $user]);
        $result = [];
        foreach ($templates as $report) {
            $result[] = [
                'id' => $report->getId(),
                'name' => $report->getName(),
                'isTemplate' => true
            ];
        }
        foreach ($userReports as $report) {
            $result[] = [
                'id' => $report->getId(),
                'name' => $report->getName(),
                'isTemplate' => false
            ];
        }

        return $this->json($result);
    }

    #[Route('/widget/{widgetId}/data', name: 'api_report_data', methods: ['GET'])]
    public function getData(int $widgetId, EntityManagerInterface $em, Request $request, ReportDataService $reportDataService): JsonResponse
    {
        $widget = $em->getRepository(DashboardWidget::class)->find($widgetId);
        if (!$widget || $widget->getUser() !== $this->getUser() || 'report' !== $widget->getType()) {
            return $this->json(['error' => 'Not found or access denied'], 404);
        }
        $report = $widget->getReportDefinition();
        if (!$report) {
            return $this->json(['error' => 'No report definition'], 400);
        }
        $config = $report->getConfig();
        $rawFilters = $config['filters'] ?? [];

        $filters = [];
        foreach ($rawFilters as $k => $v) {
            if (null !== $v && '' !== $v) {
                $filters[$k] = $v;
            }
        }
        $config['filters'] = $filters;

        $reportData = $reportDataService->generateReportData($config);

        return $this->json([
            'config' => $config,
            'labels' => $reportData['labels'],
            'datasets' => $reportData['datasets'],
            'diagramType' => $config['diagramType'] ?? 'bar'
        ]);
    }

    #[Route('/definitions', name: 'api_report_definitions', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $repo = $em->getRepository(ReportDefinition::class);
        $templates = $repo->findBy(['isTemplate' => true]);
        $userReports = $repo->findBy(['user' => $user]);

        return $this->json([
            'templates' => $templates,
            'userReports' => $userReports
        ], 200, [], ['groups' => ['report:read']]);
    }

    #[Route('/definition', name: 'api_report_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['name'], $data['config'])) {
            return $this->json(['error' => 'Missing name or config'], 400);
        }
        /** @var User $user */
        $user = $this->getUser();
        $report = new ReportDefinition();
        $report->setName($data['name']);
        $report->setDescription($data['description'] ?? null);
        $report->setConfig($data['config']);
        $report->setUser($user);
        $report->setIsTemplate(false);
        $em->persist($report);
        $em->flush();

        return $this->json(['status' => 'success', 'id' => $report->getId()]);
    }

    #[Route('/definition/{id}', name: 'api_report_update', methods: ['PUT'])]
    public function update(ReportDefinition $report, Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($report->getUser()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        $data = json_decode($request->getContent(), true);
        if (isset($data['name'])) {
            $report->setName($data['name']);
        }
        if (array_key_exists('description', $data)) {
            $report->setDescription($data['description']);
        }
        if (isset($data['config'])) {
            $report->setConfig($data['config']);
        }
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    #[Route('/definition/{id}', name: 'api_report_delete', methods: ['DELETE'])]
    public function delete(ReportDefinition $report, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($report->getUser()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        $em->remove($report);
        $em->flush();

        return $this->json(['status' => 'success']);
    }
}
