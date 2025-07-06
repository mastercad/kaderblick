<?php

namespace App\DataFixtures\MasterData;

use App\Entity\SubstitutionReason;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SubstitutionReasonFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $reasons = [
            ['name' => 'Taktische Gründe', 'description' => 'Systemwechsel, Zeitspiel oder frischer Spieler'],
            ['name' => 'Verletzung', 'description' => 'Spieler verletzt oder angeschlagen'],
            ['name' => 'Leistung', 'description' => 'Schlechte Leistung oder zu viele Fehler'],
            ['name' => 'Schonung', 'description' => 'Rotation oder Schonung für andere Spiele'],
            ['name' => 'Gelb-Rot-Risiko', 'description' => 'Risiko auf Gelb-Rot oder Platzverweis'],
            ['name' => 'Debüt', 'description' => 'Einwechslung für einen Jugend-/Debütspieler'],
            ['name' => 'Comeback', 'description' => 'Rückkehr nach Verletzung'],
            ['name' => 'Zeitspiel', 'description' => 'Auswechslung in der Nachspielzeit'],
            ['name' => 'Verabschiedung', 'description' => 'Spieler bekommt Applaus beim Abschied'],
        ];

        foreach ($reasons as $reason) {
            $substitutionReason = new SubstitutionReason();
            $substitutionReason->setName($reason['name']);
            $substitutionReason->setDescription($reason['description']);
            $substitutionReason->setActive(true);
            $manager->persist($substitutionReason);
        }

        $manager->flush();
        $manager->clear();
    }
}
