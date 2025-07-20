<?php

namespace App\DataFixtures\MasterData;

use App\Entity\League;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class LeagueFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $leagues = [
            // Profi-Ligen
            'Bundesliga',
            '2. Bundesliga',
            '3. Liga',

            // Regionalligen
            'Regionalliga Nord',
            'Regionalliga Nordost',
            'Regionalliga West',
            'Regionalliga Südwest',
            'Regionalliga Bayern',

            // Semi-Professionell
            'Oberliga',
            'Verbandsliga',
            'Landesliga',

            // Amateur-Bereich
            'Bezirksoberliga',
            'Bezirksliga',
            'Kreisliga A',
            'Kreisliga B',
            'Kreisliga C',
            'Kreisklasse A',
            'Kreisklasse B',
            'Kreisklasse C',

            'Regionalliga',
            'Verbandsliga',
            'Bezirksliga',
            'Kreisliga',

            // Frauen Bundesligen
            'Frauen-Bundesliga',
            '2. Frauen-Bundesliga',

            // Frauen Regional
            'Frauen-Regionalliga',
            'Frauen-Verbandsliga',
            'Frauen-Landesliga',
            'Frauen-Bezirksliga',
            'Frauen-Kreisliga'
        ];

        foreach ($leagues as $leagueName) {
            $league = new League();
            $league->setName($leagueName);
            $manager->persist($league);

            $referenceKey = 'league_' . strtolower(
                preg_replace(
                    '/_+/',
                    '_',
                    str_replace(
                        [' ', '-', '.'],
                        ['_', '_', ''],
                        $leagueName
                    )
                )
            );

            $this->addReference($referenceKey, $league);
        }

        $manager->flush();
    }
}
