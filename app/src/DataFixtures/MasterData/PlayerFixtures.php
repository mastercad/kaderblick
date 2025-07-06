<?php

namespace App\DataFixtures\MasterData;

use App\Entity\Player;
use App\Entity\Position;
use App\Entity\StrongFoot;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PlayerFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            ClubFixtures::class,
            StrongFootFixtures::class,
            PositionFixtures::class,
            TeamFixtures::class,
            PlayerTeamAssignmentTypeFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $rightFoot = $manager->getRepository(StrongFoot::class)->findOneBy(['code' => 'Rechts']);
        $bothFoot = $manager->getRepository(StrongFoot::class)->findOneBy(['code' => 'Beidfüßig']);

        $posStuermer = $manager->getRepository(Position::class)->findOneBy(['shortName' => 'ST']);
        $posRechteAbwehr = $manager->getRepository(Position::class)->findOneBy(['shortName' => 'RV']);
        $posRechtsAussen = $manager->getRepository(Position::class)->findOneBy(['shortName' => 'RA']);
        $posMittelfeld = $manager->getRepository(Position::class)->findOneBy(['shortName' => 'ZM']);

        $players = [
            [
                'firstName' => 'Justin',
                'lastName' => 'Wetzig',
                'birthDate' => new DateTimeImmutable('2008-03-07'),
                'strongFoot' => $rightFoot,
                'mainPosition' => $posStuermer,
                'alternatePositions' => [
                    $posStuermer,
                    $posRechteAbwehr,
                    $posMittelfeld
                ],
            ],
            [
                'firstName' => 'Moritz',
                'lastName' => 'Eichler',
                'birthDate' => new DateTimeImmutable('2008-10-06'),
                'strongFoot' => $bothFoot,
                'mainPosition' => $posStuermer,
                'alternatePositions' => [
                    $posStuermer,
                    $posRechteAbwehr,
                    $posRechtsAussen
                ],
            ],
        ];

        foreach ($players as $player) {
            $playerEntity = new Player();
            $playerEntity->setFirstName($player['firstName']);
            $playerEntity->setLastName($player['lastName']);
            $playerEntity->setMainPosition($player['mainPosition']);
            $playerEntity->setStrongFoot($player['strongFoot']);
            $playerEntity->setBirthdate($player['birthDate']);

            foreach ($player['alternatePositions'] as $position) {
                $playerEntity->addAlternativePosition($position);
            }

            $manager->persist($playerEntity);
        }

        $manager->flush();
        $manager->clear();
    }
}
