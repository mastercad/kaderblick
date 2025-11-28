<?php

namespace App\Command;

use App\Entity\GameEvent;
use App\Service\TitleCalculationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:xp:award-titles',
    description: 'Award top scorer titles (team and platform) retroactively.'
)]
class AwardTitlesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TitleCalculationService $titleCalculationService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('season', null, InputOption::VALUE_OPTIONAL, 'Season string (e.g. 2024/2025). Default: current season')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be awarded, but do not persist changes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $season = $input->getOption('season') ?? $this->titleCalculationService->retrieveCurrentSeason();
        $dryRun = $input->getOption('dry-run');

        $io->title('Awarding Top Scorer Titles');
        $io->text("Season: $season");
        if ($dryRun) {
            $io->warning('DRY-RUN: No changes will be persisted.');
        }

        // Debug: Alle gezählten Tore für die Saison ausgeben
        $io->section('DEBUG: Alle gezählten Tore für diese Saison (inkl. Player-Relationen)');
        $gameEvents = $this->titleCalculationService->debugGoalsForSeason($season);

        $counted = 0;
        /** @var GameEvent $gameEvent */
        foreach ($gameEvents as $gameEvent) {
            $scorer = $gameEvent->getPlayer();
            $relations = [];
            foreach ($scorer->getUserRelations() as $userRelation) {
                $relations[] = $userRelation->getRelationType()->getIdentifier();
            }

            if ('goal' !== $gameEvent->getGameEventType()?->getCode()) {
                continue;
            }

            $game = $gameEvent->getGame();
            $calendarEvent = $game->getCalendarEvent();
            $team = $game->getHomeTeam();
            $io->writeln(sprintf(
                'Tor-ID: %d | Spieler: %s %s | Spiel-ID: %d | Team: %s | Datum: %s | Player-RelationTypes: [%s]',
                $gameEvent->getId(),
                $scorer->getFirstName(),
                $scorer->getLastName(),
                $game->getId(),
                $team?->getName() ?? '-',
                $calendarEvent?->getStartDate()?->format('Y-m-d H:i') ?? '-',
                implode(', ', $relations)
            ));
            ++$counted;
        }
        if (0 === $counted) {
            $io->writeln('Keine Tore gefunden.');
        }

        // Platform-wide
        $platformTitles = $this->titleCalculationService->calculatePlatformTopScorers($season);
        $io->section('Platform-wide Top Scorers');
        foreach ($platformTitles as $title) {
            $io->writeln(sprintf(
                '%s: %s %s (%d goals) [%s]',
                ucfirst($title->getTitleRank()),
                $title->getUser()->getFirstName(),
                $title->getUser()->getLastName(),
                $title->getValue(),
                $title->getUser()->getEmail()
            ));
        }

        // Team-wise
        $teamTitles = $this->titleCalculationService->calculateAllTeamTopScorers($season);
        $io->section('Team Top Scorers');
        foreach ($teamTitles as $title) {
            $io->writeln(sprintf(
                '%s: %s %s (%d goals) [Team: %s] [%s]',
                ucfirst($title->getTitleRank()),
                $title->getUser()->getFirstName(),
                $title->getUser()->getLastName(),
                $title->getValue(),
                $title->getTeam()?->getName() ?? '-',
                $title->getUser()->getEmail()
            ));
        }

        if ($dryRun) {
            $io->success('DRY-RUN complete. No titles were persisted.');
            $this->entityManager->clear();
        } else {
            $io->success('Titles awarded and persisted.');
        }

        return Command::SUCCESS;
    }
}
