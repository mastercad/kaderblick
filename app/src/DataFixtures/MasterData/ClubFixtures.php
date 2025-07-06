<?php

namespace App\DataFixtures\MasterData;

use App\Entity\Club;
use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

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
    }
}
