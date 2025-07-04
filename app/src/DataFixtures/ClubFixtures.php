<?php

namespace App\DataFixtures;

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
            LocationFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $wurgwitz = $manager->getRepository(Location::class)->findOneBy(['name' => 'Wurgwitz']);
        $braunsdorf = $manager->getRepository(Location::class)->findOneBy(['name' => 'Braunsdorf']);

        $clubs = [
            [
                'name' => 'Sportgemeinschaft Wurgwitz e.V.',
                'stadiumName' => 'Sportplatz Wurgwitz',
                'city' => 'Wurgwitz',
                'country' => 'Deutschland',
                'location' => $wurgwitz
            ],
            [
                'name' => 'SG90 Braunsdorf e.V.',
                'stadiumName' => 'Sportplatz Braunsdorf',
                'city' => 'Braunsdorf',
                'country' => 'Deutschland',
                'location' => $braunsdorf
            ],
        ];

        foreach ($clubs as $club) {
            $clubEntity = new Club();
            $clubEntity->setName($club['name']);
            $clubEntity->setCity($club['city']);
            $clubEntity->setCountry($club['country']);
            $clubEntity->setLocation($club['location']);
            $clubEntity->setStadiumName($club['stadiumName']);

            $manager->persist($clubEntity);
        }

        $manager->flush();
        $manager->clear();

        //        $this->createFakeData($manager);
    }

    private function createFakeData($manager): void
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
