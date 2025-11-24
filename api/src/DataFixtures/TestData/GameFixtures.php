<?php

namespace App\DataFixtures\TestData;

use App\Entity\Game;
use App\Entity\GameType;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

class GameFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            TeamFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        // Hole Teams aus den Fixtures
        $teams = $manager->getRepository(Team::class)->findAll();
        $gameTypes = $manager->getRepository(GameType::class)->findAll();

        if (count($teams) < 2) {
            throw new RuntimeException('Mindestens 2 Teams werden für Game-Fixtures benötigt');
        }

        if (empty($gameTypes)) {
            throw new RuntimeException('Keine GameTypes gefunden. Bitte master-Fixtures laden.');
        }

        // Erstelle 5 Test-Games
        for ($i = 0; $i < 5; ++$i) {
            $game = new Game();

            // Wähle zwei verschiedene Teams
            $homeTeamIndex = $i % count($teams);
            $awayTeamIndex = ($i + 1) % count($teams);

            $game->setHomeTeam($teams[$homeTeamIndex]);
            $game->setAwayTeam($teams[$awayTeamIndex]);
            $game->setGameType($gameTypes[array_rand($gameTypes)]);

            // Setze optional Scores für abgeschlossene Spiele
            if ($i < 3) {
                $game->setHomeScore(rand(0, 5));
                $game->setAwayScore(rand(0, 5));
                $game->setIsFinished(true);
            } else {
                $game->setIsFinished(false);
            }

            $manager->persist($game);
            $this->addReference('game_' . $i, $game);
        }

        $manager->flush();
    }
}
