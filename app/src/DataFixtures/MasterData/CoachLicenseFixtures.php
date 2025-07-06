<?php

namespace App\DataFixtures\MasterData;

use App\Entity\CoachLicense;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CoachLicenseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $licenses = [
            // Deutschland (DFB)
            ['Kindertrainer-Zertifikat', 'Einsteigerzertifikat für Bambini und G-Jugend.', 'DE'],
            ['Basis-Coach', 'Niedrigste Lizenzstufe, ersetzt teilweise C-Lizenz.', 'DE'],
            ['C-Lizenz', 'Für Breiten- und Jugendfußball im Amateurbereich.', 'DE'],
            ['B-Lizenz', 'Für leistungsorientierten Jugendfußball und untere Ligen.', 'DE'],
            ['A-Lizenz', 'Für höherklassige Amateurmannschaften.', 'DE'],
            ['Fußball-Lehrer', 'Höchste deutsche Lizenzstufe, Voraussetzung für Bundesliga.', 'DE'],

            // UEFA
            ['UEFA C', 'Einsteigerlizenz auf UEFA-Ebene.', 'UEFA'],
            ['UEFA B', 'Zugang zu professioneller Jugendarbeit & untere Erwachsenenligen.', 'UEFA'],
            ['UEFA A', 'Fortgeschrittene Lizenz für nationale Profi-Teams.', 'UEFA'],
            ['UEFA Pro', 'Höchste UEFA-Lizenz, für alle Top-Profiligen.', 'UEFA'],
        ];

        foreach ($licenses as [$name, $description, $region]) {
            $license = new CoachLicense();
            $license->setName($name);
            $license->setDescription($description);
            $license->setCountryCode($region);
            $license->setActive(true);
            $manager->persist($license);
        }

        $manager->flush();
        $manager->clear();
    }
}
