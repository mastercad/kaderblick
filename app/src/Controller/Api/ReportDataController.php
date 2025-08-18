<?php

namespace App\Controller\Api;

use App\Entity\DashboardWidget;
use App\Entity\GameEvent;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/report-data')]
#[IsGranted('ROLE_USER')]
class ReportDataController extends AbstractController
{
    #[Route('/{widgetId}', name: 'api_report_data', methods: ['GET'])]
    public function getData(int $widgetId, EntityManagerInterface $em, Request $request): JsonResponse
    {
        $widget = $em->getRepository(DashboardWidget::class)->find($widgetId);
        if (!$widget || $widget->getUser() !== $this->getUser() || $widget->getType() !== 'report') {
            return $this->json(['error' => 'Not found or access denied'], 404);
        }
        $report = $widget->getReportDefinition();
        if (!$report) {
            return $this->json(['error' => 'No report definition'], 400);
        }
        $config = $report->getConfig();
        // Example: config = ['xField' => 'player', 'yField' => 'goals', 'diagramType' => 'bar', 'filters' => [...]]
        $xField = $config['xField'] ?? 'player';
        $yField = $config['yField'] ?? 'goals';
        $diagramType = $config['diagramType'] ?? 'bar';
        $filters = $config['filters'] ?? [];

        $qb = $em->getRepository(GameEvent::class)->createQueryBuilder('e');
        // Apply filters (example: by team, event type, date range)
        if (isset($filters['team'])) {
            $qb->andWhere('e.team = :team')->setParameter('team', $filters['team']);
        }
        if (isset($filters['eventType'])) {
            $qb->andWhere('e.gameEventType = :eventType')->setParameter('eventType', $filters['eventType']);
        }
        if (isset($filters['dateFrom'])) {
            $qb->andWhere('e.timestamp >= :dateFrom')->setParameter('dateFrom', new DateTimeImmutable($filters['dateFrom']));
        }
        if (isset($filters['dateTo'])) {
            $qb->andWhere('e.timestamp <= :dateTo')->setParameter('dateTo', new DateTimeImmutable($filters['dateTo']));
        }
        $events = $qb->getQuery()->getResult();

        // Aggregate data for chart
        $labels = [];
        $values = [];
        foreach ($events as $event) {
            $x = $this->retrieveFieldValue($event, $xField);
            $y = $this->retrieveFieldValue($event, $yField);
            if (!isset($values[$x])) {
                $values[$x] = 0;
            }
            $values[$x] += is_numeric($y) ? $y : 1;
        }
        $labels = array_keys($values);
        $data = array_values($values);

        return $this->json([
            'labels' => $labels,
            'values' => $data,
            'diagramType' => $diagramType,
        ]);
    }

    private function retrieveFieldValue(GameEvent $event, string $field): mixed
    {
        // Simple dynamic getter for demo; extend as needed
        $getter = 'get' . ucfirst($field);
        if (method_exists($event, $getter)) {
            $value = $event->$getter();
            if (is_object($value) && method_exists($value, '__toString')) {
                return (string)$value;
            }
            return $value;
        }
        return null;
    }
}
