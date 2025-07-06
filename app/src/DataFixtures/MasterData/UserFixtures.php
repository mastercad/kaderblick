<?php

namespace App\DataFixtures\MasterData;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('andreas.kempe@byte-artist.de')
            ->setFirstName('Anderas')
            ->setLastName('Kempe')
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setPassword('$2y$13$dp0os8m.w398LvveRtUKwO0sQkKMXfKDyclEK1X0ZpVkXFqwQtmkG')
            ->setIsVerified(true)
            ->setIsEnabled(true);

        $manager->persist($user);
        $manager->flush();
        $manager->clear();
    }
}
