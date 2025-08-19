<?php

namespace App\Service;

use App\Entity\GameEvent;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ReportDataService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Generiert die Datenstruktur für einen Report (Labels, Datasets) anhand der Report-Definition.
     *
     * @param array<string, mixed> $config Report-Konfiguration (xField, yField, groupBy, filters, diagramType)
     *
     * @return array<string, mixed>
     */
    public function generateReportData(array $config): array
    {
        $xField = $config['xField'] ?? 'player';
        $yField = $config['yField'] ?? 'goals';
        $filters = $config['filters'] ?? [];

        $qb = $this->em->getRepository(GameEvent::class)->createQueryBuilder('e');
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

        // Flexible Aggregation für beliebige X/Y (auch kategorisch)
        $xValues = [];
        $yValues = [];
        $matrix = [];
        foreach ($events as $event) {
            $x = $this->retrieveFieldValue($event, $xField);
            $y = $this->retrieveFieldValue($event, $yField);
            $xValues[$x] = true;
            $yValues[$y] = true;
            if (!isset($matrix[$x][$y])) {
                $matrix[$x][$y] = 0;
            }
            ++$matrix[$x][$y];
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
                'label' => (string) $yVal,
                'data' => $data,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    private function retrieveFieldValue(GameEvent $event, string $field): mixed
    {
        $fieldAliases = ReportFieldAliasService::fieldAliases();
        if (isset($fieldAliases[$field]['value']) && is_callable($fieldAliases[$field]['value'])) {
            return $fieldAliases[$field]['value']($event);
        }

        /* TODO kann zukünftig eher weg, das soll alles nur noch über die alias definition laufen! */
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
}
