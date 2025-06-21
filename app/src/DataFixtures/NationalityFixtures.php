<?php

namespace App\DataFixtures;

use App\Entity\Nationality;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class NationalityFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $nationalities = [
            [
                'name' => 'Deutschland',
                'isoCode' => 'DE'
            ],
            [
                'name' => 'Österreich',
                'isoCode' => 'AT'
            ],
        ];

        foreach ($nationalities as $nationality) {
            $nationalityEntity = new Nationality();
            $nationalityEntity->setName($nationality['name']);
            $nationalityEntity->setIsoCode($nationality['isoCode']);

            $manager->persist($nationalityEntity);
        }

        $manager->flush();
        $manager->clear();
    }
}
