<?php

namespace App\DataFixtures\MasterData;

use App\Entity\GameEventType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class GameEventTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $eventTypes = [
            [
                'name' => 'Tor',
                'code' => 'goal',
                'color' => '#28a745',
                'icon' => 'fas fa-futbol',
                'isSystem' => true
            ],
            [
                'name' => 'Eigentor',
                'code' => 'own_goal',
                'color' => '#dc3545',
                'icon' => 'fas fa-futbol',
                'isSystem' => true
            ],
            [
                'name' => 'Gelbe Karte',
                'code' => 'yellow_card',
                'color' => '#ffc107',
                'icon' => 'fas fa-square',
                'isSystem' => true
            ],
            [
                'name' => 'Rote Karte',
                'code' => 'red_card',
                'color' => '#dc3545',
                'icon' => 'fas fa-square',
                'isSystem' => true
            ],
            [
                'name' => 'Gelb-Rote Karte',
                'code' => 'yellow_red_card',
                'color' => '#fd7e14',
                'icon' => 'fas fa-square',
                'isSystem' => true
            ],
            [
                'name' => 'Einwechslung',
                'code' => 'substitution_in',
                'color' => '#28a745',
                'icon' => 'fas fa-arrow-right',
                'isSystem' => true
            ],
            [
                'name' => 'Auswechslung',
                'code' => 'substitution_out',
                'color' => '#dc3545',
                'icon' => 'fas fa-arrow-left',
                'isSystem' => true
            ],
        ];

        foreach ($eventTypes as $type) {
            $existing = $manager->getRepository(GameEventType::class)
                ->findOneBy(['name' => $type['name'], 'code' => $type['code']]);
            if (!$existing) {
                $eventType = new GameEventType();
                $eventType->setName($type['name']);
                $eventType->setCode($type['code']);
                $eventType->setColor($type['color']);
                $eventType->setIcon($type['icon']);
                $eventType->setSystem($type['isSystem']);
                $manager->persist($eventType);
                $this->addReference(
                    'game_event_type_' . strtolower(str_replace(['-', ' '], '_', $type['name'])),
                    $eventType
                );
            }
        }

        $manager->flush();
        $manager->clear();
    }
}
