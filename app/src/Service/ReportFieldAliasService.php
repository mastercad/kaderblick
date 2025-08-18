<?php

namespace App\Service;

class ReportFieldAliasService
{
    /**
     * Returns a list of user-friendly report field aliases and their mapping to entity fields.
     * This can be extended for more entities and relations.
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
            // Game fields
            'gameDate' => [
                'label' => 'Spieldatum',
                'entity' => 'Game',
                'field' => 'date',
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
            // Add more as needed ...
        ];
    }
}
