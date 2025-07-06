<?php

namespace App\DataFixtures\MasterData;

use App\Entity\Location;
use App\Entity\SurfaceType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

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
                'city' => 'Braunsdorf',
                'capacity' => 150,
                'hasFloodlight' => false,
                'surfaceType' => $naturrasen
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
    }
}
