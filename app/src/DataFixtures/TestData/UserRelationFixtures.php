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
        $playerEinsTeamEins = $this->getReference('player_1_1', Player::class);

        $userRelationMutterVonSpielerEinsTeamEins = new UserRelation();

        $userRelationMutterVonSpielerEinsTeamEins->setPlayer($playerEinsTeamEins);
        $userRelationMutterVonSpielerEinsTeamEins->setUser($userMutterVonSpielerEinsTeamEins);
        $userRelationMutterVonSpielerEinsTeamEins->setRelationType($this->getReference('relation_type_parent', RelationType::class));

        $manager->persist($userRelationMutterVonSpielerEinsTeamEins);

        $userVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier = $this->getReference('user_5', User::class);
        $playerZehnTeamZwei = $this->getReference('player_10_2', Player::class);
        $coachTeamVier = $this->getReference('coach_7', Coach::class);

        $userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier = new UserRelation();
        $userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier->setPlayer($playerZehnTeamZwei);
        $userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier->setUser(
            $userVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier
        );
        $userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier->setRelationType($this->getReference('relation_type_parent', RelationType::class));
        $userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier->setCoach($coachTeamVier);

        $manager->persist($userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier);

        // Bruder von Spieler 2 in Team 1
        $userBruderVonSpieler2 = $this->getReference('user_2', User::class);
        $player2Team1 = $this->getReference('player_2_1', Player::class);
        $relationBruder = new UserRelation();
        $relationBruder->setUser($userBruderVonSpieler2);
        $relationBruder->setPlayer($player2Team1);
        $relationBruder->setRelationType($this->getReference('relation_type_sibling', RelationType::class));
        $manager->persist($relationBruder);

        // Vater von Spieler 3 in Team 2
        $userVaterVonSpieler3 = $this->getReference('user_3', User::class);
        $player3Team2 = $this->getReference('player_3_2', Player::class);
        $relationVater = new UserRelation();
        $relationVater->setUser($userVaterVonSpieler3);
        $relationVater->setPlayer($player3Team2);
        $relationVater->setRelationType($this->getReference('relation_type_parent', RelationType::class));
        $manager->persist($relationVater);

        // Freund von Spieler 4 (Team 1) und Spieler 5 (Team 2)
        $userFreundMehrererSpieler = $this->getReference('user_4', User::class);
        $player4Team1 = $this->getReference('player_4_1', Player::class);
        $player5Team2 = $this->getReference('player_5_2', Player::class);
        $relationFriend1 = new UserRelation();
        $relationFriend1->setUser($userFreundMehrererSpieler);
        $relationFriend1->setPlayer($player4Team1);
        $relationFriend1->setRelationType($this->getReference('relation_type_friend', RelationType::class));
        $manager->persist($relationFriend1);
        $relationFriend2 = new UserRelation();
        $relationFriend2->setUser($userFreundMehrererSpieler);
        $relationFriend2->setPlayer($player5Team2);
        $relationFriend2->setRelationType($this->getReference('relation_type_friend', RelationType::class));
        $manager->persist($relationFriend2);

        // Freund von Coach 2 und Coach 3 (verschiedene Teams)
        $userFreundMehrererCoaches = $this->getReference('user_5', User::class);
        $coach2 = $this->getReference('coach_2', Coach::class);
        $coach3 = $this->getReference('coach_3', Coach::class);
        $relationFriendCoach2 = new UserRelation();
        $relationFriendCoach2->setUser($userFreundMehrererCoaches);
        $relationFriendCoach2->setCoach($coach2);
        $relationFriendCoach2->setRelationType($this->getReference('relation_type_friend', RelationType::class));
        $manager->persist($relationFriendCoach2);
        $relationFriendCoach3 = new UserRelation();
        $relationFriendCoach3->setUser($userFreundMehrererCoaches);
        $relationFriendCoach3->setCoach($coach3);
        $relationFriendCoach3->setRelationType($this->getReference('relation_type_friend', RelationType::class));
        $manager->persist($relationFriendCoach3);

        // User ist Bruder von Spieler 2 und Freund von Coach 3
        $userBruderUndFreund = $this->getReference('user_2', User::class);
        $relationBruderUndFreund = new UserRelation();
        $relationBruderUndFreund->setUser($userBruderUndFreund);
        $relationBruderUndFreund->setCoach($coach3);
        $relationBruderUndFreund->setRelationType($this->getReference('relation_type_friend', RelationType::class));
        $manager->persist($relationBruderUndFreund);

        // User ist Mentor von Coach 5 und Freund von Spieler 4
        $userMentorUndFreund = $this->getReference('user_3', User::class);
        $coach5 = $this->getReference('coach_5', Coach::class);
        $relationMentor = new UserRelation();
        $relationMentor->setUser($userMentorUndFreund);
        $relationMentor->setCoach($coach5);
        $relationMentor->setRelationType($this->getReference('relation_type_mentor', RelationType::class));
        $manager->persist($relationMentor);

        $relationFriendSpieler4 = new UserRelation();
        $relationFriendSpieler4->setUser($userMentorUndFreund);
        $relationFriendSpieler4->setPlayer($player4Team1);
        $relationFriendSpieler4->setRelationType($this->getReference('relation_type_friend', RelationType::class));
        $manager->persist($relationFriendSpieler4);

        $manager->flush();
    }
}
