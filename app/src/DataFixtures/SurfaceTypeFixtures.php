<?php

namespace App\DataFixtures;

use App\Entity\SurfaceType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SurfaceTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $surfaces = [
            'Naturrasen',
            'Kunstrasen',
            'Hartplatz',
            'Hybridrasen',
            'Asche'
        ];

        foreach ($surfaces as $surface) {
            $surfaceType = new SurfaceType();
            $surfaceType->setName($surface);
            $manager->persist($surfaceType);
        }

        $manager->flush();
        $manager->clear();
    }
}
