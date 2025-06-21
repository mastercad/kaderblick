<?php

namespace App\DataFixtures;

use App\Entity\League;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LeagueFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $leagues = [
            'Kreisliga A',
            'Kreisliga B',
            'Kreisliga C',
            'Bezirksliga',
            'Landesliga'
        ];

        foreach ($leagues as $leagueName) {
            $league = new League();
            $league->setName($leagueName);
            $manager->persist($league);
            $this->addReference('league_' . strtolower(str_replace(' ', '_', $leagueName)), $league);
        }

        $manager->flush();
    }
}
