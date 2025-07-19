<?php

namespace App\DataFixtures\TestData;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            PlayerFixtures::class,
        ];
    }

    /** @return list<string> */
    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        for ($userCount = 1; $userCount <= 20; $userCount++) {
            $user = new User();
            $user->setFirstName('testuser');
            $user->setLastName((string)$userCount);
            $user->setEmail('user' . $userCount . '@example.com');
            $user->setPassword('password');

            $manager->persist($user);

            // Add reference for each user
            $this->addReference('user_' . $userCount, $user);
        }

        $manager->flush();
    }
}
