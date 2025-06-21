<?php

namespace App\DataFixtures;

use App\Entity\Location;
use App\Entity\SurfaceType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class LocationFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            SurfaceTypeFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $naturrasen = $manager->getRepository(SurfaceType::class)->findOneBy(['name' => 'Naturrasen']);
        $kunstrasen = $manager->getRepository(SurfaceType::class)->findOneBy(['name' => 'Kunstrasen']);

        $types = [
            [
                'name' => 'Wurgwitz',
                'address' => 'Pesterwitzer Straße 6, 01705 Freital',
                'city' => 'Freital-Wurgwitz',
                'capacity' => 220,
                'hasFloodlight' => true,
                'surfaceType' => $kunstrasen
            ],
            [
                'name' => 'Braunsdorf',
                'address' => 'Ernst-Thälmann-Str. 29, 01737 Braunsdorf',
                'city'=> 'Braunsdorf',
                'capacity' => 150,
                'hasFloodlight' => false,
                'surfaceType'=> $naturrasen
            ],
        ];

        foreach ($types as $type) {
            $location = new Location();
            $location->setName($type['name']);
            $location->setAddress($type['address']);
            $location->setCity($type['city']);
            $location->setCapacity($type['capacity']);
            $location->setSurfaceType($type['surfaceType']);
            $location->setHasFloodlight($type['hasFloodlight']);

            $manager->persist($location);
        }

        $manager->flush();
        $manager->clear();

        $this->createFakeData($manager);
    }

    private function createFakeData($manager): void
    {
        $faker = Factory::create('de_DE');
        $numLocations = $faker->numberBetween(50, 150);
        $surfaceTypes = $manager->getRepository(SurfaceType::class)->findAll();
        shuffle($surfaceTypes);
        $usedNames = [];

        for ($i = 0; $i < $numLocations; $i++) {
            $locationEntity = new Location();
            $cityName = $faker->cityName;
            $locationEntity->setName($this->generateUniqueLocationName($cityName, $usedNames, $faker));
            $locationEntity->setAddress($faker->streetAddress);
            $locationEntity->setCity($cityName);
            $locationEntity->setCapacity($faker->numberBetween(250,0));
            $locationEntity->setHasFloodlight($faker->boolean(false));
            $locationEntity->setSurfaceType($faker->randomElement($surfaceTypes));

            $manager->persist($locationEntity);
        }

        $manager->flush();
        $manager->clear();
    }

    function generateUniqueLocationName(string &$cityName, array &$usedNames, Generator $faker): string
    {
        do {
            $cityName = $faker->cityName;
            $nameParts = array_filter([$cityName]);
            $name = implode(' ', $nameParts);
        } while (in_array($name, $usedNames));
    
        $usedNames[] = $name;
        return $name;
    }
}
