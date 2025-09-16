<?php

namespace App\DataFixtures\TestData;

use App\Entity\Club;
use App\Entity\Coach;
use App\Entity\CoachClubAssignment;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CoachClubAssignmentFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            CoachFixtures::class,
            ClubFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $assignments = [
            // Profi-Trainer bei großen Clubs
            ['coach' => 'coach_1', 'club' => 'club1', 'start' => '2023-01-01'], // Fußballlehrer
            ['coach' => 'coach_5', 'club' => 'club2', 'start' => '2016-01-01'], // UEFA Pro

            // Etablierte Trainer
            ['coach' => 'coach_3', 'club' => 'club3', 'start' => '2022-01-01'], // UEFA A
            ['coach' => 'coach_4', 'club' => 'club1', 'start' => '2021-01-01'], // UEFA A

            // Jugendtrainer
            ['coach' => 'coach_2', 'club' => 'club4', 'start' => '2019-01-01'], // C-Lizenz
            ['coach' => 'coach_7', 'club' => 'club2', 'start' => '2022-01-01'], // B-Lizenz

            // Historische Assignments (beendet)
            [
                'coach' => 'coach_8',
                'club' => 'club1',
                'start' => '2015-01-01',
                'end' => '2020-12-31'
            ],

            // Aktuelle Assignments
            ['coach' => 'coach_8', 'club' => 'club2', 'start' => '2021-01-01'], // Vereinswechsel
            ['coach' => 'coach_9', 'club' => 'club3', 'start' => '2020-01-01'],
            ['coach' => 'coach_10', 'club' => 'club4', 'start' => '2023-01-01'],
            ['coach' => 'coach_11', 'club' => 'club1', 'start' => '2021-01-01'],
            ['coach' => 'coach_12', 'club' => 'club2', 'start' => '2022-01-01']
        ];

        foreach ($assignments as $data) {
            $coach = $this->getReference($data['coach'], Coach::class);
            $club = $this->getReference($data['club'], Club::class);
            $startDate = new DateTime($data['start']);
            $criteria = [
                'coach' => $coach,
                'club' => $club,
                'startDate' => $startDate,
            ];
            $existing = $manager->getRepository(CoachClubAssignment::class)->findOneBy($criteria);
            if ($existing) {
                continue;
            }
            $assignment = new CoachClubAssignment();
            $assignment->setCoach($coach);
            $assignment->setClub($club);
            $assignment->setStartDate($startDate);
            if (isset($data['end'])) {
                $assignment->setEndDate(new DateTime($data['end']));
            }
            $manager->persist($assignment);
        }

        $manager->flush();
    }
}
