<?php

namespace App\DataFixtures\MasterData;

use App\Entity\Coach;
use App\Entity\CoachLicense;
use App\Entity\CoachLicenseAssignment;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class CoachFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            CoachLicenseFixtures::class,
            CoachTeamAssignmentTypeFixtures::class,
            ClubFixtures::class,
            TeamFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // Beispiel: Lizenzen schon in DB oder per Fixture vorab angelegt
        // Hier Beispielhafte Abfrage (du kannst das anders lösen)
        $licenseA = $manager->getRepository(CoachLicense::class)->findOneBy(['name' => 'UEFA A']);
        $licenseB = $manager->getRepository(CoachLicense::class)->findOneBy(['name' => 'UEFA B']);

        if (!$licenseA || !$licenseB) {
            throw new RuntimeException('Benötigte Lizenzen (UEFA A und B) sind nicht vorhanden. Bitte zuerst CoachLicenseFixtures laden.');
        }

        // Coach 1 anlegen
        $coach1 = new Coach();
        $coach1->setFirstName('Max');
        $coach1->setLastName('Mustermann');
        $coach1->setBirthDate(new DateTime('1980-05-15'));
        // ...weitere Felder setzen falls vorhanden

        $manager->persist($coach1);

        // Lizenzzuweisung Coach 1
        $assign1 = new CoachLicenseAssignment();
        $assign1->setCoach($coach1);
        $assign1->setLicense($licenseA);
        $assign1->setStartDate(new DateTime('2015-01-01'));
        $assign1->setEndDate(null); // unbefristet gültig

        $manager->persist($assign1);

        // Coach 2 anlegen
        $coach2 = new Coach();
        $coach2->setFirstName('Julia');
        $coach2->setLastName('Schmidt');
        $coach2->setBirthDate(new DateTime('1990-11-20'));

        $manager->persist($coach2);

        // Mehrere Lizenzzuweisungen Coach 2
        $assign2a = new CoachLicenseAssignment();
        $assign2a->setCoach($coach2);
        $assign2a->setLicense($licenseB);
        $assign2a->setStartDate(new DateTime('2018-03-15'));
        $assign2a->setEndDate(new DateTime('2023-03-14')); // abgelaufen

        $manager->persist($assign2a);

        $assign2b = new CoachLicenseAssignment();
        $assign2b->setCoach($coach2);
        $assign2b->setLicense($licenseA);
        $assign2b->setStartDate(new DateTime('2023-03-15'));
        $assign2b->setEndDate(null); // aktuell gültig

        $manager->persist($assign2b);
        $manager->flush();
        $manager->clear();
    }
}
