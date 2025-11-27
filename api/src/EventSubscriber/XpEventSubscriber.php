<?php

namespace App\EventSubscriber;

use App\Event\CalendarEventParticipatedEvent;
use App\Event\GameEventCreatedEvent;
use App\Event\GoalAssistedEvent;
use App\Event\GoalScoredEvent;
use App\Event\ProfileCompletenessReachedEvent;
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
            GoalScoredEvent::class => 'onGoalScored',
            GoalAssistedEvent::class => 'onGoalAssisted',
            ProfileCompletenessReachedEvent::class => 'onProfileCompletenessReached',
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

    public function onGoalScored(GoalScoredEvent $event): void
    {
        $user = $event->getUser();
        $this->xpRegistrationService->registerXpEvent($user, 'goal_scored', $event->getGoal()->getId());
    }

    public function onGoalAssisted(GoalAssistedEvent $event): void
    {
        $user = $event->getUser();
        $this->xpRegistrationService->registerXpEvent($user, 'goal_assisted', $event->getGoal()->getId());
    }

    public function onProfileCompletenessReached(ProfileCompletenessReachedEvent $event): void
    {
        $user = $event->getUser();
        $milestone = $event->getMilestone();
        $actionType = 'profile_completion_' . $milestone;
        $this->xpRegistrationService->registerXpEvent($user, $actionType, $user->getId());
    }
}
