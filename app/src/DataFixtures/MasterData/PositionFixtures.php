<?php

namespace App\DataFixtures\MasterData;

use App\Entity\Position;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PositionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $positions = [
            ['name' => 'Torwart', 'shortName' => 'TW', 'description' => 'Hütet das Tor'],
            ['name' => 'Innenverteidiger', 'shortName' => 'IV', 'description' => 'Zentraler Abwehrspieler'],
            ['name' => 'Rechtsverteidiger', 'shortName' => 'RV', 'description' => 'Rechter Abwehrspieler'],
            ['name' => 'Linksverteidiger', 'shortName' => 'LV', 'description' => 'Linker Abwehrspieler'],
            ['name' => 'Defensives Mittelfeld', 'shortName' => 'DM', 'description' => 'Defensiver Mittelfeldspieler'],
            ['name' => 'Zentrales Mittelfeld', 'shortName' => 'ZM', 'description' => 'Zentraler Mittelfeldspieler'],
            ['name' => 'Offensives Mittelfeld', 'shortName' => 'OM', 'description' => 'Offensiver Mittelfeldspieler'],
            ['name' => 'Rechtes Mittelfeld', 'shortName' => 'RM', 'description' => 'Rechter Mittelfeldspieler'],
            ['name' => 'Linkes Mittelfeld', 'shortName' => 'LM', 'description' => 'Linker Mittelfeldspieler'],
            ['name' => 'Rechtsaußen', 'shortName' => 'RA', 'description' => 'Rechter Flügelspieler'],
            ['name' => 'Linksaußen', 'shortName' => 'LA', 'description' => 'Linker Flügelspieler'],
            ['name' => 'Stürmer', 'shortName' => 'ST', 'description' => 'Angriffsspieler']
        ];

        foreach ($positions as $pos) {
            $position = new Position();
            $position->setName($pos['name']);
            $position->setShortName($pos['shortName']);
            $position->setDescription($pos['description']);
            $manager->persist($position);
        }

        $manager->flush();
        $manager->clear();
    }
}
