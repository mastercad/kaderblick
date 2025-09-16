<?php

namespace App\DataFixtures\MasterData;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $existing = $manager->getRepository(User::class)->findOneBy([
            'email' => 'andreas.kempe@byte-artist.de',
        ]);
        if (!$existing) {
            $user = new User();
            $user->setEmail('andreas.kempe@byte-artist.de')
                ->setFirstName('Andreas')
                ->setLastName('Kempe')
                ->setRoles(['ROLE_SUPERADMIN'])
                ->setPassword('$2y$13$dp0os8m.w398LvveRtUKwO0sQkKMXfKDyclEK1X0ZpVkXFqwQtmkG')
                ->setIsVerified(true)
                ->setIsEnabled(true);
            $manager->persist($user);
        }

        $manager->flush();
        $manager->clear();
    }
}
