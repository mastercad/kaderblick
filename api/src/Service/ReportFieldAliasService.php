<?php

namespace App\Service;

use App\Entity\GameEventType;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class ReportFieldAliasService
{
    /**
     * Returns a list of user-friendly report field aliases and their mapping to entity fields.
     * This can be extended for more entities and relations.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function fieldAliases(?EntityManagerInterface $em = null): array
    {
        // If an EntityManager is provided we can resolve GameEventType ids for code-based aliases
        $typesByCode = [];
        $typesById = [];
        if (null !== $em) {
            $types = $em->getRepository(GameEventType::class)->findAll();
            foreach ($types as $t) {
                // GameEventType is a known entity; call getters directly
                $typesByCode[$t->getCode()] = $t;
                $typesById[$t->getId()] = $t;
            }
        }

        $aliases = [
            // GameEvent fields
            'player' => [
                'label' => 'Spieler',
                'entity' => 'GameEvent',
                'field' => 'player',
                'type' => 'relation',
                'subfield' => 'fullName', // Player entity
            ],
            'team' => [
                'label' => 'Mannschaft',
                'entity' => 'GameEvent',
                'field' => 'team',
                'type' => 'relation',
                'subfield' => 'name', // Team entity
            ],
            'eventType' => [
                'label' => 'Ereignistyp',
                'value' => static function ($event) {
                    if (method_exists($event, 'getGameEventType')) {
                        $type = $event->getGameEventType();
                        if ($type && method_exists($type, 'getName')) {
                            return $type->getName();
                        }
                    }

                    return null;
                },
                'entity' => 'GameEvent',
                'field' => 'gameEventType',
                'type' => 'relation',
                'subfield' => 'name', // GameEventType entity
            ],
            'surfaceType' => [
                'label' => 'Platztyp',
                'value' => static function ($event) {
                    if (method_exists($event, 'getGame') && $event->getGame() && method_exists($event->getGame(), 'getLocation')) {
                        $location = $event->getGame()->getLocation();
                        if ($location && method_exists($location, 'getSurfaceType')) {
                            return $location->getSurfaceType();
                        }
                    }

                    return null;
                },
                'entity' => 'Location',
                'field' => 'surfaceType',
                'type' => 'string'
            ],
            'minute' => [
                'label' => 'Minute',
                'entity' => 'GameEvent',
                'field' => 'timestamp',
                'type' => 'datetime',
                'format' => 'i', // Minute aus Zeitstempel
            ],
            'gameDate' => [
                'label' => 'Spieldatum',
                'value' => static function ($event) {
                    if (method_exists($event, 'getGame') && $event->getGame() && method_exists($event->getGame(), 'getCalendarEvent')) {
                        $calendarEvent = $event->getGame()->getCalendarEvent();
                        if ($calendarEvent && method_exists($calendarEvent, 'getStartDate')) {
                            $date = $calendarEvent->getStartDate();
                            if ($date instanceof DateTimeInterface) {
                                // Format für Chart: z.B. "Y-m-d H:i"
                                return $date->format('Y-m-d H:i');
                            }
                        }
                    }

                    return null;
                },
                'entity' => 'Game',
                'field' => 'startDate',
                'type' => 'date',
            ],
            'homeTeam' => [
                'label' => 'Heimteam',
                'entity' => 'Game',
                'field' => 'homeTeam',
                'type' => 'relation',
                'subfield' => 'name',
            ],
            'awayTeam' => [
                'label' => 'Auswärtsteam',
                'entity' => 'Game',
                'field' => 'awayTeam',
                'type' => 'relation',
                'subfield' => 'name',
            ],
            // Player fields
            'playerFirstName' => [
                'label' => 'Spieler Vorname',
                'entity' => 'Player',
                'field' => 'firstName',
                'type' => 'string',
            ],
            'playerLastName' => [
                'label' => 'Spieler Nachname',
                'entity' => 'Player',
                'field' => 'lastName',
                'type' => 'string',
            ],
            // Metric aliases for radar charts and similar aggregated reports
            'goals' => [
                'label' => 'Tore',
                'aggregate' => (static function (array $events) use ($typesByCode) {
                    $goalId = null;
                    if (isset($typesByCode['goal'])) {
                        $goalId = $typesByCode['goal']->getId();
                    }
                    $c = 0;
                    foreach ($events as $e) {
                        if (method_exists($e, 'getGameEventType')) {
                            $t = $e->getGameEventType();
                            if ($t) {
                                if (null !== $goalId && $t->getId() === $goalId) {
                                    ++$c;
                                    continue;
                                }
                                if ('goal' === $t->getCode()) {
                                    ++$c;
                                }
                            }
                        }
                    }

                    return $c;
                }),
            ],
            'shots' => [
                'label' => 'Schüsse',
                'aggregate' => (static function (array $events) use ($typesByCode) {
                    $shotIds = [];
                    foreach (['shot', 'shot_on_target', 'shot_off_target'] as $code) {
                        if (isset($typesByCode[$code])) {
                            $shotIds[] = $typesByCode[$code]->getId();
                        }
                    }
                    $c = 0;
                    foreach ($events as $e) {
                        if (method_exists($e, 'getGameEventType')) {
                            $t = $e->getGameEventType();
                            if ($t) {
                                if (!empty($shotIds) && in_array($t->getId(), $shotIds, true)) {
                                    ++$c;
                                    continue;
                                }
                                $code = $t->getCode();
                                if (in_array($code, ['shot', 'shot_on_target', 'shot_off_target'], true)) {
                                    ++$c;
                                }
                            }
                        }
                    }

                    return $c;
                }),
            ],
            'dribbles' => [
                'label' => 'Dribblings',
                'aggregate' => (static function (array $events) use ($typesByCode) {
                    $dribbleId = null;
                    if (isset($typesByCode['dribble'])) {
                        $dribbleId = $typesByCode['dribble']->getId();
                    }
                    $c = 0;
                    foreach ($events as $e) {
                        if (method_exists($e, 'getGameEventType')) {
                            $t = $e->getGameEventType();
                            if ($t) {
                                if (null !== $dribbleId && $t->getId() === $dribbleId) {
                                    ++$c;
                                    continue;
                                }
                                if ('dribble' === $t->getCode()) {
                                    ++$c;
                                }
                            }
                        }
                    }

                    return $c;
                }),
            ],
            'duelsWonPercent' => [
                'label' => 'Zweikämpfe gewonnen %',
                'aggregate' => (static function (array $events) use ($typesByCode) {
                    $wonId = isset($typesByCode['duel_won']) ? $typesByCode['duel_won']->getId() : null;
                    $lostId = isset($typesByCode['duel_lost']) ? $typesByCode['duel_lost']->getId() : null;
                    $won = 0;
                    $lost = 0;
                    foreach ($events as $e) {
                        if (method_exists($e, 'getGameEventType')) {
                            $t = $e->getGameEventType();
                            if ($t) {
                                if (null !== $wonId && $t->getId() === $wonId) {
                                    ++$won;
                                    continue;
                                }
                                if (null !== $lostId && $t->getId() === $lostId) {
                                    ++$lost;
                                    continue;
                                }
                                $code = $t->getCode();
                                if ('duel_won' === $code) {
                                    ++$won;
                                }
                                if ('duel_lost' === $code) {
                                    ++$lost;
                                }
                            }
                        }
                    }

                    $total = $won + $lost;
                    if (0 === $total) {
                        return 0;
                    }

                    return ($won / $total) * 100;
                }),
            ],
            'substitutionsIn' => [
                'label' => 'Einwechslungen',
                'aggregate' => (static function (array $events) {
                    $c = 0;
                    foreach ($events as $e) {
                        if (method_exists($e, 'isSubstitutionIn') && $e->isSubstitutionIn()) {
                            ++$c;
                        }
                    }

                    return $c;
                }),
            ],
            // Add more as needed ...
        ];

        // Augment aliases with deterministic metadata to make accessibility checks and
        // query building easier. We avoid changing existing alias semantics; this only
        // adds helper fields: `accessibleFromEvent`, `path` and leaves room for `joinHint`.
        foreach ($aliases as $k => &$v) {
            // accessibleFromEvent: true when a runtime `value` is provided (callable) or when a relation `field` exists
            // is_callable() on a possibly-missing entry: static analysis can be noisy here
            $hasValueCallable = is_callable($v['value'] ?? null);
            $v['accessibleFromEvent'] = $hasValueCallable || array_key_exists('field', $v);

            // Build a normalized `path` array for traversal (prefer explicit subfield)
            $path = [];
            if (is_string($v['field'] ?? null)) {
                $path[] = $v['field'];
                if (is_string($v['subfield'] ?? null)) {
                    $path[] = $v['subfield'];
                }
            } elseif (is_string($v['subfield'] ?? null)) {
                // rare case: subfield without field — keep it as single path element
                $path[] = $v['subfield'];
            }
            $v['path'] = $path;

            // Provide joinHint for common aliases to make DB-aggregate generation deterministic
            if ('player' === $k) {
                $v['joinHint'] = ['player'];
            } elseif ('team' === $k) {
                $v['joinHint'] = ['team'];
            } elseif ('surfaceType' === $k) {
                // surfaceType is reachable via game -> location -> surfaceType
                $v['joinHint'] = ['game', 'location', 'surfaceType'];
            } elseif ('homeTeam' === $k) {
                // Home/Away team are reachable via the game relation
                $v['joinHint'] = ['game', 'homeTeam'];
            } elseif ('awayTeam' === $k) {
                $v['joinHint'] = ['game', 'awayTeam'];
            } elseif ('gameDate' === $k) {
                // gameDate is reachable via game -> calendarEvent (label field is startDate)
                $v['joinHint'] = ['game', 'calendarEvent'];
            } elseif ('playerFirstName' === $k) {
                $v['joinHint'] = ['player', 'firstName'];
            } elseif ('playerLastName' === $k) {
                $v['joinHint'] = ['player', 'lastName'];
            } else {
                // Ensure joinHint exists (may be null) so callers can check for it
                // @phpstan-ignore-next-line - joinHint presence is initialized above for common aliases
                if (!array_key_exists('joinHint', $v)) {
                    $v['joinHint'] = null;
                }
            }
        }
        unset($v);

        return $aliases;
    }
}
