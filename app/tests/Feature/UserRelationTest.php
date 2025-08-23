<?php

namespace Tests\Feature;

use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRelationTest extends KernelTestCase
{
    public function testUserWithMultiplePlayerRelationsSeesAllRelevantPlayersAndTeams(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'user4@example.com']);
        $player4 = $entityManager->getRepository(Player::class)->findOneBy([
            'firstName' => 'Player_4',
            'lastName' => 'Team 1 / Club 1'
        ]);
        $player5Team2Club2 = $entityManager->getRepository(Player::class)->findOneBy([
            'firstName' => 'Player_5',
            'lastName' => 'Team 2 / Club 2'
        ]);

        // Prüfe, ob die Relationen korrekt bestehen
        $relationPlayers = [];
        foreach ($user->getRelations() as $rel) {
            if ($rel->getPlayer()) {
                $relationPlayers[] = $rel->getPlayer()->getId();
            }
        }
        self::assertContains($player4->getId(), $relationPlayers, 'User ist mit Player 4 befreundet');
        self::assertContains($player5Team2Club2->getId(), $relationPlayers, 'User ist mit Player 33 befreundet');

        $players = $entityManager->getRepository(Player::class)->fetchOptimizedList($user);
        $teams = $entityManager->getRepository(Team::class)->fetchOptimizedList($user);

        $playerIds = array_map(fn ($p) => $p->getId(), $players);
        self::assertContains($player4->getId(), $playerIds);
        self::assertContains($player5Team2Club2->getId(), $playerIds);
        self::assertNotEmpty($teams, 'User mit mehreren Spieler-Relationen sieht Teams');
    }

    public function testUserWithMultipleCoachRelationsSeesAllRelevantCoachesAndTeams(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'user3@example.com']);
        $coach5 = $entityManager->getRepository(Coach::class)->findOneBy([
            'firstName' => 'Coach',
            'lastName' => '5'
        ]);
        $player4 = $entityManager->getRepository(Player::class)->findOneBy([
            'firstName' => 'Player_4',
            'lastName' => 'Team 1 / Club 1'
        ]);

        // Prüfe Relationen
        $relationCoaches = [];
        $relationPlayers = [];
        foreach ($user->getRelations() as $rel) {
            if ($rel->getCoach()) {
                $relationCoaches[] = $rel->getCoach()->getId();
            }
            if ($rel->getPlayer()) {
                $relationPlayers[] = $rel->getPlayer()->getId();
            }
        }
        self::assertContains($coach5->getId(), $relationCoaches, 'User ist Mentor von Coach 5');
        self::assertContains($player4->getId(), $relationPlayers, 'User ist Freund von Player 4');

        $coaches = $entityManager->getRepository(Coach::class)->fetchOptimizedList($user);
        $players = $entityManager->getRepository(Player::class)->fetchOptimizedList($user);
        $teams = $entityManager->getRepository(Team::class)->fetchOptimizedList($user);

        $coachIds = array_map(fn ($c) => $c->getId(), $coaches);
        $playerIds = array_map(fn ($p) => $p->getId(), $players);
        self::assertContains($coach5->getId(), $coachIds);
        self::assertContains($player4->getId(), $playerIds);
        self::assertNotEmpty($teams, 'User mit gemischten Relationen sieht Teams');
    }

    public function testUserWithDoubleRoleSeesAllRelevantEntities(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'user2@example.com']);
        $coach3 = $entityManager->getRepository(Coach::class)->findOneBy([
            'firstName' => 'Coach',
            'lastName' => '3'
        ]);
        $player2 = $entityManager->getRepository(Player::class)->findOneBy([
            'firstName' => 'Player_2',
            'lastName' => 'Team 1 / Club 1'
        ]);

        // Prüfe Relationen
        $relationCoaches = [];
        $relationPlayers = [];
        foreach ($user->getRelations() as $rel) {
            if ($rel->getCoach()) {
                $relationCoaches[] = $rel->getCoach()->getId();
            }
            if ($rel->getPlayer()) {
                $relationPlayers[] = $rel->getPlayer()->getId();
            }
        }
        self::assertContains($coach3->getId(), $relationCoaches, 'User ist Freund von Coach 3');
        self::assertContains($player2->getId(), $relationPlayers, 'User ist Bruder von Player 2');

        $coaches = $entityManager->getRepository(Coach::class)->fetchOptimizedList($user);
        $players = $entityManager->getRepository(Player::class)->fetchOptimizedList($user);
        $teams = $entityManager->getRepository(Team::class)->fetchOptimizedList($user);

        $coachIds = array_map(fn ($c) => $c->getId(), $coaches);
        $playerIds = array_map(fn ($p) => $p->getId(), $players);
        self::assertContains($coach3->getId(), $coachIds);
        self::assertContains($player2->getId(), $playerIds);
        self::assertNotEmpty($teams, 'User mit doppelter Rolle sieht Teams');
    }

    public function testUserWithoutAnyRelationsDontSeeAnyPlayerOrAnyTeam(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        // Verwende einen existierenden User ohne UserRelation, z.B. user6@example.com (user_6)
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'user6@example.com']);

        $teams = $entityManager->getRepository(Team::class)->fetchOptimizedList($user);
        $players = $entityManager->getRepository(Player::class)->fetchOptimizedList($user);
        $coaches = $entityManager->getRepository(Coach::class)->fetchOptimizedList($user);

        self::assertEmpty($teams, 'User ohne Relationen sieht keine Teams');
        self::assertEmpty($players, 'User ohne Relationen sieht keine Spieler');
        self::assertEmpty($coaches, 'User ohne Relationen sieht keine Coaches');
    }

    public function testUserWithRelationToPlayerCanSeePlayerAndHisTeam(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'user3@example.com']); // z.B. Vater von Spieler 3
        $player = $entityManager->getRepository(Player::class)->findOneBy([
            'firstName' => 'Player_3',
            'lastName' => 'Team 2 / Club 2'
        ]);

        $teams = $entityManager->getRepository(Team::class)->fetchOptimizedList($user);
        $players = $entityManager->getRepository(Player::class)->fetchOptimizedList($user);
        $coaches = $entityManager->getRepository(Coach::class)->fetchOptimizedList($user);

        $playerIds = array_map(fn ($p) => $p->getId(), $players);
        $teamNames = array_map(fn ($t) => is_array($t) ? $t['name'] : $t->getName(), $teams);

        self::assertContains($player->getId(), $playerIds, 'User mit Relation zu Spieler sieht diesen Spieler');
        self::assertNotEmpty($teamNames, 'User mit Relation zu Spieler sieht mindestens ein Team');

        // User soll die Coaches aller Teams seiner Spieler sehen (über CoachTeamAssignments)
        $coachIds = array_map(fn ($c) => $c->getId(), $coaches);
        $playerTeams = [];
        foreach ($player->getPlayerTeamAssignments() as $assignment) {
            $team = $assignment->getTeam();
            if ($team) {
                $playerTeams[] = $team;
            }
        }
        foreach ($playerTeams as $team) {
            foreach ($team->getCoachTeamAssignments() as $coachAssignment) {
                $coach = $coachAssignment->getCoach();
                if ($coach) {
                    self::assertContains($coach->getId(), $coachIds, 'User sieht Coach des Teams seines Spielers');
                }
            }
        }
    }

    public function testUserWithRelationToPlayerAndCoachCanSeeCoachHisTeamsAndPlayerAndHisTeams(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'user5@example.com']); // user_5: Freund von Coach 2 und Coach 3
        $coach2 = $entityManager->getRepository(Coach::class)->findOneBy([
            'firstName' => 'Coach',
            'lastName' => '2'
        ]);
        $coach3 = $entityManager->getRepository(Coach::class)->findOneBy([
            'firstName' => 'Coach',
            'lastName' => '3'
        ]);

        $teams = $entityManager->getRepository(Team::class)->fetchOptimizedList($user);
        $players = $entityManager->getRepository(Player::class)->fetchOptimizedList($user);
        $coaches = $entityManager->getRepository(Coach::class)->fetchOptimizedList($user);

        $coachIds = array_map(fn ($c) => $c->getId(), $coaches);
        $teamNames = array_map(fn ($t) => is_array($t) ? $t['name'] : $t->getName(), $teams);

        self::assertContains($coach2->getId(), $coachIds, 'User mit Relation zu Coach 2 sieht diesen Coach');
        self::assertContains($coach3->getId(), $coachIds, 'User mit Relation zu Coach 3 sieht diesen Coach');
        self::assertNotEmpty($teamNames, 'User mit Relation zu Coaches sieht Teams');

        // Überprüfe, ob die Teams, die der User sieht, tatsächlich Spieler enthalten (unabhängig von der User-Relation)
        foreach ($teams as $team) {
            $teamEntity = $entityManager->getRepository(Team::class)->find(is_array($team) ? $team['id'] : $team->getId());
            $playerAssignments = $teamEntity->getPlayerTeamAssignments();
            self::assertNotEmpty($playerAssignments, 'Jedes sichtbare Team hat mindestens einen Spieler (Team: ' . (is_array($team) ? $team['name'] : $team->getName()) . ')');
        }
    }
}
