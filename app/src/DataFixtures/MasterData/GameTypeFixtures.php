<?php

namespace App\DataFixtures\MasterData;

use App\Entity\GameType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class GameTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['master'];
    }

    public function load(ObjectManager $manager): void
    {
        $types = [
            [
                'name' => 'Ligaspiel',
                'description' => 'Reguläre Spiele in Ligen wie Bundesliga, Premier League etc.'
            ],
            [
                'name' => 'Pokalspiel',
                'description' => 'Spiele in nationalen Pokalwettbewerben wie DFB-Pokal, FA Cup etc.'
            ],
            [
                'name' => 'Freundschaftsspiel',
                'description' => 'Testspiele und Vorbereitungsspiele ohne Wettbewerbscharakter'
            ],
            [
                'name' => 'Internationales Spiel',
                'description' => 'Länderspiele und Nations League Spiele'
            ],
            [
                'name' => 'Turnierspiel',
                'description' => 'Spiele bei Turnieren wie WM, EM, Copa America, Champions League'
            ],
            [
                'name' => 'Playoff-Spiel',
                'description' => 'Spiele in Aufstiegsrunden oder Relegation'
            ],
            [
                'name' => 'Supercup',
                'description' => 'Spiele wie DFL-Supercup oder FA Community Shield'
            ],
            [
                'name' => 'Testspiel',
                'description' => 'Ähnlich wie Freundschaftsspiele, aber mit mehr Experimentiercharakter'
            ],
            [
                'name' => 'Trainingseinheit',
                'description' => 'Interne Trainingsspiele und -einheiten'
            ],
            [
                'name' => 'Nachholspiel',
                'description' => 'Verschobene oder nachgeholte Spiele'
            ],
            [
                'name' => 'Finale',
                'description' => 'Endspiele in Pokalen oder Turnieren'
            ],
            [
                'name' => 'Halbfinale',
                'description' => 'Vorletzte Runde in Pokalen oder Turnieren'
            ],
            [
                'name' => 'Vorrundenspiel',
                'description' => 'Gruppenspiele bei Turnieren'
            ],
            [
                'name' => 'Qualifikationsspiel',
                'description' => 'Spiele zur Qualifikation für Turniere wie WM oder EM'
            ],
        ];

        foreach ($types as $type) {
            $gameType = new GameType();
            $gameType->setName($type['name']);
            $gameType->setDescription($type['description']);
            $manager->persist($gameType);

            $this->addReference(
                'game_type_' . strtolower(str_replace(['-', ' '], '_', $gameType->getName())),
                $gameType
            );
        }

        $manager->flush();
        $manager->clear();
    }
}
