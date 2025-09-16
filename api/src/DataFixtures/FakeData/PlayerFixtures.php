<?php

namespace App\DataFixtures\FakeData;

use App\DataFixtures\MasterData\PlayerTeamAssignmentTypeFixtures;
use App\DataFixtures\MasterData\PositionFixtures;
use App\DataFixtures\MasterData\StrongFootFixtures;
use App\Entity\Club;
use App\Entity\Nationality;
use App\Entity\Player;
use App\Entity\PlayerClubAssignment;
use App\Entity\PlayerNationalityAssignment;
use App\Entity\PlayerTeamAssignment;
use App\Entity\PlayerTeamAssignmentType;
use App\Entity\Position;
use App\Entity\StrongFoot;
use App\Entity\Team;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class PlayerFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            ClubFixtures::class,
            StrongFootFixtures::class,
            PositionFixtures::class,
            //            TeamFixtures::class,
            PlayerTeamAssignmentTypeFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['fake'];
    }

    public function load(ObjectManager $manager): void
    {
        echo 'Memory Limit NOW: ' . ini_get('memory_limit') . PHP_EOL;

        $faker = Factory::create('de_DE');
        $teams = $manager->getRepository(Team::class)->findAll();
        $clubs = $manager->getRepository(Club::class)->findAll();
        $strongFoots = $manager->getRepository(StrongFoot::class)->findAll();
        $nationalities = $manager->getRepository(Nationality::class)->findAll();
        $playerTeamAssignmentTypes = $manager->getRepository(PlayerTeamAssignmentType::class)->findAll();

        shuffle($clubs);
        shuffle($strongFoots);
        shuffle($nationalities);
        shuffle($playerTeamAssignmentTypes);

        list($positions, $alternativePositions) = $this->preparePositionsMap($manager);

        $batchSize = 100;
        $playerCount = 0;
        $attachedObjects = [];

        foreach ($teams as $team) {
            $usedShirtNumbers = [];
            for ($playerId = 0; $playerId < $faker->numberBetween(10, 26); ++$playerId) {
                $birthdate = $faker->dateTimeBetween('-60 years', '-5 years');
                $age = (new DateTime())->diff($birthdate)->y;

                $firstName = $faker->firstName;
                $lastName = $faker->lastName;
                // Prüfe, ob Spieler mit diesen Daten schon existiert
                $existingPlayer = $manager->getRepository(Player::class)->findOneBy([
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'birthdate' => $birthdate,
                ]);
                if ($existingPlayer) {
                    continue;
                }

                $playerEntity = new Player();
                $playerEntity->setFirstName($firstName);
                $playerEntity->setLastName($lastName);
                $playerEntity->setBirthdate($birthdate);
                $mainPosition = $faker->randomElement($positions);
                $playerEntity->setMainPosition($mainPosition);
                $playerEntity->setStrongFoot($faker->randomElement($strongFoots));

                $currentAlternativePositions = $this->retrieveAlternativePositions($mainPosition->getName(), $positions, $alternativePositions, $faker);

                foreach ($currentAlternativePositions as $currentAlternativePosition) {
                    $playerEntity->addAlternativePosition($currentAlternativePosition);
                }

                $playerClubAssignmentEntity = new PlayerClubAssignment();
                $playerClubAssignmentEntity->setPlayer($playerEntity);
                $playerClubAssignmentEntity->setClub($faker->randomElement($clubs));
                $playerClubAssignmentEntity->setStartDate($faker->dateTimeBetween('-30 years', '0 years'));
                $playerClubAssignmentEntity->setEndDate($faker->dateTimeBetween('-10 years', null));

                $manager->persist($playerClubAssignmentEntity);
                $playerEntity->addPlayerClubAssignment($playerClubAssignmentEntity);

                $playerTeamAssignmentEntity = new PlayerTeamAssignment();
                $playerTeamAssignmentEntity->setShirtNumber($this->retrieveUniqueShirtNumber($usedShirtNumbers, $faker));
                $playerTeamAssignmentEntity->setPlayer($playerEntity);
                $playerTeamAssignmentEntity->setTeam($team);
                $playerTeamAssignmentEntity->setStartDate($faker->dateTimeBetween('-15 years', '-7 years'));
                $playerTeamAssignmentEntity->setEndDate($faker->dateTimeBetween('-10 years', null));
                $playerTeamAssignmentEntity->setPlayerTeamAssignmentType($faker->randomElement($playerTeamAssignmentTypes));

                $manager->persist($playerTeamAssignmentEntity);
                $playerEntity->addPlayerTeamAssignment($playerTeamAssignmentEntity);

                $playerNationalityAssignment = new PlayerNationalityAssignment();
                $playerNationalityAssignment->setPlayer($playerEntity);
                $playerNationalityAssignment->setStartDate($faker->dateTimeBetween('-' . $age . ' years', '-1 years'));
                $playerNationalityAssignment->setEndDate($faker->dateTimeBetween('-' . $age . ' years', null));
                $playerNationalityAssignment->setNationality($faker->randomElement($nationalities));

                $manager->persist($playerNationalityAssignment);
                $playerEntity->addPlayerNationalityAssignment($playerNationalityAssignment);

                $manager->persist($playerEntity);

                $attachedObjects[] = $playerEntity;
                $attachedObjects[] = $playerClubAssignmentEntity;
                $attachedObjects[] = $playerTeamAssignmentEntity;
                $attachedObjects[] = $playerNationalityAssignment;

                ++$playerCount;

                if (0 === $playerCount % $batchSize) {
                    $manager->flush();

                    foreach ($attachedObjects as $attacheObject) {
                        $manager->detach($attacheObject);
                    }
                    $attachedObjects = [];
                }
            }
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param array<int> $usedShirtNumbers
     */
    private function retrieveUniqueShirtNumber(array &$usedShirtNumbers, Generator $faker): int
    {
        do {
            $shirtNumber = $faker->numberBetween(1, 26);
        } while (in_array($shirtNumber, $usedShirtNumbers));

        $usedShirtNumbers[] = $shirtNumber;

        return $shirtNumber;
    }

    /**
     * @return array{0: array<string, Position>, 1: array<string, array<int, string>>}
     */
    private function preparePositionsMap(ObjectManager $manager): array
    {
        $rawPositions = $manager->getRepository(Position::class)->findAll();
        $positions = [];

        foreach ($rawPositions as $rawPosition) {
            $positions[$rawPosition->getName()] = $rawPosition;
        }

        $alternativMap = [
            'Torwart' => ['Innenverteidiger', 'Stürmer'],
            'Innenverteidiger' => ['Linksverteidiger', 'Defensives Mittelfeld'],
            'Linksverteidiger' => ['Innenverteidiger', 'Rechtes Mittelfeld'],
            'Rechtsverteidiger' => ['Innenverteidiger', 'Rechtes Mittelfeld'],
            'Defensives Mittelfeld' => ['Zentrales Mittelfeld', 'Innenverteidiger'],
            'Zentrales Mittelfeld' => ['Offensives Mittelfeld', 'Rechtes Mittelfeld', 'Defensives Mittelfeld'],
            'Offensives Mittelfeld' => ['Stürmer', 'Zentrales Mittelfeld'],
            'Rechtes Mittelfeld' => ['Offensives Mittelfeld', 'Rechtsverteidiger'],
            'Linkes Mittelfeld' => ['Offensives Mittelfeld', 'Linksverteidiger'],
            'Stürmer' => ['Offensives Mittelfeld', 'Torwart'],
        ];

        return [
            $positions,
            $alternativMap
        ];
    }

    /**
     * @param array<string, Position>           $positions
     * @param array<string, array<int, string>> $alternativeMap
     *
     * @return array<int, Position>
     */
    private function retrieveAlternativePositions(string $mainPosition, array $positions, array $alternativeMap, Generator $faker): array
    {
        $alternativePositions = [];

        $alternativeNames = $alternativeMap[$mainPosition] ?? array_keys($positions);
        // Filtere die Hauptposition raus
        $alternativeNames = array_filter($alternativeNames, fn ($name) => $name !== $mainPosition);

        // Maximal 2 alternative Positionen
        $anzahlAlternativen = $faker->numberBetween(0, 2);
        $alternatives = $faker->randomElements($alternativeNames, $anzahlAlternativen);

        // Hole aus Namen wieder die Position-Objekte
        $alternativePositions = array_map(fn ($name) => $positions[$name], $alternatives);

        // 5% Chance für irgend eine position
        if ($faker->boolean(5)) {
            $alternativePositions[] = $faker->randomElement($positions);
        }

        return $alternativePositions;
    }
}
