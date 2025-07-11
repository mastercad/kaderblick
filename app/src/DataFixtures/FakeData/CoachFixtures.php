<?php

namespace App\DataFixtures\FakeData;

use App\DataFixtures\MasterData\CoachLicenseFixtures;
use App\DataFixtures\MasterData\CoachTeamAssignmentTypeFixtures;
use App\Entity\Club;
use App\Entity\Coach;
use App\Entity\CoachClubAssignment;
use App\Entity\CoachLicense;
use App\Entity\CoachLicenseAssignment;
use App\Entity\CoachTeamAssignment;
use App\Entity\CoachTeamAssignmentType;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CoachFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            CoachLicenseFixtures::class,
            CoachTeamAssignmentTypeFixtures::class,
            ClubFixtures::class,
            //            TeamFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['fake'];
    }

    public function load(ObjectManager $manager): void
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

        for ($i = 0; $i < $coachCount; ++$i) {
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
