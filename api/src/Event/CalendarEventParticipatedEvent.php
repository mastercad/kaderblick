<?php

namespace App\Event;

use App\Entity\CalendarEvent;
use App\Entity\User;

final class CalendarEventParticipatedEvent
{
    public function __construct(private User $user, private CalendarEvent $calendarEvent)
    {
    }
    
    public function getUser(): User
    {
        return $this->user;
    }

    public function getCalendarEvent(): CalendarEvent
    {
        return $this->calendarEvent;
    }
}
