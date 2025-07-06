<?php

namespace App\DataFixtures\FakeData;

use App\DataFixtures\MasterData\SurfaceTypeFixtures;
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
        /** @var Generator $faker */
        $faker = Factory::create('de_DE');
        $numLocations = $faker->numberBetween(50, 150);
        $surfaceTypes = $manager->getRepository(SurfaceType::class)->findAll();
        shuffle($surfaceTypes);
        /** @var array<string> $usedNames */
        $usedNames = [];

        for ($i = 0; $i < $numLocations; ++$i) {
            $locationEntity = new Location();
            $cityName = $faker->city;
            $locationEntity->setName($this->generateUniqueLocationName($cityName, $usedNames, $faker));
            $locationEntity->setAddress($faker->streetAddress);
            $locationEntity->setCity($cityName);
            $locationEntity->setCapacity($faker->numberBetween(250, 0));
            $locationEntity->setHasFloodlight($faker->boolean(false));
            $locationEntity->setSurfaceType($faker->randomElement($surfaceTypes));

            $manager->persist($locationEntity);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param array<string> $usedNames
     */
    private function generateUniqueLocationName(string &$cityName, array &$usedNames, Generator $faker): string
    {
        do {
            $cityName = $faker->city;
            $nameParts = array_filter([$cityName]);
            $name = implode(' ', $nameParts);
        } while (in_array($name, $usedNames));

        $usedNames[] = $name;

        return $name;
    }
}
