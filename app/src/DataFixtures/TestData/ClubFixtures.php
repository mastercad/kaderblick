<?php

namespace App\DataFixtures\TestData;

use App\Entity\Club;
use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ClubFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            LocationFixtures::class
        ];
    }

    public function load(ObjectManager $manager): void
    {
        for ($clubNumber = 1; $clubNumber <= 4; ++$clubNumber) {
            $club = new Club();
            $club->setName('Club ' . $clubNumber);
            $club->setShortName('C' . $clubNumber);
            $club->setLocation($this->getReference('location' . $clubNumber, Location::class));

            $manager->persist($club);

            $this->addReference('club' . $clubNumber, $club);
        }

        $manager->flush();
    }
}
