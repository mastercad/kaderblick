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
        $locations = [
            [
                'name' => 'Location 1',
                'address' => 'Location 1 Street',
                'city' => 'Location 1 City',
                'capacity' => 100,
                'hasFloodlight' => true,
                'facilities' => 'Duschen, Umkleide, Toiletten, Bar',
                'ref' => 'location1',
            ],
            [
                'name' => 'Location 2',
                'address' => 'Location 2 Street',
                'city' => 'Location 2 City',
                'capacity' => 150,
                'hasFloodlight' => true,
                'facilities' => 'Umkleide',
                'ref' => 'location2',
            ],
            [
                'name' => 'Location 3',
                'address' => 'Location 3 Street',
                'city' => 'Location 3 City',
                'capacity' => 200,
                'hasFloodlight' => false,
                'facilities' => 'Toiletten',
                'ref' => 'location3',
            ],
            [
                'name' => 'Location 4',
                'address' => 'Location 4 Street',
                'city' => 'Location 4 City',
                'capacity' => 250,
                'hasFloodlight' => false,
                'facilities' => '',
                'ref' => 'location4',
            ],
        ];

        foreach ($locations as $loc) {
            $existing = $manager->getRepository(Location::class)->findOneBy([
                'name' => $loc['name'],
                'address' => $loc['address'],
                'city' => $loc['city'],
            ]);
            if ($existing) {
                $this->setReference($loc['ref'], $existing);
                continue;
            }
            $location = new Location();
            $location->setName($loc['name']);
            $location->setAddress($loc['address']);
            $location->setCity($loc['city']);
            $location->setCapacity($loc['capacity']);
            $location->setHasFloodlight($loc['hasFloodlight']);
            $location->setFacilities($loc['facilities']);
            $manager->persist($location);
            $this->setReference($loc['ref'], $location);
        }

        $manager->flush();
    }
}
