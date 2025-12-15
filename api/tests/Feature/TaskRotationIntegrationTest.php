<?php

namespace Tests\Feature;

use App\Entity\AgeGroup;
use App\Entity\CalendarEvent;
use App\Entity\CalendarEventType;
use App\Entity\Game;
use App\Entity\GameType;
use App\Entity\Player;
use App\Entity\PlayerTeamAssignment;
use App\Entity\RelationType;
use App\Entity\Task;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserRelation;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskRotationIntegrationTest extends WebTestCase
{
    public function testTaskCreationWithMultipleUsersCreatesRotationCorrectly(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->cleanup($entityManager);
        $adminUser = $this->createAdminUser($entityManager);

        $ageGroup = $entityManager->getRepository(AgeGroup::class)->findOneBy([]);
        if (!$ageGroup) {
            $ageGroup = new AgeGroup();
            $ageGroup->setName('U19');
            $entityManager->persist($ageGroup);
            $entityManager->flush();
        }

        $team = new Team();
        $team->setName('Test Team 1');
        $team->setAgeGroup($ageGroup);
        $entityManager->persist($team);
        $entityManager->flush(); // Flush team first

        $user1 = $this->createUserWithPlayer($entityManager, 'test-task-user1@example.com', 'User', 'One', $team);
        $user2 = $this->createUserWithPlayer($entityManager, 'test-task-user2@example.com', 'User', 'Two', $team);

        for ($i = 1; $i <= 3; ++$i) {
            $this->createGame($entityManager, $team, "Test Game {$i}", "+{$i} week");
        }

        $entityManager->flush(); // Flush games

        $client->loginUser($adminUser);
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Test Task - Torwart',
            'description' => 'Torwart Aufgabe',
            'isRecurring' => true,
            'recurrenceMode' => 'per_match',
            'rotationUsers' => [$user1->getId(), $user2->getId()],
            'rotationCount' => 1,
        ]));

        if (201 !== $client->getResponse()->getStatusCode()) {
            dump($client->getResponse()->getContent());
        }

        $this->assertResponseStatusCodeSame(201);

        // Get fresh EntityManager after API call
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        $taskRepo = $entityManager->getRepository(Task::class);
        $task = $taskRepo->findOneBy(['title' => 'Test Task - Torwart']);

        $this->assertNotNull($task);
        $this->assertEquals('per_match', $task->getRecurrenceMode());
        $this->assertCount(2, $task->getRotationUsers());

        // Nach der neuen Architektur sind die Assignments mit den Occurrences (nicht dem Template) verknüpft
        // Diese haben seriesId = template->getId()
        $assignmentRepo = $entityManager->getRepository(\App\Entity\TaskAssignment::class);

        // Suche alle Occurrences dieser Serie
        $occurrences = $entityManager->getRepository(Task::class)
            ->findBy(['seriesId' => $task->getId()], ['assignedDate' => 'ASC']);

        $this->assertCount(3, $occurrences, 'Should have 3 task occurrences for 3 games');

        // Bekomme die Assignments für alle Occurrences
        $assignments = [];
        foreach ($occurrences as $occurrence) {
            $assignmentsForOccurrence = $assignmentRepo->findBy(['task' => $occurrence]);
            $assignments = array_merge($assignments, $assignmentsForOccurrence);
        }

        $this->assertCount(3, $assignments, 'Should have 3 task assignments for 3 games');
        usort($assignments, fn ($a, $b) => $a->getTask()->getAssignedDate()?->getTimestamp() <=> $b->getTask()->getAssignedDate()?->getTimestamp());

        $this->assertEquals($user1->getId(), $assignments[0]->getUser()->getId());
        $this->assertEquals($user2->getId(), $assignments[1]->getUser()->getId());
        $this->assertEquals($user1->getId(), $assignments[2]->getUser()->getId());

        // Hole alle CalendarEvents für diese Serie-Occurrences
        $calendarEvents = [];
        foreach ($occurrences as $occurrence) {
            $assignmentsForOccurrence = $assignmentRepo->findBy(['task' => $occurrence]);
            foreach ($assignmentsForOccurrence as $assignment) {
                if ($assignment->getCalendarEvent()) {
                    $calendarEvents[] = $assignment->getCalendarEvent();
                }
            }
        }

        // Sort by start date
        usort($calendarEvents, fn ($a, $b) => $a->getStartDate()->getTimestamp() <=> $b->getStartDate()->getTimestamp());

        $this->assertCount(3, $calendarEvents, 'Should have 3 calendar events for 3 games');

        // Verifiziere, dass die Assignments die richtigen User haben
        $this->assertEquals($user1->getId(), $assignments[0]->getUser()->getId());
        $this->assertEquals($user2->getId(), $assignments[1]->getUser()->getId());
        $this->assertEquals($user1->getId(), $assignments[2]->getUser()->getId());
    }

    public function testNewGameCreationTriggersTaskRegeneration(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->cleanup($entityManager);
        $adminUser = $this->createAdminUser($entityManager);

        $ageGroup = $entityManager->getRepository(AgeGroup::class)->findOneBy([]);

        $team = new Team();
        $team->setName('Test Team 2');
        $team->setAgeGroup($ageGroup);
        $entityManager->persist($team);

        $user = $this->createUserWithPlayer($entityManager, 'test-task-user3@example.com', 'User', 'Three', $team);
        $game1 = $this->createGame($entityManager, $team, 'Test Game Initial', '+1 week');

        $entityManager->flush();

        $client->loginUser($adminUser);
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Test Task - Auto Update',
            'description' => 'Should auto-update',
            'isRecurring' => true,
            'recurrenceMode' => 'per_match',
            'rotationUsers' => [$user->getId()],
            'rotationCount' => 1,
        ]));

        $this->assertResponseStatusCodeSame(201);

        // Get fresh EntityManager after API call
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        $taskRepo = $entityManager->getRepository(Task::class);
        $task = $taskRepo->findOneBy(['title' => 'Test Task - Auto Update']);

        $this->assertNotNull($task, 'Template task should be created by POST /api/tasks');

        // Find assignments linked to occurrences of this template
        $initialAssignments = $this->getAssignmentsForTemplate($task, $entityManager);

        $this->assertCount(1, $initialAssignments);

        // Create a new game directly and dispatch event
        // Re-fetch team from DB after clear
        $team = $entityManager->getRepository(Team::class)->find($team->getId());
        $game2 = $this->createGame($entityManager, $team, 'Test Game New', '+2 weeks');
        $entityManager->flush();

        // Dispatch GameCreatedEvent manually to trigger task regeneration
        $eventDispatcher = static::getContainer()->get('event_dispatcher');
        $gameCreatedEvent = new \App\Event\GameCreatedEvent($game2);
        $eventDispatcher->dispatch($gameCreatedEvent);

        $entityManager->clear();

        // Re-fetch repositories after clear
        $taskRepo = $entityManager->getRepository(Task::class);
        $task = $taskRepo->findOneBy(['title' => 'Test Task - Auto Update']);

        // Find assignments linked to occurrences of this template
        $updatedAssignments = $this->getAssignmentsForTemplate($task, $entityManager);

        $this->assertCount(2, $updatedAssignments, 'Should have 2 assignments after new game creation');
    }

    public function testGameDeletionTriggersTaskRegeneration(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->cleanup($entityManager);
        $adminUser = $this->createAdminUser($entityManager);

        $ageGroup = $entityManager->getRepository(AgeGroup::class)->findOneBy([]);

        $team = new Team();
        $team->setName('Test Team 3');
        $team->setAgeGroup($ageGroup);
        $entityManager->persist($team);

        $user = $this->createUserWithPlayer($entityManager, 'test-task-user4@example.com', 'User', 'Four', $team);

        $game1 = $this->createGame($entityManager, $team, 'Test Game Del 1', '+1 week');
        $game2 = $this->createGame($entityManager, $team, 'Test Game Del 2', '+2 weeks');

        $entityManager->flush();

        $client->loginUser($adminUser);
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Test Task - Delete Game',
            'description' => 'Should handle deletion',
            'isRecurring' => true,
            'recurrenceMode' => 'per_match',
            'rotationUsers' => [$user->getId()],
            'rotationCount' => 1,
        ]));

        $this->assertResponseStatusCodeSame(201);

        // Get fresh EntityManager after API call
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        $taskRepo = $entityManager->getRepository(Task::class);
        $task = $taskRepo->findOneBy(['title' => 'Test Task - Delete Game']);

        $initialAssignments = $this->getAssignmentsForTemplate($task, $entityManager);
        $this->assertCount(2, $initialAssignments);

        // Actually delete the game FIRST
        // Re-fetch game from DB after clear
        $game1 = $entityManager->getRepository(Game::class)->find($game1->getId());
        $calendarEvent = $game1->getCalendarEvent();
        $entityManager->remove($game1);
        $entityManager->remove($calendarEvent);
        $entityManager->flush();

        // THEN dispatch GameDeletedEvent to trigger task regeneration
        $eventDispatcher = static::getContainer()->get('event_dispatcher');
        $gameDeletedEvent = new \App\Event\GameDeletedEvent($game1);
        $eventDispatcher->dispatch($gameDeletedEvent);

        $entityManager->clear();

        // Re-fetch repositories after clear
        $taskRepo = $entityManager->getRepository(Task::class);
        $task = $taskRepo->findOneBy(['title' => 'Test Task - Delete Game']);

        $updatedAssignments = $this->getAssignmentsForTemplate($task, $entityManager);

        $this->assertCount(1, $updatedAssignments, 'Should have 1 assignment after game deletion');
    }

    public function testTaskOnlyCreatedForUserTeams(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->cleanup($entityManager);
        $adminUser = $this->createAdminUser($entityManager);

        $ageGroup = $entityManager->getRepository(AgeGroup::class)->findOneBy([]);

        $team1 = new Team();
        $team1->setName('Test Team User');
        $team1->setAgeGroup($ageGroup);
        $entityManager->persist($team1);

        $team2 = new Team();
        $team2->setName('Test Team Other');
        $team2->setAgeGroup($ageGroup);
        $entityManager->persist($team2);

        $user = $this->createUserWithPlayer($entityManager, 'test-task-user5@example.com', 'User', 'Five', $team1);

        $gameTeam1 = $this->createGame($entityManager, $team1, 'Test Game User Team', '+1 week');
        $gameTeam2 = $this->createGame($entityManager, $team2, 'Test Game Other Team', '+2 weeks');

        $entityManager->flush();

        $client->loginUser($adminUser);
        $client->request('POST', '/api/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Test Task - Team Filter',
            'description' => 'Only for user teams',
            'isRecurring' => true,
            'recurrenceMode' => 'per_match',
            'rotationUsers' => [$user->getId()],
            'rotationCount' => 1,
        ]));

        $this->assertResponseStatusCodeSame(201);

        // Get fresh EntityManager after API call
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        $taskRepo = $entityManager->getRepository(Task::class);
        $task = $taskRepo->findOneBy(['title' => 'Test Task - Team Filter']);

        $assignments = $this->getAssignmentsForTemplate($task, $entityManager);

        $this->assertCount(1, $assignments, 'Should only have 1 assignment for user team game');
    }

    private function cleanup(EntityManagerInterface $entityManager): void
    {
        $connection = $entityManager->getConnection();

        try {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

            $connection->executeStatement('DELETE FROM task_assignment WHERE 1=1');
            $connection->executeStatement("DELETE FROM calendar_events WHERE title LIKE 'Test Task%'");
            $connection->executeStatement('DELETE FROM task_rotation_users WHERE 1=1');
            $connection->executeStatement("DELETE FROM task WHERE title LIKE 'Test Task%'");
            $connection->executeStatement('DELETE FROM games WHERE 1=1');
            $connection->executeStatement("DELETE FROM calendar_events WHERE title LIKE 'Test Game%'");
            $connection->executeStatement('DELETE FROM user_relation WHERE 1=1');
            $connection->executeStatement('DELETE FROM coach_team_assignments WHERE 1=1');
            $connection->executeStatement('DELETE FROM player_team_assignments WHERE 1=1');
            $connection->executeStatement('DELETE FROM coaches WHERE 1=1');
            $connection->executeStatement('DELETE FROM players WHERE 1=1');
            $connection->executeStatement("DELETE FROM users WHERE email LIKE 'test-task-%'");
            $connection->executeStatement("DELETE FROM teams WHERE name LIKE 'Test Team%'");

            $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }

    private function createAdminUser(EntityManagerInterface $entityManager): User
    {
        $existingUser = $entityManager->getRepository(User::class)
            ->findOneBy(['email' => 'test-task-admin@example.com']);

        if ($existingUser) {
            return $existingUser;
        }

        $user = new User();
        $user->setEmail('test-task-admin@example.com');
        $user->setPassword('$2y$13$dummy');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsVerified(true);
        $user->setIsEnabled(true);
        $user->setFirstName('Admin');
        $user->setLastName('User');

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    private function createUserWithPlayer(EntityManagerInterface $entityManager, string $email, string $firstName, string $lastName, Team $team): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword('$2y$13$dummy');
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setIsVerified(true);
        $user->setIsEnabled(true);
        $entityManager->persist($user);

        $position = $entityManager->getRepository(\App\Entity\Position::class)->findOneBy([]);
        if (!$position) {
            throw new RuntimeException('No position found in database. Ensure fixtures are loaded.');
        }

        $player = new Player();
        $player->setFirstName($firstName);
        $player->setLastName($lastName);
        $player->setEmail($email);
        $player->setMainPosition($position);
        $entityManager->persist($player);

        $playerAssignment = new PlayerTeamAssignment();
        $playerAssignment->setTeam($team);
        $player->addPlayerTeamAssignment($playerAssignment);
        $entityManager->persist($playerAssignment);

        $relationType = $entityManager->getRepository(RelationType::class)
            ->findOneBy(['identifier' => 'self_player']);

        if (!$relationType) {
            $relationType = new RelationType();
            $relationType->setIdentifier('self_player');
            $relationType->setCategory('player');
            $relationType->setName('Eigener Spieler');
            $entityManager->persist($relationType);
        }

        $userRelation = new UserRelation();
        $userRelation->setUser($user);
        $userRelation->setPlayer($player);
        $userRelation->setRelationType($relationType);
        $entityManager->persist($userRelation);

        return $user;
    }

    private function createGame(EntityManagerInterface $entityManager, Team $homeTeam, string $title, string $dateModifier): Game
    {
        $spielEventType = $entityManager->getRepository(CalendarEventType::class)
            ->findOneBy(['name' => 'Spiel']);

        if (!$spielEventType) {
            throw new RuntimeException('Spiel CalendarEventType not found. Ensure fixtures are loaded.');
        }

        $gameType = $entityManager->getRepository(GameType::class)->findOneBy([])
            ?? $this->createGameType($entityManager);

        $calendarEvent = new CalendarEvent();
        $calendarEvent->setTitle($title);
        $calendarEvent->setStartDate(new DateTimeImmutable($dateModifier));
        $calendarEvent->setEndDate(new DateTimeImmutable($dateModifier . ' +2 hours'));
        $calendarEvent->setCalendarEventType($spielEventType);
        $entityManager->persist($calendarEvent);

        $game = new Game();
        $game->setHomeTeam($homeTeam);
        $game->setAwayTeam($homeTeam);
        $game->setGameType($gameType);
        $game->setCalendarEvent($calendarEvent);
        $calendarEvent->setGame($game);
        $entityManager->persist($game);

        $entityManager->flush();  // Flush to make sure games are in database

        return $game;
    }

    private function createGameType(EntityManagerInterface $entityManager): GameType
    {
        $gameType = new GameType();
        $gameType->setName('Test Game Type');
        $entityManager->persist($gameType);

        return $gameType;
    }

    /**
     * @return array<\App\Entity\TaskAssignment>
     */
    private function getAssignmentsForTemplate(Task $template, EntityManagerInterface $entityManager): array
    {
        $occurrences = $entityManager->getRepository(Task::class)
            ->findBy(['seriesId' => $template->getId()]);

        $assignments = [];
        $assignmentRepo = $entityManager->getRepository(\App\Entity\TaskAssignment::class);
        foreach ($occurrences as $occurrence) {
            $occurrenceAssignments = $assignmentRepo->findBy(['task' => $occurrence]);
            $assignments = array_merge($assignments, $occurrenceAssignments);
        }

        return $assignments;
    }
}
