<?php

namespace App\Command;

use Minishlink\WebPush\VAPID;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-vapid-keys',
    description: 'Generiert VAPID Keys für Web Push Notifications',
)]
class GenerateVapidKeysCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $vapid = VAPID::createVapidKeys();

        $io->success('VAPID Keys wurden generiert. Füge diese zu deiner .env Datei hinzu:');
        $io->text('VAPID_PUBLIC_KEY=' . $vapid['publicKey']);
        $io->text('VAPID_PRIVATE_KEY=' . $vapid['privateKey']);

        return Command::SUCCESS;
    }
}
