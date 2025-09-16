<?php

namespace App\Command;

use App\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:hash-password',
    description: 'Generates a hashed password for a User entity',
)]
class HashPasswordCommand extends Command
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new Question('Please enter the password to hash: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $plainPassword = $helper->ask($input, $output, $question);

        if (!$plainPassword) {
            $io->error('Password cannot be empty.');

            return Command::FAILURE;
        }

        // Temporäres User-Objekt nur für das Hashing
        $user = new User();

        $hash = $this->passwordHasher->hashPassword($user, $plainPassword);

        $io->success('Hashed password:');
        $io->writeln($hash);

        return Command::SUCCESS;
    }
}
