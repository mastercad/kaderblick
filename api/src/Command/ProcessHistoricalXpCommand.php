<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\GameEvent;
use App\Entity\Goal;
use App\Entity\User;
use App\Entity\UserLevel;
use App\Entity\UserXpEvent;
use App\Service\XPService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:xp:process-historical',
    description: 'Process historical events and award XP retroactively for events created before XP system was implemented'
)]
class ProcessHistoricalXpCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private XPService $xpService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Type of events to process (goals, game_events, calendar_events, profiles, all)', 'all')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Run without actually awarding XP')
            ->addOption('user-id', 'u', InputOption::VALUE_OPTIONAL, 'Process events only for specific user ID')
            ->setHelp(<<<'HELP'
This command processes historical events that were created before the XP system was implemented
and awards XP retroactively. It directly adds XP to users instead of creating XP events.

Examples:
  php bin/console app:xp:process-historical
  php bin/console app:xp:process-historical --type=goals
  php bin/console app:xp:process-historical --type=goals --user-id=5
  php bin/console app:xp:process-historical --dry-run

HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getOption('type');
        $dryRun = $input->getOption('dry-run');
        $userId = $input->getOption('user-id');

        if ($dryRun) {
            $io->warning('Running in DRY-RUN mode - no XP will be awarded');
        }

        $io->title('Processing Historical Events and Awarding XP Retroactively');
        $io->text('This will award XP for events created before the XP system was implemented.');

        $totalProcessed = 0;
        $totalXpAwarded = 0;

        try {
            if ($type === 'goals' || $type === 'all') {
                [$processed, $xpAwarded] = $this->processGoals($io, $dryRun, $userId);
                $totalProcessed += $processed;
                $totalXpAwarded += $xpAwarded;
                $io->success("Processed {$processed} goals, awarded {$xpAwarded} XP");
            }

            if ($type === 'game_events' || $type === 'all') {
                [$processed, $xpAwarded] = $this->processGameEvents($io, $dryRun, $userId);
                $totalProcessed += $processed;
                $totalXpAwarded += $xpAwarded;
                $io->success("Processed {$processed} game events, awarded {$xpAwarded} XP");
            }

            if ($type === 'calendar_events' || $type === 'all') {
                [$processed, $xpAwarded] = $this->processCalendarEvents($io, $dryRun, $userId);
                $totalProcessed += $processed;
                $totalXpAwarded += $xpAwarded;
                $io->success("Processed {$processed} calendar event participations, awarded {$xpAwarded} XP");
            }

            if ($type === 'profiles' || $type === 'all') {
                [$processed, $xpAwarded] = $this->processProfiles($io, $dryRun, $userId);
                $totalProcessed += $processed;
                $totalXpAwarded += $xpAwarded;
                $io->success("Processed {$processed} user profiles, awarded {$xpAwarded} XP");
            }

            $io->success("Total: {$totalProcessed} events processed, {$totalXpAwarded} XP awarded");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error processing historical events: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function processGoals(SymfonyStyle $io, bool $dryRun, ?string $userId): array
    {
        $io->section('Processing Goals (50 XP per goal, 30 XP per assist)');

        $qb = $this->entityManager->getRepository(Goal::class)
            ->createQueryBuilder('g')
            ->innerJoin('g.scorer', 's')
            ->leftJoin('s.userRelations', 'ur')
            ->leftJoin('ur.user', 'u')
            ->where('u.id IS NOT NULL');

        if ($userId) {
            $qb->andWhere('u.id = :userId')
               ->setParameter('userId', $userId);
        }

        $goals = $qb->getQuery()->getResult();
        $processed = 0;
        $totalXpAwarded = 0;

        foreach ($goals as $goal) {
            $scorer = $goal->getScorer();
            $users = $this->getUsersForPlayer($scorer);

            foreach ($users as $user) {
                // Check if XP already awarded for this goal
                if ($this->hasXpEventForAction($user, 'goal_scored', $goal->getId())) {
                    continue;
                }

                if (!$dryRun) {
                    $xp = $this->xpService->retrieveXPForAction('goal_scored');
                    $this->xpService->addXPToUser($user, $xp);
                    $this->createXpEventRecord($user, 'goal_scored', $goal->getId(), $xp);
                    $totalXpAwarded += $xp;
                    $processed++;
                    $io->writeln("  ✓ Goal #{$goal->getId()} → User #{$user->getId()} ({$user->getEmail()}) +{$xp} XP");
                } else {
                    $xp = $this->xpService->retrieveXPForAction('goal_scored');
                    $io->writeln("  [DRY-RUN] Would award {$xp} XP for goal #{$goal->getId()} to user #{$user->getId()} ({$user->getEmail()})");
                    $totalXpAwarded += $xp;
                    $processed++;
                }
            }

            // Process assists
            $assistant = $goal->getAssistBy();
            if ($assistant) {
                $assistUsers = $this->getUsersForPlayer($assistant);
                foreach ($assistUsers as $user) {
                    // Check if XP already awarded for this assist
                    if ($this->hasXpEventForAction($user, 'goal_assisted', $goal->getId())) {
                        continue;
                    }

                    if (!$dryRun) {
                        $xp = $this->xpService->retrieveXPForAction('goal_assisted');
                        $this->xpService->addXPToUser($user, $xp);
                        $this->createXpEventRecord($user, 'goal_assisted', $goal->getId(), $xp);
                        $totalXpAwarded += $xp;
                        $processed++;
                        $io->writeln("  ✓ Assist #{$goal->getId()} → User #{$user->getId()} ({$user->getEmail()}) +{$xp} XP");
                    } else {
                        $xp = $this->xpService->retrieveXPForAction('goal_assisted');
                        $io->writeln("  [DRY-RUN] Would award {$xp} XP for assist #{$goal->getId()} to user #{$user->getId()} ({$user->getEmail()})");
                        $totalXpAwarded += $xp;
                        $processed++;
                    }
                }
            }
        }

        return [$processed, $totalXpAwarded];
    }

    private function processGameEvents(SymfonyStyle $io, bool $dryRun, ?string $userId): array
    {
        $io->section('Processing Game Events (15 XP per event)');

        $qb = $this->entityManager->getRepository(GameEvent::class)
            ->createQueryBuilder('ge')
            ->leftJoin('ge.player', 'p')
            ->leftJoin('p.userRelations', 'ur')
            ->leftJoin('ur.user', 'u')
            ->where('u.id IS NOT NULL');

        if ($userId) {
            $qb->andWhere('u.id = :userId')
               ->setParameter('userId', $userId);
        }

        $gameEvents = $qb->getQuery()->getResult();
        $processed = 0;
        $totalXpAwarded = 0;

        foreach ($gameEvents as $gameEvent) {
            $player = $gameEvent->getPlayer();
            if (!$player) {
                continue;
            }

            $users = $this->getUsersForPlayer($player);

            foreach ($users as $user) {
                if ($this->hasXpEventForAction($user, 'game_event', $gameEvent->getId())) {
                    continue;
                }

                if (!$dryRun) {
                    $xp = $this->xpService->retrieveXPForAction('game_event');
                    $this->xpService->addXPToUser($user, $xp);
                    $this->createXpEventRecord($user, 'game_event', $gameEvent->getId(), $xp);
                    $totalXpAwarded += $xp;
                    $processed++;
                    $io->writeln("  ✓ Game Event #{$gameEvent->getId()} → User #{$user->getId()} ({$user->getEmail()}) +{$xp} XP");
                } else {
                    $xp = $this->xpService->retrieveXPForAction('game_event');
                    $io->writeln("  [DRY-RUN] Would award {$xp} XP for game event #{$gameEvent->getId()} to user #{$user->getId()} ({$user->getEmail()})");
                    $totalXpAwarded += $xp;
                    $processed++;
                }
            }
        }

        return [$processed, $totalXpAwarded];
    }

    private function processCalendarEvents(SymfonyStyle $io, bool $dryRun, ?string $userId): array
    {
        $io->section('Processing Calendar Event Participations');
        $io->note('Calendar event participation tracking needs to be implemented based on your data model');
        return [0, 0];
    }

    private function processProfiles(SymfonyStyle $io, bool $dryRun, ?string $userId): array
    {
        $io->section('Processing Profile Completeness (one-time award for current state)');
        
        $qb = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.isEnabled = true');

        if ($userId) {
            $qb->andWhere('u.id = :userId')
               ->setParameter('userId', $userId);
        }

        $users = $qb->getQuery()->getResult();
        $processed = 0;
        $totalXpAwarded = 0;

        foreach ($users as $user) {
            // Sicherstellen, dass UserLevel existiert
            $userLevel = $user->getUserLevel();
            if ($userLevel === null) {
                $userLevel = new UserLevel();
                $userLevel->setUser($user);
                $userLevel->setXpTotal(0);
                $userLevel->setLevel(1);
                $userLevel->setUpdatedAt(new DateTimeImmutable());
                $user->setUserLevel($userLevel);
                $this->entityManager->persist($userLevel);
            }
            $completeness = $this->calculateProfileCompleteness($user);
            // Award XP for milestones reached
            $milestones = [25, 50, 75, 100];
            foreach ($milestones as $milestone) {
                if ($completeness >= $milestone) {
                    $actionType = 'profile_completion_' . $milestone;
                    if ($this->hasXpEventForAction($user, $actionType, $user->getId())) {
                        continue;
                    }
                    if (!$dryRun) {
                        $xp = $this->xpService->retrieveXPForAction($actionType);
                        $this->xpService->addXPToUser($user, $xp);
                        $this->createXpEventRecord($user, $actionType, $user->getId(), $xp);
                        $totalXpAwarded += $xp;
                        $processed++;
                        $io->writeln("  ✓ Profile {$milestone}% → User #{$user->getId()} ({$user->getEmail()}) +{$xp} XP");
                    } else {
                        $xp = $this->xpService->retrieveXPForAction($actionType);
                        $io->writeln("  [DRY-RUN] Would award {$xp} XP for profile {$milestone}% to user #{$user->getId()} ({$user->getEmail()})");
                        $totalXpAwarded += $xp;
                        $processed++;
                    }
                }
            }
        }

        return [$processed, $totalXpAwarded];
    }

    private function hasXpEventForAction(User $user, string $actionType, int $actionId): bool
    {
        $existingEvent = $this->entityManager->getRepository(UserXpEvent::class)
            ->findOneBy([
                'user' => $user,
                'actionType' => $actionType,
                'actionId' => $actionId,
            ]);

        return $existingEvent !== null;
    }

    private function createXpEventRecord(User $user, string $actionType, int $actionId, int $xpValue): void
    {
        $xpEvent = new UserXpEvent();
        $xpEvent->setUser($user);
        $xpEvent->setActionType($actionType);
        $xpEvent->setActionId($actionId);
        $xpEvent->setXpValue($xpValue);
        $xpEvent->setIsProcessed(true); // Already processed, just for record keeping
        $xpEvent->setCreatedAt(new DateTimeImmutable());
        $this->entityManager->persist($xpEvent);
        $this->entityManager->flush();
    }

    private function calculateProfileCompleteness(User $user): int
    {
        $fields = [
            'firstName' => $user->getFirstName() !== null && $user->getFirstName() !== '',
            'lastName' => $user->getLastName() !== null && $user->getLastName() !== '',
            'email' => $user->getEmail() !== null && $user->getEmail() !== '',
            'avatar' => $user->getAvatarFilename() !== null,
            'height' => $user->getHeight() !== null,
            'weight' => $user->getWeight() !== null,
            'shoeSize' => $user->getShoeSize() !== null,
            'shirtSize' => $user->getShirtSize() !== null,
            'pantsSize' => $user->getPantsSize() !== null,
            'hasUserRelations' => $user->getUserRelations()->count() > 0,
        ];

        $completedFields = array_filter($fields);
        $totalFields = count($fields);
        
        return (int) round((count($completedFields) / $totalFields) * 100);
    }

    /**
     * @return User[]
     */
    private function getUsersForPlayer($player): array
    {
        if (!$player) {
            return [];
        }

        $users = [];
        foreach ($player->getUserRelations() as $userRelation) {
            // Nur self_player-Relationen berücksichtigen
            if (method_exists($userRelation, 'getRelationType') && $userRelation->getRelationType()->getIdentifier() !== 'self_player') {
                continue;
            }
            $user = $userRelation->getUser();
            if ($user) {
                $users[] = $user;
            }
        }

        return $users;
    }
}
