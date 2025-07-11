<?php

namespace App\DataFixtures\TestData;

use App\DataFixtures\MasterData\CoachTeamAssignmentTypeFixtures;
use App\Entity\Coach;
use App\Entity\CoachTeamAssignment;
use App\Entity\CoachTeamAssignmentType;
use App\Entity\Team;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CoachTeamAssignmentFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            CoachFixtures::class,
            TeamFixtures::class,
            CoachClubAssignmentFixtures::class,
            CoachTeamAssignmentTypeFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $assignments = [
            // Profi-Teams
            [
                'coach' => 'coach_1',     // Fußballlehrer
                'team' => 'Team 1',       // Bundesliga Team
                'start' => '2023-01-01',
                'type' => 'cheftrainer'
            ],
            [
                'coach' => 'coach_5',     // UEFA Pro
                'team' => 'Team 2',       // 2. Bundesliga Team
                'start' => '2016-01-01',
                'type' => 'cheftrainer'
            ],
            [
                'coach' => 'coach_7',     // B-Lizenz
                'team' => 'Team 4',       // A-Jugend Bundesliga
                'start' => '2022-01-01',
                'type' => 'co_trainer'
            ],
            [
                'coach' => 'coach_4',     // UEFA A
                'team' => 'Team 6',       // B-Jugend Bundesliga
                'start' => '2021-01-01',
                'type' => 'torwarttrainer'
            ],
            [
                'coach' => 'coach_2',     // C-Lizenz
                'team' => 'Team 11',      // D-Jugend Kreis
                'start' => '2019-01-01',
                'type' => 'athletiktrainer'
            ],
            [
                'coach' => 'coach_8',     // UEFA Pro
                'team' => 'Team 1',       // Bundesliga Team
                'start' => '2015-01-01',
                'end' => '2020-12-31',
                'type' => 'interimstrainer'
            ],
            [
                'coach' => 'coach_9',     // B-Lizenz
                'team' => 'Team 13',      // F-Jugend
                'start' => '2020-01-01',
                'type' => 'trainer_in_ausbildung'
            ],
            [
                'coach' => 'coach_11',    // Parallele Lizenzen
                'team' => 'Team 15',      // Frauen Bundesliga
                'start' => '2021-01-01',
                'type' => 'co_trainer'
            ],
            [
                'coach' => 'coach_12',    // A-Lizenz
                'team' => 'Team 8',       // C-Jugend Regional
                'start' => '2022-01-01',
                'type' => 'gasttrainer'
            ],
            [
                'coach' => 'coach_3',     // UEFA A
                'team' => 'Team 10',      // D-Jugend
                'start' => '2023-01-01',
                'type' => 'videoanalyst'
            ]
        ];

        foreach ($assignments as $data) {
            $assignment = new CoachTeamAssignment();
            $assignment->setCoach($this->getReference($data['coach'], Coach::class));
            $assignment->setTeam($this->getReference($data['team'], Team::class));
            $assignment->setStartDate(new DateTime($data['start']));
            $assignment->setCoachTeamAssignmentType(
                $this->getReference('coach_team_assignment_type_' . $data['type'], CoachTeamAssignmentType::class)
            );

            if (isset($data['end'])) {
                $assignment->setEndDate(new DateTime($data['end']));
            }

            $manager->persist($assignment);
        }

        $manager->flush();
    }
}
