<?php

namespace App\DataFixtures\MasterData;

use App\Entity\RelationType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RelationTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $types = [
            // Player Relations
            ['parent', 'Elternteil', 'player'],
            ['sibling', 'Geschwister', 'player'],
            ['relative', 'Verwandter', 'player'],
            ['guardian', 'Erziehungsberechtigter', 'player'],
            ['friend', 'Freund', 'player'],
            ['self_player', 'Spieler selbst', 'player'],

            // Coach Relations
            ['assistant', 'Assistent', 'coach'],
            ['observer', 'Beobachter', 'coach'],
            ['substitute', 'Vertretung', 'coach'],
            ['mentor', 'Mentor', 'coach'],
            ['self_coach', 'Trainer selbst', 'coach']
        ];

        foreach ($types as [$identifier, $name, $category]) {
            $type = new RelationType();
            $type->setIdentifier($identifier);
            $type->setName($name);
            $type->setCategory($category);
            $manager->persist($type);

            $this->addReference('relation_type_' . $identifier, $type);
        }

        $manager->flush();
    }
}
