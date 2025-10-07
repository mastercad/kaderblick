<?php

namespace App\EventSubscriber;

use App\Event\CalendarEventParticipatedEvent;
use App\Event\GameEventCreatedEvent;
use App\Event\ProfileUpdatedEvent;
use App\Service\XPRegistrationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class XpEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private XPRegistrationService $xpRegistrationService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEventParticipatedEvent::class => 'onCalendarEventParticipated',
            GameEventCreatedEvent::class => 'onGameEventCreated',
            ProfileUpdatedEvent::class => 'onProfileUpdated',
        ];
    }

    public function onCalendarEventParticipated(CalendarEventParticipatedEvent $event): void
    {
        $user = $event->getUser();
        $this->xpRegistrationService->registerXpEvent($user, 'calendar_event', $event->getCalendarEvent()->getId());
    }

    public function onGameEventCreated(GameEventCreatedEvent $event): void
    {
        $user = $event->getUser();
        $this->xpRegistrationService->registerXpEvent($user, 'game_event', $event->getGameEvent()->getId());
    }

    public function onProfileUpdated(ProfileUpdatedEvent $event): void
    {
        $user = $event->getUser();
        $this->xpRegistrationService->registerXpEvent($user, 'profile_update', $user->getId());
    }
}
