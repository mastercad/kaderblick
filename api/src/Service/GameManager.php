<?php

namespace App\Service;

use App\Entity\CalendarEvent;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;

class GameManager
{
    /**
     * Synchronisiert alle anstehenden Spiele und legt neue CalendarGameEvents an.
     *
     * @param array<int, array<string, mixed>> $spieleData Array mit Spieldaten (z.B. von FussballDeCrawlerService)
     */
    public function syncUpcomingGames(array $spieleData, EntityManagerInterface $em): void
    {
        foreach ($spieleData as $spiel) {
            // Annahme: $spiel['fussballDeId'], $spiel['fussballDeUrl'], $spiel['homeTeam'], $spiel['awayTeam'], $spiel['startDate'], ...
            $game = $em->getRepository(Game::class)->findOneBy([
                'fussballDeId' => $spiel['fussballDeId']
            ]);
            if (!$game) {
                $game = new Game();
                $game->setHomeTeam($spiel['homeTeam']);
                $game->setAwayTeam($spiel['awayTeam']);
                $game->setGameType($spiel['gameType'] ?? null);
                $game->setLocation($spiel['location'] ?? null);
            }
            $game->setFussballDeId($spiel['fussballDeId']);
            $game->setFussballDeUrl($spiel['fussballDeUrl']);

            // CalendarEvent prüfen/erstellen
            if (!$game->getCalendarEvent()) {
                $calendarEvent = new CalendarEvent();
                $calendarEvent->setTitle($spiel['title'] ?? 'Spiel');
                $calendarEvent->setStartDate($spiel['startDate']);
                $calendarEvent->setEndDate($spiel['endDate'] ?? null);
                $calendarEvent->setLocation($spiel['location'] ?? null);
                $calendarEvent->setDescription($spiel['description'] ?? null);
                $game->setCalendarEvent($calendarEvent);
                $em->persist($calendarEvent);
            }

            $em->persist($game);
        }
        $em->flush();
    }
}
