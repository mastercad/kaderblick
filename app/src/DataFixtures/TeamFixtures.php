<?php

namespace App\DataFixtures;

use App\Entity\Team;
use App\Entity\League;
use App\Entity\AgeGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TeamFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            LeagueFixtures::class,
            AgeGroupFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $teams = [
            ['name' => 'FC Musterdorf', 'league' => 'kreisliga_a', 'ageGroup' => 'senioren'],
            ['name' => 'SV Beispielhausen', 'league' => 'kreisliga_b', 'ageGroup' => 'senioren'],
        ];

        foreach ($teams as $teamData) {
            $team = new Team();
            $team->setName($teamData['name']);
            $team->setAgeGroup($this->getReference('age_group_' . $teamData['ageGroup'], AgeGroup::class));
            $team->setLeague($this->getReference('league_' . $teamData['league'], League::class));
            $manager->persist($team);
        }

        $manager->flush();
    }
}
