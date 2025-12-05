<?php

namespace App\Service;

use App\Entity\GameEvent;
use App\Entity\GameEventType;
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
     * Unterstützte config-Keys (Auszug):
     * - xField, yField, groupBy, filters, diagramType
     * - area: optional field alias (z.B. 'surfaceType') um Events nach einer Area aufzubrechen
     * - areaMode: 'overlay' (separate datasets pro area) oder 'single' (alle Areas zusammenfassen)
     *
     * Filter: zusätzlich zu team/eventType/player/dateFrom/dateTo wird `filters['surfaceType']` unterstützt.
     *
     * @param array<string, mixed> $config Report-Konfiguration
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

        // (keine Area-spezifische Logik hier - Chart 'area' wird im Frontend als Line+Fill gerendert)

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
        if (isset($filters['surfaceType'])) {
            // join through game -> location -> surfaceType
            $qb->join('e.game', 'g')->join('g.location', 'loc')->andWhere('loc.surfaceType = :surfaceType')->setParameter('surfaceType', (int) $filters['surfaceType']);
        }
        if (isset($filters['dateFrom'])) {
            $qb->andWhere('DATE(e.timestamp) >= DATE(:dateFrom)')->setParameter('dateFrom', new DateTimeImmutable($filters['dateFrom']));
        }
        if (isset($filters['dateTo'])) {
            $qb->andWhere('DATE(e.timestamp) <= DATE(:dateTo)')->setParameter('dateTo', new DateTimeImmutable($filters['dateTo']));
        }
        // NOTE: any area/surface-type filtering should be done via standard filters
        $events = $qb->getQuery()->getResult();

        // Post-query filtering for precipitation (weather) since weather is stored as JSON
        if (isset($filters['precipitation']) && in_array($filters['precipitation'], ['yes', 'no'], true)) {
            $filtered = [];
            foreach ($events as $ev) {
                $hasPrecip = false;
                if (method_exists($ev, 'getGame') && $ev->getGame() && method_exists($ev->getGame(), 'getCalendarEvent')) {
                    $ce = $ev->getGame()->getCalendarEvent();
                    if ($ce && method_exists($ce, 'getWeatherData')) {
                        $wd = $ce->getWeatherData();
                        if ($wd) {
                            $daily = $wd->getDailyWeatherData();
                            if (is_array($daily) && isset($daily['precipitation_sum'][0]) && is_numeric($daily['precipitation_sum'][0]) && $daily['precipitation_sum'][0] > 0) {
                                $hasPrecip = true;
                            }
                            $hourly = $wd->getHourlyWeatherData();
                            if (!$hasPrecip && is_array($hourly) && isset($hourly['precipitation']) && is_array($hourly['precipitation'])) {
                                foreach ($hourly['precipitation'] as $val) {
                                    if (is_numeric($val) && $val > 0) {
                                        $hasPrecip = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                if ('yes' === $filters['precipitation'] && $hasPrecip) {
                    $filtered[] = $ev;
                }
                if ('no' === $filters['precipitation'] && !$hasPrecip) {
                    $filtered[] = $ev;
                }
            }
            $events = $filtered;
        }

        // Build a meta object we can return to inform the UI about capabilities / data shape
        $meta = [];
        $meta['eventsCount'] = count($events);

        // Spatial heatmap support: if client requested spatial points, try to emit {x:0..100, y:0..100, intensity}
        if (isset($config['diagramType']) && 'pitchheatmap' === $config['diagramType'] && !empty($config['heatmapSpatial'])) {
            $meta['spatialRequested'] = true;
            $points = $this->generateSpatialPoints($events, $config);
            $meta['spatialProvided'] = !empty($points);

            // If we found spatial points, return them.
            // If not, fall back to the regular matrix/grid output so the frontend can still render a heatmap.
            if (!empty($points)) {
                return [
                    'labels' => [],
                    'datasets' => [
                        [
                            'label' => 'Heatmap',
                            'data' => $points,
                        ],
                    ],
                    'diagramType' => 'pitchheatmap',
                    'meta' => $meta,
                ];
            }
            // else: continue and generate matrix-style output below (meta will be attached later)
        }

        // Radar chart handling: expects `metrics` array in config and a `groupBy` (e.g. player/team)
        $diagramType = $config['diagramType'] ?? '';
        $metrics = $config['metrics'] ?? [];
        if ('radar' === $diagramType && is_array($metrics) && !empty($metrics)) {
            $radarResult = $this->generateReportDataForRadar($events, $metrics, $groupBy);
            // attach meta (eventsCount) for the UI
            $radarResult['meta'] = $radarResult['meta'] ?? [];
            $radarResult['meta']['eventsCount'] = $meta['eventsCount'];

            return $radarResult;
        }

        $result = $this->considerGroup($events, $diagramType, $xField, $yField, $groupBy);
        $result['meta'] = $meta;

        return $result;
    }

    /**
     * Generiert die Datenstruktur für ein Radar/Spinnennetz-Diagramm.
     * Erwartet `metrics` (Array von Feld-Aliases) und `groupBy` zur Bildung der Datasets.
     *
     * @param array<string, mixed> $events
     * @param array<int, string>   $metrics
     * @param array<int, string>   $groupBy
     *
     * @return array<string, mixed>
     */
    private function generateReportDataForRadar(array $events, array $metrics, array $groupBy): array
    {
        $fieldAliases = ReportFieldAliasService::fieldAliases($this->em);

        // Resolve any referenced eventType ids/codes to readable labels
        $eventTypeIds = [];
        $eventCodes = [];
        foreach ($metrics as $m) {
            if (preg_match('/^eventType:(\d+)$/', $m, $matches)) {
                $eventTypeIds[] = (int) $matches[1];
            } elseif (preg_match('/^eventCode:(.+)$/', $m, $matches)) {
                $eventCodes[] = $matches[1];
            }
        }
        $typesById = [];
        if (!empty($eventTypeIds)) {
            $types = $this->em->getRepository(GameEventType::class)->findBy(['id' => $eventTypeIds]);
            foreach ($types as $t) {
                $typesById[$t->getId()] = $t;
            }
        }

        // Build groups keyed by groupBy values
        $groups = [];
        foreach ($events as $event) {
            $layerKeyParts = [];
            foreach ($groupBy as $gField) {
                $layerKeyParts[] = $this->retrieveFieldValue($event, $gField);
            }
            $groupKey = $layerKeyParts ? implode(' | ', $layerKeyParts) : 'All';
            $groups[$groupKey][] = $event;
        }

        // Labels are metric labels
        $labels = [];
        foreach ($metrics as $m) {
            if (preg_match('/^eventType:(\d+)$/', $m, $matches)) {
                $id = (int) $matches[1];
                $labels[] = isset($typesById[$id]) ? $typesById[$id]->getName() : "Ereignis #$id";
                continue;
            }
            if (preg_match('/^eventCode:(.+)$/', $m, $matches)) {
                $labels[] = 'Ereignis: ' . $matches[1];
                continue;
            }
            $labels[] = $fieldAliases[$m]['label'] ?? $m;
        }

        $datasets = [];
        foreach ($groups as $groupLabel => $groupEvents) {
            $data = [];
            foreach ($metrics as $m) {
                $value = 0;
                if (isset($fieldAliases[$m]['aggregate']) && is_callable($fieldAliases[$m]['aggregate'])) {
                    $value = $fieldAliases[$m]['aggregate']($groupEvents);
                } elseif (preg_match('/^eventType:(\d+)$/', $m, $matches)) {
                    // count events with that GameEventType id
                    $typeId = (int) $matches[1];
                    $count = 0;
                    foreach ($groupEvents as $ev) {
                        $et = $ev->getGameEventType();
                        if ($et && $et->getId() === $typeId) {
                            ++$count;
                        }
                    }
                    $value = $count;
                } elseif (preg_match('/^eventCode:(.+)$/', $m, $matches)) {
                    // count events with that EventType code
                    $code = $matches[1];
                    $count = 0;
                    foreach ($groupEvents as $ev) {
                        $et = $ev->getGameEventType();
                        if ($et && $et->getCode() === $code) {
                            ++$count;
                        }
                    }
                    $value = $count;
                } elseif (preg_match('/^surfaceType:(\d+)$/', $m, $matches)) {
                    // count events played on that surface type
                    $surfaceId = (int) $matches[1];
                    $count = 0;
                    foreach ($groupEvents as $ev) {
                        if (method_exists($ev, 'getGame') && $ev->getGame() && method_exists($ev->getGame(), 'getLocation')) {
                            $location = $ev->getGame()->getLocation();
                            if ($location && method_exists($location, 'getSurfaceType')) {
                                $st = $location->getSurfaceType();
                                if ($st && method_exists($st, 'getId') && $st->getId() === $surfaceId) {
                                    ++$count;
                                }
                            }
                        }
                    }
                    $value = $count;
                } elseif ('weather:precipitation' === $m) {
                    // count events where the corresponding calendarEvent weather data indicates precipitation
                    $count = 0;
                    foreach ($groupEvents as $ev) {
                        if (!method_exists($ev, 'getGame') || !$ev->getGame() || !method_exists($ev->getGame(), 'getCalendarEvent')) {
                            continue;
                        }
                        $ce = $ev->getGame()->getCalendarEvent();
                        if (!$ce || !method_exists($ce, 'getWeatherData')) {
                            continue;
                        }
                        $wd = $ce->getWeatherData();
                        if (!$wd) {
                            continue;
                        }
                        // Check daily precipitation sum if available
                        $daily = $wd->getDailyWeatherData();
                        if (is_array($daily) && isset($daily['precipitation_sum'][0]) && is_numeric($daily['precipitation_sum'][0]) && $daily['precipitation_sum'][0] > 0) {
                            ++$count;
                            continue;
                        }
                        // Check hourly precipitation if available
                        $hourly = $wd->getHourlyWeatherData();
                        if (is_array($hourly) && isset($hourly['precipitation']) && is_array($hourly['precipitation'])) {
                            foreach ($hourly['precipitation'] as $val) {
                                if (is_numeric($val) && $val > 0) {
                                    ++$count;
                                    break;
                                }
                            }
                        }
                    }
                    $value = $count;
                } else {
                    // Fallback: count events where the alias/value returns a truthy value
                    $count = 0;
                    foreach ($groupEvents as $ev) {
                        $v = $this->retrieveFieldValue($ev, $m);
                        if (null !== $v && '' !== $v) {
                            ++$count;
                        }
                    }
                    $value = $count;
                }

                // Ensure numeric (cast to float)
                $value = (float) $value;
                $data[] = $value;
            }

            $datasets[] = [
                'label' => $groupLabel,
                'data' => $data,
            ];
        }

        // check if any non-zero value exists (useful to warn the UI)
        $hasNonZero = false;
        foreach ($datasets as $ds) {
            foreach ($ds['data'] as $v) {
                if (0 != $v) {
                    $hasNonZero = true;
                    break 2;
                }
            }
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
            'diagramType' => 'radar',
            'meta' => ['radarHasData' => $hasNonZero],
        ];
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
        // TODO das erscheint mir hier wenig sinnvoll. die Daten passen mit und ohne [$x] im frontend
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
        $fieldAliases = ReportFieldAliasService::fieldAliases($this->em);
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

    /**
     * Versucht aus GameEvent-Objekten räumliche Koordinaten zu extrahieren.
     * Unterstützt verschiedene mögliche Getter-Namen (z.B. getPosX/getPosY, getX/getY).
     * Liefert Punkte im Format [{x:0..100, y:0..100, intensity: number}, ...].
     *
     * @param array<string, mixed> $events
     * @param array<string, mixed> $config
     *
     * @return array<int, array<string, float|int>>
     */
    private function generateSpatialPoints(array $events, array $config): array
    {
        $points = [];
        foreach ($events as $ev) {
            $x = null;
            $y = null;

            if (method_exists($ev, 'getPosX') && method_exists($ev, 'getPosY')) {
                $x = $ev->getPosX();
                $y = $ev->getPosY();
            } elseif (method_exists($ev, 'getX') && method_exists($ev, 'getY')) {
                $x = $ev->getX();
                $y = $ev->getY();
            } elseif (method_exists($ev, 'getCanvasX') && method_exists($ev, 'getCanvasY')) {
                $x = $ev->getCanvasX();
                $y = $ev->getCanvasY();
            }

            if (!is_numeric($x) || !is_numeric($y)) {
                continue;
            }

            $x = (float) $x;
            $y = (float) $y;

            // Normalize fractions to percent if needed
            if ($x <= 1 && $x >= 0) {
                $x = $x * 100.0;
            }
            if ($y <= 1 && $y >= 0) {
                $y = $y * 100.0;
            }

            // Clamp
            $x = max(0.0, min(100.0, $x));
            $y = max(0.0, min(100.0, $y));

            $intensity = 1.0;
            if (!empty($config['heatmapIntensityField'])) {
                $v = $this->retrieveFieldValue($ev, (string) $config['heatmapIntensityField']);
                if (is_numeric($v)) {
                    $intensity = (float) $v;
                }
            }

            $points[] = [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'intensity' => $intensity,
            ];
        }

        return $points;
    }
}
