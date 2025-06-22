<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\Coach;
use App\Entity\CoachClubAssignment;
use App\Entity\CoachLicense;
use App\Entity\CoachLicenseAssignment;
use App\Entity\CoachTeamAssignment;
use App\Entity\CoachTeamAssignmentType;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
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
        return;

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

//        $this->generateFakeData($manager);
    }

    private function generateFakeData(ObjectManager $manager): void
    {
        $faker = Factory::create('de_DE');
        $coachCount = $faker->numberBetween(100, 250);
        $licenses = $manager->getRepository(CoachLicense::class)->findAll();
        $clubs = $manager->getRepository(Club::class)->findAll();
        $teams = $manager->getRepository(Team::class)->findAll();
        $coachTeamAssignmentTypes = $manager->getRepository(CoachTeamAssignmentType::class)->findAll();

        shuffle($licenses);
        shuffle($clubs);
        shuffle($teams);
        shuffle($coachTeamAssignmentTypes);

        for ($i = 0; $i < $coachCount; $i++) {
            $coachEntity = new Coach();
            $coachEntity->setFirstName($faker->firstName);
            $coachEntity->setLastName($faker->lastName);
            $coachEntity->setBirthdate($faker->dateTimeBetween('-70 years', '-15 years'));

            $licenseCount = $faker->numberBetween(0, 5);
            $currentLicenses = array_slice($licenses, 0, $licenseCount);

            foreach ($currentLicenses as $currentLicense) {
                $coachLicenseAssignmentEntity = new CoachLicenseAssignment();
                $coachLicenseAssignmentEntity->setCoach($coachEntity);
                $coachLicenseAssignmentEntity->setLicense($currentLicense);
                $coachLicenseAssignmentEntity->setStartDate($faker->dateTimeBetween('-15 years', '-7 years'));
                $coachLicenseAssignmentEntity->setEndDate($faker->dateTimeBetween('-10 years', null));
                $coachEntity->addCoachLicenseAssignment($coachLicenseAssignmentEntity);

                $manager->persist($coachLicenseAssignmentEntity);
            }

            $coachClubAssignmentEntity = new CoachClubAssignment();
            $coachClubAssignmentEntity->setCoach($coachEntity);
            $coachClubAssignmentEntity->setClub($faker->randomElement($clubs));
            $coachClubAssignmentEntity->setStartDate($faker->dateTimeBetween('-15 years', '-7 years'));
            $coachClubAssignmentEntity->setEndDate($faker->dateTimeBetween('-10 years', null));
            $coachEntity->addCoachClubAssignment($coachClubAssignmentEntity);

            $manager->persist($coachClubAssignmentEntity);

            $coachTeamAssignmentEntity = new CoachTeamAssignment();
            $coachTeamAssignmentEntity->setCoach($coachEntity);
            $coachTeamAssignmentEntity->setTeam($faker->randomElement($teams));
            $coachTeamAssignmentEntity->setStartDate($faker->dateTimeBetween('-15 years', '-7 years'));
            $coachTeamAssignmentEntity->setEndDate($faker->dateTimeBetween('-10 years', null));
            $coachTeamAssignmentEntity->setCoachTeamAssignmentType($faker->randomElement($coachTeamAssignmentTypes));

            $manager->persist($coachTeamAssignmentEntity);

            $manager->persist($coachEntity);
        }

        $manager->flush();
        $manager->clear();
    }
}
