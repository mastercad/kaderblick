<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\PushSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:push:health-check',
    description: 'Check push notification health for all users and report problems'
)]
class PushHealthCheckCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private PushSubscriptionRepository $subscriptionRepo,
        private NotificationRepository $notificationRepo,
        private ParameterBagInterface $params,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('days', 'd', InputOption::VALUE_OPTIONAL, 'Number of days to check for delivery stats', 7)
            ->addOption('fail-threshold', null, InputOption::VALUE_OPTIONAL, 'Failure rate % to flag as problematic', 50)
            ->addOption('cleanup-stale', null, InputOption::VALUE_NONE, 'Deactivate push subscriptions older than 90 days with no successful delivery');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = (int) $input->getOption('days');
        $failThreshold = (float) $input->getOption('fail-threshold');
        $cleanupStale = $input->getOption('cleanup-stale');

        $io->title('Push Notification Health Check');

        // 1. Check VAPID configuration
        $vapidPublicKey = $this->params->get('vapid_public_key');
        $vapidPrivateKey = $this->params->get('vapid_private_key');

        if (!$vapidPublicKey || !$vapidPrivateKey) {
            $io->error('VAPID keys are NOT configured! Push notifications cannot work.');
            $this->logger->critical('Push health check: VAPID keys not configured');

            return Command::FAILURE;
        }
        $io->success('VAPID keys configured');

        // 2. Global subscription statistics
        $allActiveSubscriptions = $this->subscriptionRepo->findAllActive();
        $totalSubscriptions = count($allActiveSubscriptions);
        $io->info(sprintf('Total active push subscriptions: %d', $totalSubscriptions));

        if ($totalSubscriptions === 0) {
            $io->warning('No active push subscriptions found! No user can receive push notifications.');
            $this->logger->warning('Push health check: No active subscriptions');

            return Command::SUCCESS;
        }

        // 3. Per-user analysis
        $userRepo = $this->em->getRepository(User::class);
        $enabledUsers = $userRepo->findBy(['isEnabled' => true]);

        $usersWithSubscriptions = 0;
        $usersWithoutSubscriptions = 0;
        $usersWithHighFailRate = 0;
        $usersWithStaleSubscriptions = 0;
        $problemUsers = [];

        foreach ($enabledUsers as $user) {
            $subs = $this->subscriptionRepo->findBy(['user' => $user, 'isActive' => true]);

            if (count($subs) === 0) {
                $usersWithoutSubscriptions++;
                continue;
            }

            $usersWithSubscriptions++;
            $stats = $this->notificationRepo->getPushDeliveryStats($user, $days);

            if ($stats['total'] > 0 && $stats['failRate'] > $failThreshold) {
                $usersWithHighFailRate++;
                $problemUsers[] = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                    'subscriptions' => count($subs),
                    'total' => $stats['total'],
                    'sent' => $stats['sent'],
                    'failRate' => $stats['failRate'] . '%',
                ];
            }

            // Check for stale subscriptions (> 90 days old)
            foreach ($subs as $sub) {
                $ageInDays = $sub->getCreatedAt()
                    ? (new \DateTimeImmutable())->diff($sub->getCreatedAt())->days
                    : 0;

                if ($ageInDays > 90) {
                    $usersWithStaleSubscriptions++;

                    if ($cleanupStale) {
                        $sub->setIsActive(false);
                        $this->logger->info('Deactivated stale push subscription', [
                            'subscription_id' => $sub->getId(),
                            'user_id' => $user->getId(),
                            'age_days' => $ageInDays,
                        ]);
                    }
                    break; // Count user only once
                }
            }
        }

        if ($cleanupStale) {
            $this->em->flush();
        }

        // 4. Summary report
        $io->section('Summary');

        $io->table(
            ['Metric', 'Value'],
            [
                ['Enabled users', (string) count($enabledUsers)],
                ['Users WITH push subscriptions', (string) $usersWithSubscriptions],
                ['Users WITHOUT push subscriptions', (string) $usersWithoutSubscriptions],
                ['Users with high failure rate (>' . $failThreshold . '%)', (string) $usersWithHighFailRate],
                ['Users with stale subscriptions (>90d)', (string) $usersWithStaleSubscriptions],
                ['Total active subscriptions', (string) $totalSubscriptions],
            ]
        );

        if (!empty($problemUsers)) {
            $io->section('Problem Users (High Failure Rate)');
            $io->table(
                ['ID', 'Name', 'Email', 'Subscriptions', 'Total', 'Sent', 'Fail Rate'],
                array_map(fn ($u) => [
                    $u['id'], $u['name'], $u['email'],
                    $u['subscriptions'], $u['total'], $u['sent'], $u['failRate'],
                ], $problemUsers)
            );
        }

        // Log summary for monitoring systems
        $this->logger->info('Push health check completed', [
            'total_users' => count($enabledUsers),
            'users_with_subscriptions' => $usersWithSubscriptions,
            'users_without_subscriptions' => $usersWithoutSubscriptions,
            'users_with_high_fail_rate' => $usersWithHighFailRate,
            'users_with_stale_subscriptions' => $usersWithStaleSubscriptions,
            'total_subscriptions' => $totalSubscriptions,
        ]);

        if ($usersWithHighFailRate > 0) {
            $io->warning(sprintf(
                '%d user(s) have high push failure rates. Check their subscriptions.',
                $usersWithHighFailRate
            ));
        }

        $coveragePercent = count($enabledUsers) > 0
            ? round(($usersWithSubscriptions / count($enabledUsers)) * 100, 1)
            : 0;

        $io->info(sprintf(
            'Push coverage: %s%% of enabled users have active subscriptions',
            $coveragePercent
        ));

        if ($coveragePercent < 50) {
            $io->warning('Less than 50% of users have push subscriptions. Consider prompting users to enable push notifications.');
        }

        $io->success('Health check completed.');

        return Command::SUCCESS;
    }
}
