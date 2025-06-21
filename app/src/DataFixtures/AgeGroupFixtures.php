<?php

namespace App\DataFixtures;

use App\Entity\AgeGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AgeGroupFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $groups = [
            [
                'code' => 'G_JUGEND',
                'name' => 'G-Jugend',
                'englishName' => 'U7',
                'minAge' => 0,
                'maxAge' => 6,
                'referenceDate' => '01-01',
                'description' => 'Bambini (unter 7 Jahre)'
            ],
            [
                'code' => 'F_JUGEND',
                'name' => 'F-Jugend',
                'englishName' => 'U9',
                'minAge' => 7,
                'maxAge' => 8,
                'referenceDate' => '01-01',
                'description' => 'F-Junioren (unter 9 Jahre)'
            ],
            [
                'code' => 'E_JUGEND',
                'name' => 'E-Jugend',
                'englishName' => 'U11',
                'minAge' => 9,
                'maxAge' => 10,
                'referenceDate' => '01-01',
                'description' => 'E-Junioren (unter 11 Jahre)'
            ],
            [
                'code' => 'D_JUGEND',
                'name' => 'D-Jugend',
                'englishName' => 'U13',
                'minAge' => 11,
                'maxAge' => 12,
                'referenceDate' => '01-01',
                'description' => 'D-Junioren (unter 13 Jahre)'
            ],
            [
                'code' => 'C_JUGEND',
                'name' => 'C-Jugend',
                'englishName' => 'U15',
                'minAge' => 13,
                'maxAge' => 14,
                'referenceDate' => '01-01',
                'description' => 'C-Junioren (unter 15 Jahre)'
            ],
            [
                'code' => 'B_JUGEND',
                'name' => 'B-Jugend',
                'englishName' => 'U17',
                'minAge' => 15,
                'maxAge' => 16,
                'referenceDate' => '01-01',
                'description' => 'B-Junioren (unter 17 Jahre)'
            ],
            [
                'code' => 'A_JUGEND',
                'name' => 'A-Jugend',
                'englishName' => 'U19',
                'minAge' => 17,
                'maxAge' => 18,
                'referenceDate' => '01-01',
                'description' => 'A-Junioren (unter 19 Jahre)'
            ],
            [
                'code' => 'SENIOREN',
                'name' => 'Senioren',
                'englishName' => 'Senior',
                'minAge' => 19,
                'maxAge' => 99,
                'referenceDate' => '01-01',
                'description' => 'Seniorenmannschaft'
            ],
        ];

        foreach ($groups as $group) {
            $ageGroup = new AgeGroup();
            $ageGroup->setCode($group['code']);
            $ageGroup->setName($group['name']);
            $ageGroup->setEnglishName($group['englishName']);
            $ageGroup->setMinAge($group['minAge']);
            $ageGroup->setMaxAge($group['maxAge']);
            $ageGroup->setReferenceDate($group['referenceDate']);
            $ageGroup->setDescription($group['description']);
            $manager->persist($ageGroup);
        }

        $manager->flush();
        $manager->clear();
    }
}
