<?php

namespace App\DataFixtures;

use App\Entity\StrongFoot;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StrongFootFixtures extends Fixture
{
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
        }

        $manager->flush();
        $manager->clear();
    }
}
