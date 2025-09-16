<?php

namespace App\DataFixtures\MasterData;

use App\Entity\CalendarEventType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CalendarEventTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $types = [
            ['name' => 'Spiel', 'color' => '#1a4789'],
            ['name' => 'Training', 'color' => '#28a745'],
            ['name' => 'Vereinstreffen', 'color' => '#ffc107'],
            ['name' => 'Event', 'color' => '#dc3545']
        ];

        foreach ($types as $type) {
            $existing = $manager->getRepository(CalendarEventType::class)->findOneBy([
                'name' => $type['name'],
            ]);
            if ($existing) {
                $eventType = $existing;
            } else {
                $eventType = new CalendarEventType();
                $eventType->setName($type['name']);
                $eventType->setColor($type['color']);
                $manager->persist($eventType);
            }
            $this->addReference('calendar_event_type_' . strtolower($eventType->getName()), $eventType);
        }

        $manager->flush();
    }
}
