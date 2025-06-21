<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:send-test-email',
    description: 'Send Test-Email to andreas.kempe@byte-artist.de',
)]
class SendTestEmailCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Send a test email.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from('no-reply@byte-artist.de')
            ->to('andreas.kempe@byte-artist.de')
            ->subject('Test E-Mail von Symfony')
            ->text('Hallo! Das ist eine Test-Mail, um den Mailversand zu prüfen.');

        try {
            $this->mailer->send($email);
            $output->writeln('Test-Mail wurde erfolgreich versendet!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Fehler beim Senden der Test-Mail: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
