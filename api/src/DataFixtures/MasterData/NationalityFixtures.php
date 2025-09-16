<?php

namespace App\DataFixtures\MasterData;

use App\Entity\Nationality;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class NationalityFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

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
            $existing = $manager->getRepository(Nationality::class)->findOneBy([
                'name' => $nationality['name'],
                'isoCode' => $nationality['isoCode'],
            ]);
            if ($existing) {
                $nationalityEntity = $existing;
            } else {
                $nationalityEntity = new Nationality();
                $nationalityEntity->setName($nationality['name']);
                $nationalityEntity->setIsoCode($nationality['isoCode']);
                $manager->persist($nationalityEntity);
            }

            $this->addReference(
                'nationality_' . strtolower($nationalityEntity->getName()),
                $nationalityEntity
            );
        }

        $manager->flush();
        $manager->clear();
    }
}
