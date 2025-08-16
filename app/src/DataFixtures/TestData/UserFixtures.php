<?php

namespace App\DataFixtures\TestData;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private UserPasswordHasherInterface $userPasswordHasherInterface;

    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->userPasswordHasherInterface = $userPasswordHasherInterface;
    }

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
        $roles = [
            [
                'name' => 'ROLE_GUEST',
                'minId' => 0,
                'maxId' => 5,
            ],
            [
                'name' => 'ROLE_USER',
                'minId' => 6,
                'maxId' => 10,
            ],
            [
                'name' => 'ROLE_CLUB',
                'minId' => 11,
                'maxId' => 15,
            ],
            [
                'name' => 'ROLE_ADMIN',
                'minId' => 16,
                'maxId' => 20,
            ],
            [
                'name' => 'ROLE_SUPER_ADMIN',
                'minId' => 21,
                'maxId' => 25,
            ]
        ];

        foreach ($roles as $role) {
            for ($userCount = $role['minId']; $userCount <= $role['maxId']; ++$userCount) {
                $user = new User();
                $user->setFirstName('testuser');
                $user->setLastName((string) $userCount);
                $user->setEmail('user' . $userCount . '@example.com');
                $hashedPassword = $this->userPasswordHasherInterface->hashPassword($user, 'password');
                $user->setPassword($hashedPassword);
                $user->addRole($role['name']);
                $user->setIsEnabled(true);
                $user->setIsVerified(true);
                $manager->persist($user);
                $this->addReference('user_' . $userCount, $user);
            }
        }

        $manager->flush();
    }
}
