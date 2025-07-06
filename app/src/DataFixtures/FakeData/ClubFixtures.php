<?php

namespace App\DataFixtures\FakeData;

use App\DataFixtures\FakeData\LocationFixtures as FakeDataLocationFixtures;
use App\Entity\Club;
use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ClubFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            FakeDataLocationFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('de_DE');
        $numClubs = $faker->numberBetween(15, 25);
        $locations = $manager->getRepository(Location::class)->findAll();

        $prefixes = ['FC', 'SV', 'SC', 'TSV', 'VfL', '1. FC', 'SpVgg', 'SG', 'SpG'];
        $suffixes = ['e.V.', '1904', 'United', 'Amateure', 'II', '', 'AK', 'Boys', 'Allstars'];

        $stadionPrefixes = ['Stadion', 'Arena', 'Waldstadion', 'Sportpark', 'Kampfbahn', 'Volkspark', 'Stadion am', 'Arena an der'];

        for ($i = 0; $i < $numClubs; ++$i) {
            $clubEntity = new Club();
            $location = $faker->randomElement($locations);
            $city = $location->getCity();
            $prefix = $faker->randomElement($prefixes);
            $suffix = $faker->randomElement($suffixes);
            $nameParts = array_filter([$prefix, $city, $suffix]);
            $clubName = implode(' ', $nameParts);
            $clubEntity->setName($clubName);
            $clubEntity->setShortName($faker->word);
            $clubEntity->setStadiumName($faker->randomElement($stadionPrefixes) . ' ' . $location->getCity());
            $clubEntity->setLocation($location);

            $manager->persist($clubEntity);
        }

        $manager->flush();
        $manager->clear();
    }
}
