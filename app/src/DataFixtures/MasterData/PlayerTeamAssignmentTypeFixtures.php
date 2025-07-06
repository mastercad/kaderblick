<?php

namespace App\DataFixtures\MasterData;

use App\Entity\PlayerTeamAssignmentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlayerTeamAssignmentTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $types = [
            [
                'name' => 'Vertragsspieler',
                'description' => 'Fester Kader-Spieler (dauerhaft im Team)',
                'active' => true
            ],
            [
                'name' => 'Leihgabe',
                'description' => 'Temporär von einem anderen Verein ausgeliehen',
                'active' => true
            ],
            [
                'name' => 'Gastspieler',
                'description' => 'Spielt gelegentlich mit, z. B. in Freundschaftsspielen',
                'active' => true
            ],
            [
                'name' => 'Testspieler',
                'description' => 'Spieler auf Probe, evtl. für Transfer oder Neuzugang',
                'active' => true
            ],
            [
                'name' => 'Jugendspieler',
                'description' => 'Spieler aus dem Jugendbereich, der z. B. Aushilft in der 1. Mannschaft',
                'active' => true
            ],
            [
                'name' => 'Doppelte Spielberechtigung',
                'description' => 'Spielt z. B. in zwei Mannschaften, oft bei Jugend/Herren-Kombis',
                'active' => true
            ],
            [
                'name' => 'Kooperationsspieler',
                'description' => 'Kommt z. B. aus einem Partnerverein temporär',
                'active' => true
            ],
            [
                'name' => 'Externer Spieler',
                'description' => 'Gehört nicht zum Verein, aber wird für ein Turnier eingesetzt',
                'active' => true
            ],
            [
                'name' => 'Gesperrter Spieler',
                'description' => 'Aktuell nicht spielberechtigt (Disziplin, Formalien, etc.)',
                'active' => true
            ],
            [
                'name' => 'Verletzter Spieler',
                'description' => 'Aktuell nicht einsatzfähig',
                'active' => true
            ],
        ];

        foreach ($types as $type) {
            $assignmentType = new PlayerTeamAssignmentType();
            $assignmentType->setName($type['name']);
            $assignmentType->setDescription($type['description']);
            $assignmentType->setActive($type['active']);
            $manager->persist($assignmentType);
        }

        $manager->flush();
        $manager->clear();
    }
}
