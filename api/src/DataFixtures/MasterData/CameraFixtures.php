<?php

namespace App\DataFixtures\MasterData;

use App\Entity\Camera;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CameraFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
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
            'email' => 'andreas.kempe@byte-artist.de',
        ]);

        if (!$masterUser) {
            throw new \RuntimeException('Master user not found. Please run UserFixtures first.');
        }

        $cameras = [
            'DJI Osmo Action 5 Pro',
            'DJI MINI 2 SE',
        ];

        foreach ($cameras as $cameraName) {
            $existing = $manager->getRepository(Camera::class)->findOneBy([
                'name' => $cameraName,
            ]);

            if ($existing) {
                $camera = $existing;
                $camera->setUpdatedFrom($masterUser);
                $camera->setUpdatedAt(new DateTimeImmutable());
            } else {
                $camera = new Camera();
                $camera->setName($cameraName);
                $camera->setCreatedFrom($masterUser);
                $camera->setCreatedAt(new DateTimeImmutable());
                $manager->persist($camera);
            }
        }

        $manager->flush();
        $manager->clear();
    }
}
