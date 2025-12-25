<?php

namespace App\DataFixtures\TestData;

use App\DataFixtures\MasterData\PlayerTeamAssignmentTypeFixtures;
use App\DataFixtures\MasterData\PositionFixtures;
use App\DataFixtures\MasterData\StrongFootFixtures;
use App\Entity\AgeGroup;
use App\Entity\Player;
use App\Entity\PlayerClubAssignment;
use App\Entity\PlayerTeamAssignment;
use App\Entity\PlayerTeamAssignmentType;
use App\Entity\Position;
use App\Entity\StrongFoot;
use App\Entity\Team;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PlayerFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            TeamFixtures::class,
            ClubFixtures::class,
            StrongFootFixtures::class,
            PositionFixtures::class,
            PlayerTeamAssignmentTypeFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $strongFeet = [
            'rechts' => $this->getReference('strong_foot_right', StrongFoot::class),
            'links' => $this->getReference('strong_foot_left', StrongFoot::class),
            'beidfuessig' => $this->getReference('strong_foot_both', StrongFoot::class),
        ];

        $positions = [
            'tor' => $this->getReference('position_tw', Position::class),
            'abwehr' => $this->getReference('position_iv', Position::class),
            'mittelfeld' => $this->getReference('position_zm', Position::class),
            'sturm' => $this->getReference('position_st', Position::class),
        ];

        // Teambasierte Spieleranzahlen (realistischere Kadergrößen)
        $teamPlayerCounts = [
            1 => 28,  // Bundesliga-Team (großer Kader)
            2 => 26,  // 2. Bundesliga
            3 => 24,  // Regionalliga
            4 => 22,  // A-Jugend Bundesliga
            5 => 20,  // A-Jugend Regional
            6 => 22,  // B-Jugend Bundesliga
            7 => 18,  // B-Jugend Verband
            8 => 18,  // C-Jugend Regional
            9 => 16,  // C-Jugend Bezirk
            10 => 16, // D-Jugend Verband
            11 => 14, // D-Jugend Kreis
            12 => 14, // E-Jugend
            13 => 12, // F-Jugend
            14 => 10, // G-Jugend
            15 => 24, // Frauen Bundesliga
            16 => 20  // Frauen Regionalliga
        ];

        // Verwende die Teams aus TeamFixtures
        for ($teamNumber = 1; $teamNumber <= 16; ++$teamNumber) {
            /** @var Team $team */
            $team = $this->getReference('Team ' . $teamNumber, Team::class);
            $playerCount = $teamPlayerCounts[$teamNumber];

            // Wähle einen der verfügbaren Clubs des Teams für die Spieler
            $clubs = $team->getClubs()->toArray();

            // Pro Team die definierte Anzahl Spieler
            for ($playerNumber = 1; $playerNumber <= $playerCount; ++$playerNumber) {
                $selectedClub = $clubs[array_rand($clubs)];

                $firstName = 'Player_' . $playerNumber;
                $lastName = 'Team ' . $teamNumber . ' / ' . $selectedClub->getName();
                $email = $firstName . '.' . $lastName . '@example.com';

                $existing = $manager->getRepository(Player::class)->findOneBy([
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'email' => $email,
                ]);
                if ($existing) {
                    $player = $existing;
                } else {
                    $player = new Player();
                    $player->setFirstName($firstName);
                    $player->setLastName($lastName);
                    $player->setStrongFoot($strongFeet[array_rand($strongFeet)]);
                    $player->setEmail($email);

                    // TODO positionen sind aktuell noch dynamisch, das könnte bei strengeren tests ein problem werden,
                    // vielleicht diese positionen einmal auswürfeln und dann fest hinterlegen
                    // Position basierend auf Teamgröße und Spielernummer
                    if ($playerNumber <= ceil($playerCount * 0.1)) {
                        $player->setMainPosition($positions['tor']);                    // 10% Torhüter
                    } elseif ($playerNumber <= ceil($playerCount * 0.4)) {
                        $player->setMainPosition($positions['abwehr']);                 // 30% Abwehr
                    } elseif ($playerNumber <= ceil($playerCount * 0.7)) {
                        $player->setMainPosition($positions['mittelfeld']);             // 30% Mittelfeld
                    } else {
                        $player->setMainPosition($positions['sturm']);                  // 30% Sturm
                    }

                    $player->setBirthdate($this->generateBirthDate($team->getAgeGroup()));
                    $manager->persist($player);
                }

                // Club Assignment - nur zu EINEM Club aus den verfügbaren Clubs des Teams
                $clubAssignment = new PlayerClubAssignment();
                $clubAssignment->setPlayer($player);
                $clubAssignment->setClub($selectedClub);
                // TODO das start datum und auch ein enddatum variabler gestalten
                $clubAssignment->setStartDate(new DateTimeImmutable('2023-01-01'));
                $manager->persist($clubAssignment);

                // Team Assignment mit Spielertyp basierend auf Position im Kader
                $teamAssignment = new PlayerTeamAssignment();
                $teamAssignment->setPlayer($player);
                $teamAssignment->setTeam($team);
                // TODO auch hier das Startdatum und enddatum variabler gestalten
                $teamAssignment->setStartDate(new DateTimeImmutable('2023-01-01'));

                // Dynamischere Verteilung der Spielertypen
                // TODO für solidere Tests diese dynamic in statische verteilung umwandeln
                $assignmentType = match (true) {
                    $playerNumber <= ceil($playerCount * 0.6) => 'vertragsspieler',     // 60% Stammspieler
                    $playerNumber <= ceil($playerCount * 0.8) => 'gastspieler',         // 20% Gastspieler
                    $playerNumber <= ceil($playerCount * 0.9) => 'testspieler',         // 10% Testspieler
                    $playerNumber <= ceil($playerCount * 0.95) => 'jugendspieler',      // 5% Jugendspieler
                    default => 'kooperationsspieler'                                    // 5% Kooperationsspieler
                };

                $teamAssignment->setPlayerTeamAssignmentType(
                    $this->getReference(
                        'player_team_assignment_type_' . $assignmentType,
                        PlayerTeamAssignmentType::class
                    )
                );

                $manager->persist($teamAssignment);

                $this->addReference('player_' . $playerNumber . '_' . $teamNumber, $player);
            }
        }

        // Ensure player_1_1 (used for user1@example.com relation) has a PlayerTeamAssignment to Team 1
        $player_1_1 = $this->getReference('player_1_1', Player::class);
        $team_1 = $this->getReference('Team 1', Team::class);
        $existingAssignment = $manager->getRepository(PlayerTeamAssignment::class)->findOneBy([
            'player' => $player_1_1,
            'team' => $team_1,
        ]);
        if (!$existingAssignment) {
            $pta = new PlayerTeamAssignment();
            $pta->setPlayer($player_1_1);
            $pta->setTeam($team_1);
            $pta->setStartDate(new DateTimeImmutable('2023-01-01'));
            $pta->setPlayerTeamAssignmentType($this->getReference('player_team_assignment_type_vertragsspieler', PlayerTeamAssignmentType::class));
            $manager->persist($pta);
        }

        $manager->flush();
    }

    private function generateBirthDate(AgeGroup $ageGroup): DateTimeImmutable
    {
        // Basierend auf der Altersgruppe das passende Geburtsjahr berechnen
        $now = new DateTimeImmutable();
        $year = $now->format('Y');

        // Alter basierend auf Altersgruppe bestimmen
        $age = match ($ageGroup->getName()) {
            'Senioren' => random_int(18, 35),
            'A-Junioren' => random_int(17, 18),
            'B-Junioren' => random_int(15, 16),
            'C-Junioren' => random_int(13, 14),
            'D-Junioren' => random_int(11, 12),
            'E-Junioren' => random_int(9, 10),
            'F-Junioren' => random_int(7, 8),
            'G-Junioren' => random_int(5, 6),
            default => random_int(18, 35),
        };

        $birthYear = (int) $year - $age;

        return new DateTimeImmutable(sprintf(
            '%d-%02d-%02d',
            $birthYear,
            random_int(1, 12),
            random_int(1, 28)
        ));
    }
}
