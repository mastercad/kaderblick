<?php

namespace App\DataFixtures\MasterData;

use App\Entity\FormationType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class FormationTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $formationTypes = [
            [
                'name' => 'fußball',
                'background' => 'fussballfeld_haelfte.jpg'
            ],
            [
                'name' => 'handball',
                'background' => ''
            ],
            [
                'name' => 'basketball',
                'background' => ''
            ],
            [
                'name' => 'volleyball',
                'background' => 'volleyball_haelfte.jpg'
            ],
            [
                'name' => 'eishockey',
                'background' => ''
            ],
            [
                'name' => 'american football',
                'background' => ''
            ],
            [
                'name' => 'rugby',
                'background' => ''
            ],
            [
                'name' => 'lacrosse',
                'background' => ''
            ],
            [
                'name' => 'cricket',
                'background' => ''
            ],
            [
                'name' => 'hockey',
                'background' => ''
            ],
            [
                'name' => 'futsal',
                'background' => ''
            ],
            [
                'name' => 'tischtennis',
                'background' => ''
            ],
            [
                'name' => 'badminton',
                'background' => ''
            ],
            [
                'name' => 'baseball',
                'background' => ''
            ],
            [
                'name' => 'softball',
                'background' => ''
            ],
            [
                'name' => 'waterpolo',
                'background' => ''
            ],
            [
                'name' => 'handball',
                'background' => ''
            ],
            [
                'name' => 'korfball',
                'background' => ''
            ],
            [
                'name' => 'ultimate frisbee',
                'background' => ''
            ],
            [
                'name' => 'golf',
                'background' => ''
            ],
            [
                'name' => 'e-sport',
                'background' => ''
            ],
            [
                'name' => 'andere',
                'background' => ''
            ]
        ];

        foreach ($formationTypes as $formationType) {
            $formation = new FormationType();
            $formation->setName($formationType['name']);
            $formation->setBackgroundPath($formationType['background']);

            $manager->persist($formation);

            $this->addReference('formation_type_' . strtolower($formationType['name']), $formation);
        }

        $manager->flush();
    }
}
