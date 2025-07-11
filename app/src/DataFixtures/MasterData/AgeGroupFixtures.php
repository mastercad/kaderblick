<?php

namespace App\DataFixtures\MasterData;

use App\Entity\AgeGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AgeGroupFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $groups = [
            [
                'code' => 'G_JUNIOREN',
                'name' => 'G-Junioren',
                'englishName' => 'U7',
                'minAge' => 0,
                'maxAge' => 6,
                'referenceDate' => '01-01',
                'description' => 'Bambini (unter 7 Jahre)'
            ],
            [
                'code' => 'F_JUNIOREN',
                'name' => 'F-Junioren',
                'englishName' => 'U9',
                'minAge' => 7,
                'maxAge' => 8,
                'referenceDate' => '01-01',
                'description' => 'F-Junioren (unter 9 Jahre)'
            ],
            [
                'code' => 'E_JUNIOREN',
                'name' => 'E-Junioren',
                'englishName' => 'U11',
                'minAge' => 9,
                'maxAge' => 10,
                'referenceDate' => '01-01',
                'description' => 'E-Junioren (unter 11 Jahre)'
            ],
            [
                'code' => 'D_JUNIOREN',
                'name' => 'D-Junioren',
                'englishName' => 'U13',
                'minAge' => 11,
                'maxAge' => 12,
                'referenceDate' => '01-01',
                'description' => 'D-Junioren (unter 13 Jahre)'
            ],
            [
                'code' => 'C_JUNIOREN',
                'name' => 'C-Junioren',
                'englishName' => 'U15',
                'minAge' => 13,
                'maxAge' => 14,
                'referenceDate' => '01-01',
                'description' => 'C-Junioren (unter 15 Jahre)'
            ],
            [
                'code' => 'B_JUNIOREN',
                'name' => 'B-Junioren',
                'englishName' => 'U17',
                'minAge' => 15,
                'maxAge' => 16,
                'referenceDate' => '01-01',
                'description' => 'B-Junioren (unter 17 Jahre)'
            ],
            [
                'code' => 'A_JUNIOREN',
                'name' => 'A-Junioren',
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

            $this->addReference('age_group_' . strtolower($group['code']), $ageGroup);
        }

        $manager->flush();
        $manager->clear();
    }
}
