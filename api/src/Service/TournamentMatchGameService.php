<?php

namespace App\Service;

use App\Entity\CalendarEvent;
use App\Entity\Game;
use App\Entity\TournamentMatch;
use App\Repository\GameTypeRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class TournamentMatchGameService
{
    private EntityManagerInterface $em;
    private GameTypeRepository $gameTypeRepository;

    public function __construct(EntityManagerInterface $em, GameTypeRepository $gameTypeRepository)
    {
        $this->em = $em;
        $this->gameTypeRepository = $gameTypeRepository;
    }

    /**
     * Create a `Game` + `CalendarEvent` for the given TournamentMatch and link them.
     */
    public function createGameForMatch(TournamentMatch $match, ?DateTimeInterface $scheduledAt = null): Game
    {
        $game = new Game();

        $homeTeam = $match->getHomeTeam()?->getTeam();
        $awayTeam = $match->getAwayTeam()?->getTeam();
        if ($homeTeam) {
            $game->setHomeTeam($homeTeam);
        }
        if ($awayTeam) {
            $game->setAwayTeam($awayTeam);
        }

        // Suche GameType: erst 'Turnier-Match', dann 'Turnierspiel'
        $gameType = $this->gameTypeRepository->findOneBy(['name' => 'Turnier-Match']);
        if (!$gameType) {
            $gameType = $this->gameTypeRepository->findOneBy(['name' => 'Turnierspiel']);
        }
        if (!$gameType) {
            throw new RuntimeException('GameType "Turnier-Match" oder "Turnierspiel" nicht gefunden, kann kein Turnier-Match-Game anlegen.');
        }
        $game->setGameType($gameType);
        $game->setIsFinished(false);

        // CalendarEventType suchen
        $eventType = $this->em->getRepository(\App\Entity\CalendarEventType::class)->findOneBy(['name' => 'Turnier-Match']);
        if (!$eventType) {
            $eventType = $this->em->getRepository(\App\Entity\CalendarEventType::class)->findOneBy(['name' => 'Turnierspiel']);
        }

        $calendarEvent = new CalendarEvent();
        $titleParts = [];
        if ($match->getTournament()) {
            $titleParts[] = $match->getTournament()->getName();
        }
        $titleParts[] = 'Turnier-Match';
        $calendarEvent->setTitle(implode(' - ', $titleParts));
        if ($scheduledAt) {
            $calendarEvent->setStartDate($scheduledAt);
        } else {
            $calendarEvent->setStartDate(new \DateTimeImmutable());
        }
        if ($eventType) {
            $calendarEvent->setCalendarEventType($eventType);
        }

        // link both sides
        $game->setCalendarEvent($calendarEvent);
        $calendarEvent->setGame($game);

        $this->em->persist($calendarEvent);
        $this->em->persist($game);

        $match->setGame($game);
        $this->em->persist($match);

        $this->em->flush();

        return $game;
    }
}
