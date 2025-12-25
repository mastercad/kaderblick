<?php

namespace App\Enum;

enum CalendarEventVisibility: string
{
    case PRIVATE = 'private';      // Nur Creator
    case SELF = 'self';            // Nur zugewiesener User (für Tasks)
    case TEAM = 'team';            // Nur Team-Mitglieder
    case CLUB = 'club';            // Nur Club-Mitglieder
    case PUBLIC = 'public';        // Alle

    public function label(): string
    {
        return match ($this) {
            self::PRIVATE => 'Privat',
            self::SELF => 'Nur Zugewiesener',
            self::TEAM => 'Team',
            self::CLUB => 'Verein',
            self::PUBLIC => 'Öffentlich',
        };
    }
}
