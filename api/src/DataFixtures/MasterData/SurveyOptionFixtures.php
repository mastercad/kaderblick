<?php

namespace App\DataFixtures\MasterData;

use App\Entity\SurveyOption;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class SurveyOptionFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        // Beispiel: Master-Optionen für verschiedene Fragetypen
        $optionValues = [
            'Ja',
            'Nein',
            'Vielleicht',
            'Weiß nicht',
            'Stimme zu',
            'Stimme nicht zu',
            'Keine Meinung',
        ];

        foreach ($optionValues as $value) {
            $existing = $manager->getRepository(SurveyOption::class)
                ->findOneBy(['optionText' => $value]);
            if (!$existing) {
                $option = new SurveyOption();
                $option->setOptionText($value);
                $manager->persist($option);
            }
        }

        $manager->flush();
    }
}
