<?php

namespace App\DataFixtures\MasterData;

use App\Entity\CoachTeamAssignmentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CoachTeamAssignmentTypeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $types = [
            [
                'name' => 'Cheftrainer',
                'description' => 'Hauptverantwortlicher Trainer des Teams.',
                'active' => true
            ],
            [
                'name' => 'Co-Trainer',
                'description' => 'Unterstützt den Cheftrainer in allen Belangen.',
                'active' => true
            ],
            [
                'name' => 'Torwarttrainer',
                'description' => 'Spezialisiert auf das Training der Torhüter.',
                'active' => true
            ],
            [
                'name' => 'Athletiktrainer',
                'description' => 'Verantwortlich für Fitness und Kondition.',
                'active' => true
            ],
            [
                'name' => 'Videoanalyst',
                'description' => 'Bereitet taktische Videoanalysen für das Team vor.',
                'active' => true
            ],
            [
                'name' => 'Physiotherapeut',
                'description' => 'Betreut die Spieler medizinisch und begleitet Reha-Maßnahmen.',
                'active' => true
            ],
            [
                'name' => 'Interimstrainer',
                'description' => 'Vorübergehend eingesetzter Trainer zur Überbrückung.',
                'active' => true
            ],
            [
                'name' => 'Trainer in Ausbildung',
                'description' => 'Trainer in Ausbildung oder Praktikum.',
                'active' => true
            ],
            [
                'name' => 'Gasttrainer',
                'description' => 'Vorübergehender externer Trainer, z. B. zur Hospitation.',
                'active' => true
            ],
        ];

        foreach ($types as $type) {
            $assignmentType = new CoachTeamAssignmentType();
            $assignmentType->setName($type['name']);
            $assignmentType->setDescription($type['description']);
            $assignmentType->setActive($type['active']);
            $manager->persist($assignmentType);
        }

        $manager->flush();
        $manager->clear();
    }
}
