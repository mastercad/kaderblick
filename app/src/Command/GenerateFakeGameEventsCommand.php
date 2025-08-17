<?php

namespace App\Command;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\GameEventType;
use App\Entity\Player;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:generate-fake-game-events',
    description: 'Erzeugt realistische, zufällige GameEvents für alle Spiele, Teams und Spieler.'
)]
class GenerateFakeGameEventsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('events-per-game', InputArgument::OPTIONAL, 'Wie viele Events pro Spiel generieren?', 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $faker = Factory::create('de_DE');
        $eventsPerGame = (int) $input->getArgument('events-per-game');

        $games = $this->em->getRepository(Game::class)->findAll();
        $eventTypes = $this->em->getRepository(GameEventType::class)->findAll();
        $players = $this->em->getRepository(Player::class)->findAll();
        $teams = $this->em->getRepository(Team::class)->findAll();

        if (empty($games) || empty($eventTypes) || empty($players) || empty($teams)) {
            $output->writeln('<error>Es fehlen Spiele, Eventtypen, Spieler oder Teams!</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Generiere Fake-Events für ' . count($games) . ' Spiele...</info>');
        $count = 0;
        foreach ($games as $game) {
            $gameTeams = [$game->getHomeTeam(), $game->getAwayTeam()];
            $gamePlayers = array_filter($players, fn($p) => in_array($p->getTeams()[0] ?? null, $gameTeams, true));
            if (empty($gamePlayers)) {
                continue;
            }

            for ($i = 0; $i < $eventsPerGame; $i++) {
                $eventType = $faker->randomElement($eventTypes);
                $team = $faker->randomElement($gameTeams);
                $player = $faker->randomElement($gamePlayers);
                $event = new GameEvent();
                $event->setGame($game);
                $event->setGameEventType($eventType);
                $event->setTeam($team);
                $event->setPlayer($player);
                $startDate = $game->getCalendarEvent()?->getStartDate() ?? new \DateTime();
                $eventMinute = $faker->numberBetween(1, 90);
                $event->setTimestamp((clone $startDate)->modify('+' . $eventMinute . ' minutes'));
                $event->setDescription($faker->optional(0.5)->sentence(6));
                // Bei Wechseln: relatedPlayer setzen
                if (in_array($eventType->getCode(), ['substitution_in', 'substitution_out'], true)) {
                    $otherPlayers = array_filter($gamePlayers, fn($p) => $p !== $player);
                    $related = $faker->randomElement($otherPlayers ?: $gamePlayers);
                    $event->setRelatedPlayer($related);
                }
                $this->em->persist($event);
                $count++;
            }
        }
        $this->em->flush();

        $output->writeln('<info>Fertig! Es wurden ' . $count . ' GameEvents generiert.</info>');
        return Command::SUCCESS;
    }
}
