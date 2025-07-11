<?php

namespace App\DataFixtures\TestData;

use App\DataFixtures\MasterData\CoachLicenseFixtures;
use App\DataFixtures\MasterData\CoachTeamAssignmentTypeFixtures;
use App\Entity\Coach;
use App\Entity\CoachLicense;
use App\Entity\CoachLicenseAssignment;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CoachFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
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

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $licenseScenarios = [
            // Coach 1: Klassische DFB-Karriere
            [
                'kindertrainer_zertifikat' => ['2010-01-01', '2012-12-31'],
                'basis_coach' => ['2012-01-01', '2013-12-31'],
                'c_lizenz' => ['2014-01-01', '2016-12-31'],
                'b_lizenz' => ['2017-01-01', '2019-12-31'],
                'a_lizenz' => ['2020-01-01', '2022-12-31'],
                'fussball_lehrer' => '2023-01-01'
            ],
            // Coach 2: Jugendbereich-Fokus
            [
                'kindertrainer_zertifikat' => ['2015-01-01', '2016-12-31'],
                'basis_coach' => ['2017-01-01', '2018-12-31'],
                'c_lizenz' => '2019-01-01'
            ],
            // Coach 3: Direkter UEFA-Weg
            [
                'uefa_c' => ['2018-01-01', '2019-12-31'],
                'uefa_b' => ['2020-01-01', '2021-12-31'],
                'uefa_a' => '2022-01-01'
            ],
            // Coach 4: Kombination DFB/UEFA
            [
                'c_lizenz' => ['2015-01-01', '2017-12-31'],
                'b_lizenz' => ['2018-01-01', '2020-12-31'],
                'uefa_a' => '2021-01-01'
            ],
            // Coach 5: Erfahrener Pro-Level
            [
                'uefa_b' => ['2010-01-01', '2012-12-31'],
                'uefa_a' => ['2013-01-01', '2015-12-31'],
                'uefa_pro' => '2016-01-01'
            ],
            // Coach 6: Frischer Einsteiger
            [
                'kindertrainer_zertifikat' => ['2022-01-01', '2022-12-31'],
                'basis_coach' => '2023-01-01'
            ],
            // Coach 7: Mittleres Niveau
            [
                'basis_coach' => ['2018-01-01', '2019-12-31'],
                'c_lizenz' => ['2020-01-01', '2021-12-31'],
                'b_lizenz' => '2022-01-01'
            ],
            // Coach 8: UEFA-Profi mit DFB-Start
            [
                'c_lizenz' => ['2012-01-01', '2014-12-31'],
                'uefa_b' => ['2015-01-01', '2017-12-31'],
                'uefa_a' => ['2018-01-01', '2020-12-31'],
                'uefa_pro' => '2021-01-01'
            ],
            // Coach 9: Langsamer Aufstieg
            [
                'kindertrainer_zertifikat' => ['2010-01-01', '2013-12-31'],
                'basis_coach' => ['2014-01-01', '2016-12-31'],
                'c_lizenz' => ['2017-01-01', '2019-12-31'],
                'b_lizenz' => '2020-01-01'
            ],
            // Coach 10: Schneller UEFA-Aufstieg
            [
                'uefa_c' => ['2020-01-01', '2020-12-31'],
                'uefa_b' => ['2021-01-01', '2021-12-31'],
                'uefa_a' => ['2022-01-01', '2022-12-31'],
                'uefa_pro' => '2023-01-01'
            ],
            // Coach 11: Parallele Lizenzen
            [
                'c_lizenz' => ['2015-01-01', '2017-12-31'],
                'uefa_c' => ['2016-01-01', '2018-12-31'],
                'b_lizenz' => ['2018-01-01', '2020-12-31'],
                'uefa_b' => '2021-01-01'
            ],
            // Coach 12: DFB-Karriere mit Pause
            [
                'c_lizenz' => ['2012-01-01', '2014-12-31'],
                'b_lizenz' => ['2017-01-01', '2019-12-31'],
                'a_lizenz' => '2022-01-01'
            ]
        ];

        foreach ($licenseScenarios as $i => $scenario) {
            $coach = new Coach();
            $coach->setFirstName('Coach');
            $coach->setLastName((string) ($i + 1));
            $coach->setBirthDate($this->generateBirthDate());

            $manager->persist($coach);

            $this->addReference('coach_' . ($i + 1), $coach);

            foreach ($scenario as $level => $dates) {
                if (is_array($dates)) {
                    $this->assignLicense($manager, $coach, $level, $dates[0], $dates[1]);
                } else {
                    $this->assignLicense($manager, $coach, $level, $dates);
                }
            }
        }

        $manager->flush();
        $manager->clear();
    }

    private function generateBirthDate(): DateTime
    {
        return new DateTime(
            sprintf(
                '19%d-%02d-%02d',
                random_int(70, 90),
                random_int(1, 12),
                random_int(1, 28)
            )
        );
    }

    private function assignLicense(ObjectManager $manager, Coach $coach, string $level, string $startDate, ?string $endDate = null): void
    {
        $assignment = new CoachLicenseAssignment();
        $assignment->setCoach($coach);
        $assignment->setLicense($this->getReference('coach_license_' . $level, CoachLicense::class));
        $assignment->setStartDate(new DateTime($startDate));
        $assignment->setEndDate($endDate ? new DateTime($endDate) : null);

        $manager->persist($assignment);
    }
}
