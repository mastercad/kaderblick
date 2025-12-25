<?php

namespace App\Enum;

enum CalendarEventPermissionType: string
{
    case USER = 'user';
    case TEAM = 'team';
    case CLUB = 'club';
    case PUBLIC = 'public';

    public function label(): string
    {
        return match ($this) {
            self::USER => 'Benutzer',
            self::TEAM => 'Team',
            self::CLUB => 'Verein',
            self::PUBLIC => 'Öffentlich',
        };
    }
}
