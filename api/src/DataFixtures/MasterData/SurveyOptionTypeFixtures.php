<?php

namespace App\DataFixtures\MasterData;

use App\Entity\SurveyOptionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class SurveyOptionTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $types = [
            ['key' => 'single_choice', 'name' => 'Einfachauswahl'],
            ['key' => 'multiple_choice', 'name' => 'Mehrfachauswahl'],
            ['key' => 'text', 'name' => 'Freitext'],
            ['key' => 'scale_1_5', 'name' => 'Skala 1–5'],
            ['key' => 'scale_1_10', 'name' => 'Skala 1–10'],
        ];

        foreach ($types as $typeData) {
            $existing = $manager->getRepository(SurveyOptionType::class)->findOneBy(['typeKey' => $typeData['key']]);
            if (!$existing) {
                $type = new SurveyOptionType();
                $type->setTypeKey($typeData['key']);
                $type->setName($typeData['name']);
                $manager->persist($type);
            } else {
                $existing->setName($typeData['name']);
            }
        }

        $manager->flush();
    }
}
