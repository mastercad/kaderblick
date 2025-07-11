<?php

namespace App\DataFixtures\TestData;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class LocationFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $location1 = new Location();
        $location1->setName('Location 1');
        $location1->setAddress('Location 1 Street');
        $location1->setCity('Location 1 City');
        $location1->setCapacity(100);
        $location1->setHasFloodlight(true);
        $location1->setFacilities('Duschen, Umkleide, Toiletten, Bar');
        $manager->persist($location1);

        $this->setReference('location1', $location1);

        $location2 = new Location();
        $location2->setName('Location 2');
        $location2->setAddress('Location 2 Street');
        $location2->setCity('Location 2 City');
        $location2->setCapacity(150);
        $location2->setHasFloodlight(true);
        $location2->setFacilities('Umkleide');
        $manager->persist($location2);

        $this->setReference('location2', $location2);

        $location3 = new Location();
        $location3->setName('Location 3');
        $location3->setAddress('Location 3 Street');
        $location3->setCity('Location 3 City');
        $location3->setCapacity(200);
        $location3->setHasFloodlight(false);
        $location3->setFacilities('Toiletten');
        $manager->persist($location3);

        $this->setReference('location3', $location3);

        $location4 = new Location();
        $location4->setName('Location 4');
        $location4->setAddress('Location 4 Street');
        $location4->setCity('Location 4 City');
        $location4->setCapacity(250);
        $location4->setHasFloodlight(false);
        $location4->setFacilities('');
        $manager->persist($location4);

        $this->setReference('location4', $location4);

        $manager->flush();
    }
}
