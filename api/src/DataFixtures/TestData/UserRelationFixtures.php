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
        // Zusätzliche Relationen für Test-User mit ROLE_USER (user6, user7, user8)
        $user6 = $this->getReference('user_6', User::class); // ROLE_USER
        $user7 = $this->getReference('user_7', User::class); // ROLE_USER
        $user8 = $this->getReference('user_8', User::class); // ROLE_USER
        $player1_1 = $this->getReference('player_1_1', Player::class); // Team 1
        $player_2_1 = $this->getReference('player_2_1', Player::class); // Team 1
        $player_3_2 = $this->getReference('player_3_2', Player::class); // Team 2
        $relationTypeParent = $this->getReference('relation_type_parent', RelationType::class);

        // user6 -> Elternteil von Spieler 1 in Team 1
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $user6,
            'player' => $player1_1,
            'relationType' => $relationTypeParent,
        ]);
        if (!$existing) {
            $rel = new UserRelation();
            $rel->setUser($user6);
            $rel->setPlayer($player1_1);
            $rel->setRelationType($relationTypeParent);
            $manager->persist($rel);
        }

        // user7 -> Elternteil von Spieler 2 in Team 1
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $user7,
            'player' => $player_2_1,
            'relationType' => $relationTypeParent,
        ]);
        if (!$existing) {
            $rel = new UserRelation();
            $rel->setUser($user7);
            $rel->setPlayer($player_2_1);
            $rel->setRelationType($relationTypeParent);
            $manager->persist($rel);
        }

        // user8 -> Elternteil von Spieler 3 in Team 2
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $user8,
            'player' => $player_3_2,
            'relationType' => $relationTypeParent,
        ]);
        if (!$existing) {
            $rel = new UserRelation();
            $rel->setUser($user8);
            $rel->setPlayer($player_3_2);
            $rel->setRelationType($relationTypeParent);
            $manager->persist($rel);
        }

        $userMutterVonSpielerEinsTeamEins = $this->getReference('user_1', User::class);
        $playerEinsTeamEins = $this->getReference('player_1_1', Player::class);

        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userMutterVonSpielerEinsTeamEins,
            'player' => $playerEinsTeamEins,
            'relationType' => $this->getReference('relation_type_parent', RelationType::class),
        ]);
        if (!$existing) {
            $userRelationMutterVonSpielerEinsTeamEins = new UserRelation();
            $userRelationMutterVonSpielerEinsTeamEins->setPlayer($playerEinsTeamEins);
            $userRelationMutterVonSpielerEinsTeamEins->setUser($userMutterVonSpielerEinsTeamEins);
            $userRelationMutterVonSpielerEinsTeamEins->setRelationType($this->getReference('relation_type_parent', RelationType::class));
            $manager->persist($userRelationMutterVonSpielerEinsTeamEins);
        }

        $userVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier = $this->getReference('user_5', User::class);
        $playerZehnTeamZwei = $this->getReference('player_10_2', Player::class);
        $coachTeamVier = $this->getReference('coach_7', Coach::class);

        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier,
            'player' => $playerZehnTeamZwei,
            'coach' => $coachTeamVier,
            'relationType' => $this->getReference('relation_type_parent', RelationType::class),
        ]);
        if (!$existing) {
            $userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier = new UserRelation();
            $userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier->setPlayer($playerZehnTeamZwei);
            $userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier->setUser($userVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier);
            $userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier->setRelationType($this->getReference('relation_type_parent', RelationType::class));
            $userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier->setCoach($coachTeamVier);
            $manager->persist($userRelationVaterVonSpielerZehnTeamZweiUndCoachVonTeamVier);
        }

        // Bruder von Spieler 2 in Team 1
        $userBruderVonSpieler2 = $this->getReference('user_2', User::class);
        $player2Team1 = $this->getReference('player_2_1', Player::class);
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userBruderVonSpieler2,
            'player' => $player2Team1,
            'relationType' => $this->getReference('relation_type_sibling', RelationType::class),
        ]);
        if (!$existing) {
            $relationBruder = new UserRelation();
            $relationBruder->setUser($userBruderVonSpieler2);
            $relationBruder->setPlayer($player2Team1);
            $relationBruder->setRelationType($this->getReference('relation_type_sibling', RelationType::class));
            $manager->persist($relationBruder);
        }

        // Vater von Spieler 3 in Team 2
        $userVaterVonSpieler3 = $this->getReference('user_3', User::class);
        $player3Team2 = $this->getReference('player_3_2', Player::class);
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userVaterVonSpieler3,
            'player' => $player3Team2,
            'relationType' => $this->getReference('relation_type_parent', RelationType::class),
        ]);
        if (!$existing) {
            $relationVater = new UserRelation();
            $relationVater->setUser($userVaterVonSpieler3);
            $relationVater->setPlayer($player3Team2);
            $relationVater->setRelationType($this->getReference('relation_type_parent', RelationType::class));
            $manager->persist($relationVater);
        }

        // Freund von Spieler 4 (Team 1) und Spieler 5 (Team 2)
        $userFreundMehrererSpieler = $this->getReference('user_4', User::class);
        $player4Team1 = $this->getReference('player_4_1', Player::class);
        $player5Team2 = $this->getReference('player_5_2', Player::class);
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userFreundMehrererSpieler,
            'player' => $player4Team1,
            'relationType' => $this->getReference('relation_type_friend', RelationType::class),
        ]);
        if (!$existing) {
            $relationFriend1 = new UserRelation();
            $relationFriend1->setUser($userFreundMehrererSpieler);
            $relationFriend1->setPlayer($player4Team1);
            $relationFriend1->setRelationType($this->getReference('relation_type_friend', RelationType::class));
            $manager->persist($relationFriend1);
        }
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userFreundMehrererSpieler,
            'player' => $player5Team2,
            'relationType' => $this->getReference('relation_type_friend', RelationType::class),
        ]);
        if (!$existing) {
            $relationFriend2 = new UserRelation();
            $relationFriend2->setUser($userFreundMehrererSpieler);
            $relationFriend2->setPlayer($player5Team2);
            $relationFriend2->setRelationType($this->getReference('relation_type_friend', RelationType::class));
            $manager->persist($relationFriend2);
        }

        // Freund von Coach 2 und Coach 3 (verschiedene Teams)
        $userFreundMehrererCoaches = $this->getReference('user_5', User::class);
        $coach2 = $this->getReference('coach_2', Coach::class);
        $coach3 = $this->getReference('coach_3', Coach::class);
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userFreundMehrererCoaches,
            'coach' => $coach2,
            'relationType' => $this->getReference('relation_type_friend', RelationType::class),
        ]);
        if (!$existing) {
            $relationFriendCoach2 = new UserRelation();
            $relationFriendCoach2->setUser($userFreundMehrererCoaches);
            $relationFriendCoach2->setCoach($coach2);
            $relationFriendCoach2->setRelationType($this->getReference('relation_type_friend', RelationType::class));
            $manager->persist($relationFriendCoach2);
        }
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userFreundMehrererCoaches,
            'coach' => $coach3,
            'relationType' => $this->getReference('relation_type_friend', RelationType::class),
        ]);
        if (!$existing) {
            $relationFriendCoach3 = new UserRelation();
            $relationFriendCoach3->setUser($userFreundMehrererCoaches);
            $relationFriendCoach3->setCoach($coach3);
            $relationFriendCoach3->setRelationType($this->getReference('relation_type_friend', RelationType::class));
            $manager->persist($relationFriendCoach3);
        }

        // User ist Bruder von Spieler 2 und Freund von Coach 3
        $userBruderUndFreund = $this->getReference('user_2', User::class);
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userBruderUndFreund,
            'coach' => $coach3,
            'relationType' => $this->getReference('relation_type_friend', RelationType::class),
        ]);
        if (!$existing) {
            $relationBruderUndFreund = new UserRelation();
            $relationBruderUndFreund->setUser($userBruderUndFreund);
            $relationBruderUndFreund->setCoach($coach3);
            $relationBruderUndFreund->setRelationType($this->getReference('relation_type_friend', RelationType::class));
            $manager->persist($relationBruderUndFreund);
        }

        // User ist Mentor von Coach 5 und Freund von Spieler 4
        $userMentorUndFreund = $this->getReference('user_3', User::class);
        $coach5 = $this->getReference('coach_5', Coach::class);
        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userMentorUndFreund,
            'coach' => $coach5,
            'relationType' => $this->getReference('relation_type_mentor', RelationType::class),
        ]);
        if (!$existing) {
            $relationMentor = new UserRelation();
            $relationMentor->setUser($userMentorUndFreund);
            $relationMentor->setCoach($coach5);
            $relationMentor->setRelationType($this->getReference('relation_type_mentor', RelationType::class));
            $manager->persist($relationMentor);
        }

        $existing = $manager->getRepository(UserRelation::class)->findOneBy([
            'user' => $userMentorUndFreund,
            'player' => $player4Team1,
            'relationType' => $this->getReference('relation_type_friend', RelationType::class),
        ]);
        if (!$existing) {
            $relationFriendSpieler4 = new UserRelation();
            $relationFriendSpieler4->setUser($userMentorUndFreund);
            $relationFriendSpieler4->setPlayer($player4Team1);
            $relationFriendSpieler4->setRelationType($this->getReference('relation_type_friend', RelationType::class));
            $manager->persist($relationFriendSpieler4);
        }

        $manager->flush();
    }
}
