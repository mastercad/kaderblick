<?php

namespace App\DataFixtures\MasterData;

use App\Entity\User;
use App\Entity\VideoType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class VideoTypeFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        // Hole den Master-User (Andreas Kempe)
        $masterUser = $manager->getRepository(User::class)->findOneBy([
            'email' => 'andreas.kempe@kaderblick.de',
        ]);

        if (!$masterUser) {
            throw new RuntimeException('Master user not found. Please run UserFixtures first.');
        }

        $videoTypes = [
            ['name' => 'Vorbereitung', 'sort' => 10],
            ['name' => '1.Halbzeit', 'sort' => 20],
            ['name' => '2.Halbzeit', 'sort' => 30],
            ['name' => 'Nachbereitung', 'sort' => 40],
        ];

        foreach ($videoTypes as $videoTypeData) {
            $existing = $manager->getRepository(VideoType::class)->findOneBy([
                'name' => $videoTypeData['name'],
            ]);

            if ($existing) {
                $videoType = $existing;
                $videoType->setSort($videoTypeData['sort']);
                $videoType->setUpdatedFrom($masterUser);
                $videoType->setUpdatedAt(new DateTimeImmutable());
            } else {
                $videoType = new VideoType();
                $videoType->setName($videoTypeData['name']);
                $videoType->setSort($videoTypeData['sort']);
                $videoType->setCreatedFrom($masterUser);
                $videoType->setUpdatedFrom($masterUser);
                $videoType->setCreatedAt(new DateTimeImmutable());
                $videoType->setUpdatedAt(new DateTimeImmutable());
                $manager->persist($videoType);
            }
        }

        $manager->flush();
        $manager->clear();
    }
}
