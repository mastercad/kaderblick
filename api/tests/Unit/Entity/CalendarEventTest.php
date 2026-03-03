<?php

namespace App\Tests\Unit\Entity;

use App\Entity\CalendarEvent;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CalendarEventTest extends TestCase
{
    public function testCancelledDefaultsToFalse(): void
    {
        $event = new CalendarEvent();

        $this->assertFalse($event->isCancelled());
    }

    public function testSetCancelled(): void
    {
        $event = new CalendarEvent();
        $result = $event->setCancelled(true);

        $this->assertTrue($event->isCancelled());
        $this->assertSame($event, $result, 'setCancelled should return self for fluent API');
    }

    public function testSetCancelledToFalse(): void
    {
        $event = new CalendarEvent();
        $event->setCancelled(true);
        $event->setCancelled(false);

        $this->assertFalse($event->isCancelled());
    }

    public function testCancelReasonDefaultsToNull(): void
    {
        $event = new CalendarEvent();

        $this->assertNull($event->getCancelReason());
    }

    public function testSetCancelReason(): void
    {
        $event = new CalendarEvent();
        $result = $event->setCancelReason('Wetter zu schlecht');

        $this->assertSame('Wetter zu schlecht', $event->getCancelReason());
        $this->assertSame($event, $result);
    }

    public function testSetCancelReasonToNull(): void
    {
        $event = new CalendarEvent();
        $event->setCancelReason('Grund');
        $event->setCancelReason(null);

        $this->assertNull($event->getCancelReason());
    }

    public function testCancelledByDefaultsToNull(): void
    {
        $event = new CalendarEvent();

        $this->assertNull($event->getCancelledBy());
    }

    public function testSetCancelledBy(): void
    {
        $event = new CalendarEvent();
        $user = $this->createMock(User::class);

        $result = $event->setCancelledBy($user);

        $this->assertSame($user, $event->getCancelledBy());
        $this->assertSame($event, $result);
    }

    public function testSetCancelledByToNull(): void
    {
        $event = new CalendarEvent();
        $user = $this->createMock(User::class);
        $event->setCancelledBy($user);
        $event->setCancelledBy(null);

        $this->assertNull($event->getCancelledBy());
    }

    public function testFullCancellationWorkflow(): void
    {
        $event = new CalendarEvent();
        $user = $this->createMock(User::class);

        $event->setCancelled(true);
        $event->setCancelReason('Platzsperrung');
        $event->setCancelledBy($user);

        $this->assertTrue($event->isCancelled());
        $this->assertSame('Platzsperrung', $event->getCancelReason());
        $this->assertSame($user, $event->getCancelledBy());
    }

    public function testPermissionsCollectionInitialized(): void
    {
        $event = new CalendarEvent();

        $this->assertCount(0, $event->getPermissions());
    }
}
