<?php

namespace App\DataFixtures\TestData;

use App\Entity\User;
use App\Entity\VideoType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class VideoTypeFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'user16@example.com']);

        if (!$user) {
            throw new RuntimeException('User user16@example.com nicht gefunden');
        }

        $videoTypes = [
            ['name' => 'Spielaufzeichnung', 'sort' => 1],
            ['name' => 'Trainingsaufzeichnung', 'sort' => 2],
            ['name' => 'Highlight', 'sort' => 3],
            ['name' => 'Tor', 'sort' => 4],
            ['name' => 'Sonstiges', 'sort' => 5],
        ];

        foreach ($videoTypes as $i => $typeData) {
            $videoType = new VideoType();
            $videoType->setName($typeData['name']);
            $videoType->setSort($typeData['sort']);
            $videoType->setCreatedAt(new DateTimeImmutable());
            $videoType->setUpdatedAt(new DateTimeImmutable());
            $videoType->setCreatedFrom($user);
            $videoType->setUpdatedFrom($user);

            $manager->persist($videoType);
            $this->addReference('video_type_' . $i, $videoType);
        }

        $manager->flush();
    }
}
