<?php

namespace App\DataFixtures\TestData;

use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\RelationType;
use App\Entity\User;
use App\Entity\UserRelation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserRelationFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            PlayerFixtures::class,
            CoachFixtures::class,
            UserFixtures::class
        ];
    }

    /** @return list<string> */
    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $userMutterVonSpielerEinsTeamEins = $this->getReference('user_1', User::class);
        $playerEinsTeamEins = $this->getReference('player_1', Player::class);

        $userRelationMutterVonSpielerEinsTeamEins = new UserRelation();

        $userRelationMutterVonSpielerEinsTeamEins->setPlayer($playerEinsTeamEins);
        $userRelationMutterVonSpielerEinsTeamEins->setRelatedUser($userMutterVonSpielerEinsTeamEins);
        $userRelationMutterVonSpielerEinsTeamEins->setRelationType($this->getReference('relation_type_parent', RelationType::class));

        $manager->persist($userRelationMutterVonSpielerEinsTeamEins);

        $userVaterVonSpielerSechsundreisigTeamZweiUndCoachVonTeamVier = $this->getReference('user_10', User::class);
        $playerZehnTeamZwei = $this->getReference('player_10', Player::class);
        $coachTeamVier = $this->getReference('coach_7', Coach::class);

        $userRelationVaterVonSpielerSechsunddreisigTeamZweiUndCoachVonTeamVier = new UserRelation();
        $userRelationVaterVonSpielerSechsunddreisigTeamZweiUndCoachVonTeamVier->setPlayer($playerZehnTeamZwei);
        $userRelationVaterVonSpielerSechsunddreisigTeamZweiUndCoachVonTeamVier->setRelatedUser(
            $userVaterVonSpielerSechsundreisigTeamZweiUndCoachVonTeamVier
        );
        $userRelationVaterVonSpielerSechsunddreisigTeamZweiUndCoachVonTeamVier->setRelationType($this->getReference('relation_type_parent', RelationType::class));
        $userRelationVaterVonSpielerSechsunddreisigTeamZweiUndCoachVonTeamVier->setCoach($coachTeamVier);

        $manager->persist($userRelationVaterVonSpielerSechsunddreisigTeamZweiUndCoachVonTeamVier);

        $manager->flush();
    }
}
