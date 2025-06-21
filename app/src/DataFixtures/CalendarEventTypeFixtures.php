<?php

namespace App\DataFixtures;

use App\Entity\CalendarEventType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CalendarEventTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $types = [
            ['name' => 'Spiel', 'color' => '#1a4789'],
            ['name' => 'Training', 'color' => '#28a745'],
            ['name' => 'Vereinstreffen', 'color' => '#ffc107'],
            ['name' => 'Event', 'color' => '#dc3545']
        ];

        foreach ($types as $type) {
            $eventType = new CalendarEventType();
            $eventType->setName($type['name']);
            $eventType->setColor($type['color']);
            $manager->persist($eventType);
        }

        $manager->flush();
    }
}
