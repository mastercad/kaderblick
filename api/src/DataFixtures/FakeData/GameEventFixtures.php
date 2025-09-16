<?php

namespace App\DataFixtures\FakeData;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\GameEventType;
use App\Entity\Player;
use App\Entity\Team;
use DateTime;
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
            $this->getReference('game_1', Game::class),
            $this->getReference('game_2', Game::class),
        ];
        $teams = [
            $this->getReference('Team 1', Team::class),
            $this->getReference('Team 2', Team::class),
        ];
        $players = [
            $this->getReference('player_1_1', Player::class),
            $this->getReference('player_2_1', Player::class),
            $this->getReference('player_3_1', Player::class),
            $this->getReference('player_4_1', Player::class),
            $this->getReference('player_1_2', Player::class),
            $this->getReference('player_2_2', Player::class),
            $this->getReference('player_3_2', Player::class),
            $this->getReference('player_4_2', Player::class),
        ];
        // Event-Typen
        $goalType = $this->getReference('game_event_type_tor', GameEventType::class);
        $ownGoalType = $this->getReference('game_event_type_eigentor', GameEventType::class);
        $yellowType = $this->getReference('game_event_type_gelbe_karte', GameEventType::class);
        $redType = $this->getReference('game_event_type_rote_karte', GameEventType::class);
        $subInType = $this->getReference('game_event_type_einwechslung', GameEventType::class);
        $subOutType = $this->getReference('game_event_type_auswechslung', GameEventType::class);

        // Hilfsfunktion für Idempotenz
        $persistIfNotExists = function (ObjectManager $manager, $game, $eventType, $team, $player, $timestamp, $description, $relatedPlayer = null) {
            $criteria = [
                'game' => $game,
                'gameEventType' => $eventType,
                'team' => $team,
                'player' => $player,
                'timestamp' => $timestamp,
                'description' => $description,
            ];
            if (null !== $relatedPlayer) {
                $criteria['relatedPlayer'] = $relatedPlayer;
            }
            $existing = $manager->getRepository(GameEvent::class)->findOneBy($criteria);
            if (!$existing) {
                $event = new GameEvent();
                $event->setGame($game);
                $event->setGameEventType($eventType);
                $event->setTeam($team);
                $event->setPlayer($player);
                $event->setTimestamp($timestamp);
                $event->setDescription($description);
                if (null !== $relatedPlayer) {
                    $event->setRelatedPlayer($relatedPlayer);
                }
                $manager->persist($event);
            }
        };

        // Spiel 1: Team 1 vs Team 2
        $game = $games[0];
        $persistIfNotExists($manager, $game, $goalType, $teams[0], $players[0], (new DateTime())->setTime(15, 23), 'Schöner Distanzschuss.');
        $persistIfNotExists($manager, $game, $yellowType, $teams[1], $players[4], (new DateTime())->setTime(22, 10), 'Foulspiel im Mittelfeld.');
        $persistIfNotExists($manager, $game, $subInType, $teams[0], $players[1], (new DateTime())->setTime(46, 0), 'Positionswechsel zur Halbzeit.', $players[2]);
        $persistIfNotExists($manager, $game, $subOutType, $teams[0], $players[2], (new DateTime())->setTime(46, 0), 'Positionswechsel zur Halbzeit.', $players[1]);
        $persistIfNotExists($manager, $game, $redType, $teams[1], $players[5], (new DateTime())->setTime(70, 5), 'Notbremse.');
        $persistIfNotExists($manager, $game, $ownGoalType, $teams[1], $players[6], (new DateTime())->setTime(80, 44), 'Abgefälschter Ball.');

        // Spiel 2: Team 2 vs Team 1 (Beispiel)
        $game = $games[1];
        $persistIfNotExists($manager, $game, $goalType, $teams[1], $players[4], (new DateTime())->setTime(10, 5), 'Kopfball nach Ecke.');

        $manager->flush();
    }
}
