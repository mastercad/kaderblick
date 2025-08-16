<?php

namespace App\DataFixtures\TestData;

use App\DataFixtures\MasterData\AgeGroupFixtures;
use App\DataFixtures\MasterData\LeagueFixtures;
use App\Entity\AgeGroup;
use App\Entity\Club;
use App\Entity\League;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TeamFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            AgeGroupFixtures::class,
            LeagueFixtures::class,
            ClubFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $teamData = [
            // Seniorenteams in verschiedenen Ligen
            [
                'name' => 'Team 1',
                'age_group' => 'senioren',
                'league' => 'bundesliga',
                'club' => 'club1',
            ],
            [
                'name' => 'Team 2',
                'age_group' => 'senioren',
                'league' => '2_bundesliga',
                'club' => 'club2',
            ],
            [
                'name' => 'Team 3',
                'age_group' => 'senioren',
                'league' => 'regionalliga_west',
                'club' => 'club3',
            ],
            // A-Junioren mit Bundesliga
            [
                'name' => 'Team 4',
                'age_group' => 'a_junioren',
                'league' => 'bundesliga',
                'club' => 'club1',
            ],
            [
                'name' => 'Team 5',
                'age_group' => 'a_junioren',
                'league' => 'regionalliga',
                'club' => 'club2',
            ],
            // B-Junioren verschiedene Ebenen
            [
                'name' => 'Team 6',
                'age_group' => 'b_junioren',
                'league' => 'bundesliga',
                'club' => 'club1',
            ],
            [
                'name' => 'Team 7',
                'age_group' => 'b_junioren',
                'league' => 'verbandsliga',
                'club' => 'club3',
            ],
            // C-Junioren
            [
                'name' => 'Team 8',
                'age_group' => 'c_junioren',
                'league' => 'regionalliga',
                'club' => 'club1',
            ],
            [
                'name' => 'Team 9',
                'age_group' => 'c_junioren',
                'league' => 'bezirksliga',
                'club' => 'club2',
            ],
            // D-Junioren
            [
                'name' => 'Team 10',
                'age_group' => 'd_junioren',
                'league' => 'verbandsliga',
                'club' => 'club1',
            ],
            [
                'name' => 'Team 11',
                'age_group' => 'd_junioren',
                'league' => 'kreisliga',
                'club' => 'club4',
            ],
            // E bis G-Junioren in Kreisligen
            [
                'name' => 'Team 12',
                'age_group' => 'e_junioren',
                'league' => 'kreisliga',
                'club' => 'club2',
            ],
            [
                'name' => 'Team 13',
                'age_group' => 'f_junioren',
                'league' => 'kreisliga',
                'club' => 'club3',
            ],
            [
                'name' => 'Team 14',
                'age_group' => 'g_junioren',
                'league' => 'kreisliga',
                'club' => 'club4',
            ],
            // Frauenmannschaften
            [
                'name' => 'Team 15',
                'age_group' => 'senioren',
                'league' => 'frauen_bundesliga',
                'club' => 'club1',
            ],
            [
                'name' => 'Team 16',
                'age_group' => 'senioren',
                'league' => 'frauen_regionalliga',
                'club' => 'club2',
            ]
        ];

        foreach ($teamData as $data) {
            $team = new Team();
            $team->setName($data['name']);
            $team->setAgeGroup($this->getReference('age_group_' . $data['age_group'], AgeGroup::class));
            $team->setLeague($this->getReference('league_' . $data['league'], League::class));
            $team->addClub($this->getReference($data['club'], Club::class));

            $manager->persist($team);
            $this->addReference($data['name'], $team);
        }

        $manager->flush();
    }
}
