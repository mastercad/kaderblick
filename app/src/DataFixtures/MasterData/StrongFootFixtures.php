<?php

namespace App\DataFixtures\MasterData;

use App\Entity\StrongFoot;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class StrongFootFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $feet = [
            ['code' => 'left', 'name' => 'Links'],
            ['code' => 'right', 'name' => 'Rechts'],
            ['code' => 'both', 'name' => 'Beidfüßig'],
        ];

        foreach ($feet as $foot) {
            $strongFoot = new StrongFoot();
            $strongFoot->setCode($foot['code']);
            $strongFoot->setName($foot['name']);
            $manager->persist($strongFoot);

            $this->addReference('strong_foot_' . strtolower($strongFoot->getCode()), $strongFoot);
        }

        $manager->flush();
        $manager->clear();
    }
}
