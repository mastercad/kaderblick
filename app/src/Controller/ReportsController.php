<?php

namespace App\Controller;

use App\Entity\ReportDefinition;
use App\Entity\User;
use App\Service\ReportFieldAliasService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReportsController extends AbstractController
{
    #[Route('/reports/preview', name: 'app_report_preview', methods: ['POST'])]
    public function preview(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $fieldAliases = ReportFieldAliasService::fieldAliases();
        $data = $request->request->all();
        $report = new ReportDefinition();
        $report->setName($data['name'] ?? '');
        $report->setDescription($data['description'] ?? null);
        $config = [
            'diagramType' => $data['config']['diagramType'] ?? 'bar',
            'xField' => $data['config']['xField'] ?? 'player',
            'yField' => $data['config']['yField'] ?? 'goals',
            // Accept groupBy as array, fallback to empty array
            'groupBy' => $data['config']['groupBy'] ?? [],
        ];
        if (!isset($fieldAliases[$config['xField']])) {
            $config['xField'] = array_key_first($fieldAliases);
        }
        if (!isset($fieldAliases[$config['yField']])) {
            $config['yField'] = array_key_first($fieldAliases);
        }
        // groupBy: ensure array
        if (!is_array($config['groupBy'])) {
            $config['groupBy'] = $config['groupBy'] ? [$config['groupBy']] : [];
        }
        $report->setConfig($config);
        $report->setIsTemplate(false);
        $report->setUser($user);

        // Aggregation logic with multi-grouping support
        $xField = $config['xField'] ?? 'player';
        $yField = $config['yField'] ?? 'goals';
        $diagramType = $config['diagramType'] ?? 'bar';
        $groupBy = $config['groupBy'] ?? [];
        $filters = $config['filters'] ?? [];

        $qb = $em->getRepository(\App\Entity\GameEvent::class)->createQueryBuilder('e');
        if (isset($filters['team'])) {
            $qb->andWhere('e.team = :team')->setParameter('team', $filters['team']);
        }
        if (isset($filters['eventType'])) {
            $qb->andWhere('e.gameEventType = :eventType')->setParameter('eventType', $filters['eventType']);
        }
        if (isset($filters['dateFrom'])) {
            $qb->andWhere('e.timestamp >= :dateFrom')->setParameter('dateFrom', new \DateTimeImmutable($filters['dateFrom']));
        }
        if (isset($filters['dateTo'])) {
            $qb->andWhere('e.timestamp <= :dateTo')->setParameter('dateTo', new \DateTimeImmutable($filters['dateTo']));
        }
        $events = $qb->getQuery()->getResult();

        // Flexible Aggregation für beliebige X/Y (auch kategorisch)
        $xValues = [];
        $yValues = [];
        $matrix = [];
        $xyDebug = [];
        foreach ($events as $event) {
            $x = $this->retrieveFieldValue($event, $xField);
            $y = $this->retrieveFieldValue($event, $yField);
            $xyDebug[] = ['x' => $x, 'y' => $y];
            $xValues[$x] = true;
            $yValues[$y] = true;
            if (!isset($matrix[$x][$y])) {
                $matrix[$x][$y] = 0;
            }
            $matrix[$x][$y] += 1;
        }
        $xLabels = array_keys($xValues);
        $yLabels = array_keys($yValues);
        sort($xLabels);
        sort($yLabels);

        // IMMER vollständige Matrix: Labels = X, Datasets = Y
        $labels = $xLabels;
        $datasets = [];
        foreach ($yLabels as $yVal) {
            $data = [];
            foreach ($xLabels as $xVal) {
                $data[] = $matrix[$xVal][$yVal] ?? 0;
            }
            $datasets[] = [
                'label' => (string)$yVal,
                'data' => $data,
            ];
        }
        $previewHtml = $this->renderView('report/_preview_chart.html.twig', [
            'report' => $report,
            'labels' => $labels,
            'datasets' => $datasets,
            'diagramType' => $diagramType,
        ]);
        // Debug-Ausgabe als Teil der Response
        return $this->json([
            'preview' => $previewHtml,
            'debug_labels' => $labels,
            'debug_datasets' => $datasets,
            'debug_xy' => $xyDebug,
        ]);
    }

    // Hilfsmethode für Vorschau wie in Api/ReportController
    private function retrieveFieldValue($event, string $field): mixed
    {
        $fieldAliases = \App\Service\ReportFieldAliasService::fieldAliases();
        if (isset($fieldAliases[$field]['value']) && is_callable($fieldAliases[$field]['value'])) {
            return $fieldAliases[$field]['value']($event);
        }
        $getter = 'get' . ucfirst($field);
        if (method_exists($event, $getter)) {
            $value = $event->$getter();
            if (is_object($value) && method_exists($value, '__toString')) {
                return (string) $value;
            }
            return $value;
        }
        return null;
    }
    
    #[Route('/reports', name: 'app_report_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $repo = $em->getRepository(ReportDefinition::class);
        $templates = $repo->findBy(['isTemplate' => true]);
        $userReports = $repo->findBy(['user' => $user]);

        return $this->render('report/list.html.twig', [
            'templates' => $templates,
            'userReports' => $userReports
        ]);
    }

    #[Route('/reports/builder/{id}', name: 'app_report_builder', requirements: ['id' => '\\d+'], defaults: ['id' => null], methods: ['GET', 'POST'])]
    public function builder(Request $request, EntityManagerInterface $em, ?int $id = null): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $repo = $em->getRepository(ReportDefinition::class);
        $isEdit = null !== $id;
        $report = $isEdit ? $repo->find($id) : new ReportDefinition();
        $fieldAliases = ReportFieldAliasService::fieldAliases();
        if ($isEdit && (!$report || ($report->getUser() && $report->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')))) {
            throw $this->createAccessDeniedException();
        }
        
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $report->setName($data['name'] ?? '');
            $report->setDescription($data['description'] ?? null);
            $config = [
                'diagramType' => $data['config']['diagramType'] ?? 'bar',
                'xField' => $data['config']['xField'] ?? 'player',
                'yField' => $data['config']['yField'] ?? 'goals',
            ];
            // Nur erlaubte Aliase speichern
            if (!isset($fieldAliases[$config['xField']])) {
                $config['xField'] = array_key_first($fieldAliases);
            }
            if (!isset($fieldAliases[$config['yField']])) {
                $config['yField'] = array_key_first($fieldAliases);
            }
            $report->setConfig($config);
            if ($this->isGranted('ROLE_ADMIN') && isset($data['isTemplate'])) {
                $report->setIsTemplate(true);
                $report->setUser(null);
            } else {
                $report->setIsTemplate(false);
                $report->setUser($user);
            }

            $em->persist($report);
            $em->flush();

            return $this->redirectToRoute('app_report_list');
        }

        return $this->render('report/builder.html.twig', [
            'report' => $report,
            'form_action' => $isEdit ? $this->generateUrl('app_report_builder', ['id' => $id]) : $this->generateUrl('app_report_builder'),
            'fieldAliases' => $fieldAliases,
        ]);
    }

    #[Route('/reports/delete/{id}', name: 'app_report_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $report = $em->getRepository(ReportDefinition::class)->find($id);
        if (!$report || ($report->getUser() && $report->getUser() !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createNotFoundException('Report not found or access denied');
        }
        $em->remove($report);
        $em->flush();
        $this->addFlash('success', 'Report deleted.');

        return $this->redirectToRoute('app_report_list');
    }

    #[Route('/reports/add-widget/{id}', name: 'app_report_add_widget', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function addWidget(int $id, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $report = $em->getRepository(ReportDefinition::class)->find($id);
        if (!$report) {
            throw $this->createNotFoundException('Report not found');
        }
        $widget = new \App\Entity\DashboardWidget();
        $widget->setUser($user);
        $widget->setType('report');
        $widget->setPosition(0); // Or calculate next position
        $widget->setWidth(6);
        $widget->setEnabled(true);
        $widget->setReportDefinition($report);
        $em->persist($widget);
        $em->flush();
        $this->addFlash('success', 'Report widget added to your dashboard.');

        return $this->redirectToRoute('app_dashboard_index');
    }
}
