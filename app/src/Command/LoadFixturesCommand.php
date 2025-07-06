<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fixtures:load',
    description: 'Load Fixtures based on data type',
)]
class LoadFixturesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Lädt Fixtures basierend auf dem Datentyp')
            ->addOption('master', 'm', InputOption::VALUE_NONE, 'Stammdaten laden')
            ->addOption('test', 't', InputOption::VALUE_NONE, 'Testdaten laden')
            ->addOption('fake', 'f', InputOption::VALUE_NONE, 'Fake-Daten laden')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Alle Daten laden');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $groups = [];

        if ($input->getOption('master') || $input->getOption('all')) {
            $groups[] = 'master';
        }

        if ($input->getOption('test') || $input->getOption('all')) {
            $groups[] = 'test';
        }

        if ($input->getOption('fake') || $input->getOption('all')) {
            $groups[] = 'fake';
        }

        if (empty($groups)) {
            $io->error('Bitte wähle mindestens einen Datentyp (--master, --test, --fake, --all)');

            return Command::FAILURE;
        }

        $command = $this->getApplication()->find('doctrine:fixtures:load');

        $arguments = [
            '--append' => true,
        ];

        foreach ($groups as $group) {
            $arguments['--group'][] = $group;
        }

        $fixturesInput = new ArrayInput($arguments);
        $returnCode = $command->run($fixturesInput, $output);

        if (0 === $returnCode) {
            $io->success('Fixtures wurden erfolgreich geladen: ' . implode(', ', $groups));
        }

        return $returnCode;
    }
}
