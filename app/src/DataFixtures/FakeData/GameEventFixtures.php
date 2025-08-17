<?php

namespace App\DataFixtures\FakeData;

use App\Entity\GameEvent;
use App\Entity\GameEventType;
use App\Entity\Player;
use App\Entity\Team;
use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class GameEventFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            PlayerFixtures::class,
            // ggf. TeamFixtures::class,
            // ggf. GameFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['fake'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('de_DE');
        $players = $manager->getRepository(Player::class)->findAll();
        $teams = $manager->getRepository(Team::class)->findAll();
        $eventTypes = $manager->getRepository(GameEventType::class)->findAll();
        $games = $manager->getRepository(Game::class)->findAll();

        if (empty($players) || empty($teams) || empty($eventTypes) || empty($games)) {
            return;
        }

        // Beispiel: 2 Spiele, 2 Teams, 4 Spieler pro Team
        $games = [
            $this->getReference('game_1', \App\Entity\Game::class),
            $this->getReference('game_2', \App\Entity\Game::class),
        ];
        $teams = [
            $this->getReference('Team 1', \App\Entity\Team::class),
            $this->getReference('Team 2', \App\Entity\Team::class),
        ];
        $players = [
            $this->getReference('player_1_1', \App\Entity\Player::class),
            $this->getReference('player_2_1', \App\Entity\Player::class),
            $this->getReference('player_3_1', \App\Entity\Player::class),
            $this->getReference('player_4_1', \App\Entity\Player::class),
            $this->getReference('player_1_2', \App\Entity\Player::class),
            $this->getReference('player_2_2', \App\Entity\Player::class),
            $this->getReference('player_3_2', \App\Entity\Player::class),
            $this->getReference('player_4_2', \App\Entity\Player::class),
        ];
        // Event-Typen
        $goalType = $this->getReference('game_event_type_tor', \App\Entity\GameEventType::class);
        $ownGoalType = $this->getReference('game_event_type_eigentor', \App\Entity\GameEventType::class);
        $yellowType = $this->getReference('game_event_type_gelbe_karte', \App\Entity\GameEventType::class);
        $redType = $this->getReference('game_event_type_rote_karte', \App\Entity\GameEventType::class);
        $subInType = $this->getReference('game_event_type_einwechslung', \App\Entity\GameEventType::class);
        $subOutType = $this->getReference('game_event_type_auswechslung', \App\Entity\GameEventType::class);

        // Spiel 1: Team 1 vs Team 2
        $game = $games[0];
        // 1. Tor Team 1
        $event1 = new GameEvent();
        $event1->setGame($game);
        $event1->setGameEventType($goalType);
        $event1->setTeam($teams[0]);
        $event1->setPlayer($players[0]);
        $event1->setTimestamp((new \DateTime())->setTime(15, 23));
        $event1->setDescription('Schöner Distanzschuss.');
        $manager->persist($event1);

        // 2. Gelbe Karte Team 2
        $event2 = new GameEvent();
        $event2->setGame($game);
        $event2->setGameEventType($yellowType);
        $event2->setTeam($teams[1]);
        $event2->setPlayer($players[4]);
        $event2->setTimestamp((new \DateTime())->setTime(22, 10));
        $event2->setDescription('Foulspiel im Mittelfeld.');
        $manager->persist($event2);

        // 3. Einwechslung Team 1 (player_2_1 rein, player_3_1 raus)
        $event3 = new GameEvent();
        $event3->setGame($game);
        $event3->setGameEventType($subInType);
        $event3->setTeam($teams[0]);
        $event3->setPlayer($players[1]); // kommt rein
        $event3->setRelatedPlayer($players[2]); // geht raus
        $event3->setTimestamp((new \DateTime())->setTime(46, 0));
        $event3->setDescription('Positionswechsel zur Halbzeit.');
        $manager->persist($event3);

        // 4. Auswechslung Team 1 (player_3_1 raus, player_2_1 rein)
        $event4 = new GameEvent();
        $event4->setGame($game);
        $event4->setGameEventType($subOutType);
        $event4->setTeam($teams[0]);
        $event4->setPlayer($players[2]); // geht raus
        $event4->setRelatedPlayer($players[1]); // kommt rein
        $event4->setTimestamp((new \DateTime())->setTime(46, 0));
        $event4->setDescription('Positionswechsel zur Halbzeit.');
        $manager->persist($event4);

        // 5. Rote Karte Team 2
        $event5 = new GameEvent();
        $event5->setGame($game);
        $event5->setGameEventType($redType);
        $event5->setTeam($teams[1]);
        $event5->setPlayer($players[5]);
        $event5->setTimestamp((new \DateTime())->setTime(70, 5));
        $event5->setDescription('Notbremse.');
        $manager->persist($event5);

        // 6. Eigentor Team 2
        $event6 = new GameEvent();
        $event6->setGame($game);
        $event6->setGameEventType($ownGoalType);
        $event6->setTeam($teams[1]);
        $event6->setPlayer($players[6]);
        $event6->setTimestamp((new \DateTime())->setTime(80, 44));
        $event6->setDescription('Abgefälschter Ball.');
        $manager->persist($event6);

        // Spiel 2: Team 2 vs Team 1 (Beispiel)
        $game = $games[1];
        $event7 = new GameEvent();
        $event7->setGame($game);
        $event7->setGameEventType($goalType);
        $event7->setTeam($teams[1]);
        $event7->setPlayer($players[4]);
        $event7->setTimestamp((new \DateTime())->setTime(10, 5));
        $event7->setDescription('Kopfball nach Ecke.');
        $manager->persist($event7);

        $manager->flush();

        $manager->flush();
    }
}
