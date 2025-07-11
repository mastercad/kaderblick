<?php

namespace App\DataFixtures\MasterData;

use App\Entity\SurfaceType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class SurfaceTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

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

            $this->addReference('surface_type_' . strtolower($surfaceType->getName()), $surfaceType);
        }

        $manager->flush();
        $manager->clear();
    }
}
