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
        $groupBy = $config['groupBy'] ?? [];
        if (!is_array($groupBy)) {
            $groupBy = $groupBy ? [$groupBy] : [];
        }

        $qb = $this->em->getRepository(GameEvent::class)->createQueryBuilder('e');
        if (isset($filters['team'])) {
            $qb->andWhere('e.team = :team')->setParameter('team', $filters['team']);
        }
        if (isset($filters['eventType'])) {
            $qb->andWhere('e.gameEventType = :eventType')->setParameter('eventType', $filters['eventType']);
        }
        if (isset($filters['player'])) {
            $qb->andWhere('e.player = :player')->setParameter('player', $filters['player']);
        }
        if (isset($filters['dateFrom'])) {
            $qb->andWhere('DATE(e.timestamp) >= DATE(:dateFrom)')->setParameter('dateFrom', new DateTimeImmutable($filters['dateFrom']));
        }
        if (isset($filters['dateTo'])) {
            $qb->andWhere('DATE(e.timestamp) <= DATE(:dateTo)')->setParameter('dateTo', new DateTimeImmutable($filters['dateTo']));
        }
        $events = $qb->getQuery()->getResult();

        return $this->considerGroup($events, $config['diagramType'] ?? '', $xField, $yField, $groupBy);
    }

    /**
     * Berücksichtigt die Gruppierung der Report-Daten.
     *
     * @param array<string, mixed> $events
     * @param array<string, mixed> $groupBy
     *
     * @return array<string, mixed>
     */
    private function considerGroup(array $events, string $diagramType, string $xField, string $yField, array $groupBy): array
    {
        if (empty($groupBy)) {
            if ('pie' === $diagramType) {
                return $this->generateReportDataForPieWithoutGroup($events, $yField);
            } else {
                return $this->generateReportDataForLineOrBarWithoutGroup($events, $xField, $yField);
            }
        }

        return $this->generateReportDataForGroup($events, $xField, $groupBy);
    }

    /**
     * Generiert die Datenstruktur für ein Kreisdiagramm ohne Gruppierung.
     *
     * @param array<string, mixed> $events
     *
     * @return array<string, mixed>
     */
    private function generateReportDataForPieWithoutGroup(array $events, string $yField): array
    {
        // Pie: Verteilung der yField-Werte (mit Fallback)
        $counts = [];
        foreach ($events as $event) {
            $y = $this->retrieveFieldValue($event, $yField);
            if (null === $y || '' === $y) {
                $y = 'Unbekannt';
            }
            $counts[$y] = ($counts[$y] ?? 0) + 1;
        }

        return [
            'labels' => array_keys($counts),
            'datasets' => [
                [
                    'label' => $yField,
                    'data' => array_values($counts),
                ]
            ],
        ];
    }

    /**
     * Generiert die Datenstruktur für ein Liniendiagramm oder Säulendiagramm ohne Gruppierung.
     *
     * @param array<string, mixed> $events
     *
     * @return array<string, mixed>
     */
    private function generateReportDataForLineOrBarWithoutGroup(array $events, string $xField, string $yField): array
    {
        // Bar/Line: X = xField, Y = Anzahl der Events pro X (mit Fallback)
        $counts = [];
        foreach ($events as $event) {
            $x = $this->retrieveFieldValue($event, $xField);
            if (null === $x || '' === $x) {
                $x = 'Unbekannt';
            }
            $counts[$x] = ($counts[$x] ?? 0) + 1;
        }
        $xLabels = array_keys($counts);
        sort($xLabels);
        $data = [];
        foreach ($xLabels as $x) {
            $data[] = $counts[$x];
        }

        return [
            'labels' => $xLabels,
            'datasets' => [
                [
                    'label' => $yField,
                    'data' => $data,
                ]
            ],
        ];
    }

    /**
     * Generiert die Datenstruktur für ein gruppiertes Diagramm.
     *
     * @param array<string, mixed> $events
     * @param array<string>        $groupBy
     *
     * @return array<string, mixed>
     */
    private function generateReportDataForGroup(array $events, string $xField, array $groupBy): array
    {
        // Mit groupBy: X = xField, Layer = groupBy, Y = yField
        $xValues = [];
        $layerValues = [];
        $matrix = [];
        foreach ($events as $event) {
            $x = $this->retrieveFieldValue($event, $xField);
            $layerKeyParts = [];
            foreach ($groupBy as $gField) {
                $layerKeyParts[] = $this->retrieveFieldValue($event, $gField);
            }
            $layerKey = $layerKeyParts ? implode(' | ', $layerKeyParts) : '';
            $xValues[$x] = true;
            $layerValues[$layerKey] = true;
            if (!isset($matrix[$layerKey][$x])) {
                $matrix[$layerKey][$x] = 0;
            }
            ++$matrix[$layerKey][$x];
        }
        $xLabels = array_keys($xValues);
        sort($xLabels);
        $layers = array_keys($layerValues);
        sort($layers);

        $datasets = [];
        foreach ($layers as $layerKey) {
            $data = [];
            foreach ($xLabels as $xVal) {
                $data[] = $matrix[$layerKey][$xVal] ?? 0;
            }
            $datasets[] = [
                'label' => $layerKey,
                'data' => $data,
            ];
        }

        return [
            'labels' => $xLabels,
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
