<?php

namespace App\DataFixtures\MasterData;

use App\Entity\Permission;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class PermissionFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $permissions = [
            [
                'view_profile',
                'Profil ansehen',
                'Erlaubt das Ansehen des kompletten Profils'
            ],
            [
                'view_medical',
                'Medizinische Daten ansehen',
                'Erlaubt Zugriff auf medizinische Informationen'
            ],
            [
                'view_stats',
                'Statistiken ansehen',
                'Erlaubt das Ansehen von Leistungsstatistiken'
            ],
            [
                'manage_attendance',
                'Anwesenheit verwalten',
                'Erlaubt An-/Abmeldungen zu Terminen'
            ],
            [
                'view_documents',
                'Dokumente ansehen',
                'Erlaubt Zugriff auf hochgeladene Dokumente'
            ],
        ];

        foreach ($permissions as [$identifier, $name, $description]) {
            $permission = new Permission();
            $permission->setIdentifier($identifier);
            $permission->setName($name);
            $permission->setDescription($description);
            $manager->persist($permission);

            $this->addReference('permission_' . $identifier, $permission);
        }

        $manager->flush();
    }
}
