<?php

namespace App\Command;

use Google\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'app:google-drive:auth',
    description: 'Generiert ein Refresh Token für den Zugriff auf Google Drive.'
)]
class GoogleDriveAuthCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('COMMAND STARTED');

        $client = new Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $client->setScopes([
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive.metadata.readonly',
        ]);

        $authUrl = $client->createAuthUrl();

        $output->writeln('');
        $output->writeln('<info>1. Bitte öffne folgenden Link in deinem Browser:</info>');
        $output->writeln($authUrl);
        $output->writeln('');
        $output->writeln('<comment>2. Melde dich an und kopiere den Code hierher.</comment>');
        $output->writeln('');

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question('Code: ');
        $authCode = $helper->ask($input, $output, $question);

        if (!$authCode) {
            $output->writeln('<error>Kein Code eingegeben.</error>');
            return Command::FAILURE;
        }

        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        if (isset($accessToken['error'])) {
            $output->writeln('<error>Fehler beim Abrufen des Tokens: ' . $accessToken['error_description'] . '</error>');
            return Command::FAILURE;
        }

        $refreshToken = $accessToken['refresh_token'] ?? null;

        if (!$refreshToken) {
            $output->writeln('<error>Kein Refresh Token erhalten. Möglicherweise hast du schon mal authorisiert und musst die Zustimmung erzwingen (prompt=consent).</error>');
            return Command::FAILURE;
        }

        $output->writeln('');
        $output->writeln('<info>Refresh Token:</info>');
        $output->writeln($refreshToken);
        $output->writeln('');
        $output->writeln('<comment>Füge diesen Token in deine .env Datei ein als:</comment>');
        $output->writeln('GOOGLE_REFRESH_TOKEN=' . $refreshToken);
        $output->writeln('');

        return Command::SUCCESS;
    }
}
