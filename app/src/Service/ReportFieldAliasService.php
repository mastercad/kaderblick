<?php

namespace App\Service;

class ReportFieldAliasService
{
    /**
     * Returns a list of user-friendly report field aliases and their mapping to entity fields.
     * This can be extended for more entities and relations.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function fieldAliases(): array
    {
        return [
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
                'entity' => 'GameEvent',
                'field' => 'gameEventType',
                'type' => 'relation',
                'subfield' => 'name', // GameEventType entity
            ],
            'minute' => [
                'label' => 'Minute',
                'entity' => 'GameEvent',
                'field' => 'timestamp',
                'type' => 'datetime',
                'format' => 'i', // Minute aus Zeitstempel
            ],
            'description' => [
                'label' => 'Beschreibung',
                'entity' => 'GameEvent',
                'field' => 'description',
                'type' => 'string',
            ],
            'gameDate' => [
                'label' => 'Spieldatum',
                'value' => static function ($event) {
                    if (method_exists($event, 'getGame') && $event->getGame() && method_exists($event->getGame(), 'getCalendarEvent')) {
                        $calendarEvent = $event->getGame()->getCalendarEvent();
                        if ($calendarEvent && method_exists($calendarEvent, 'getStartDate')) {
                            $date = $calendarEvent->getStartDate();
                            if ($date instanceof \DateTimeInterface) {
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
            'eventType' => [
                'label' => 'Ereignistyp'
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
            // Add more as needed ...
        ];
    }
}
