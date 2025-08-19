<?php

namespace App\Service;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;

class ReportVirtualFieldService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Calculate total minutes played for a player in all games.
     */
    public function einsatzminuten(Player $player): int
    {
        // Alle Spiele, mit denen der Spieler in Verbindung steht
        $games = $this->em->getRepository(Game::class)->createQueryBuilder('g')
            ->join('g.gameEvents', 'e')
            ->where('e.player = :player')
            ->setParameter('player', $player)
            ->getQuery()->getResult();

        $totalMinutes = 0;
        foreach ($games as $game) {
            // Alle Events für diesen Spieler in diesem Spiel
            $events = $this->em->getRepository(GameEvent::class)->findBy([
                'game' => $game,
                'player' => $player
            ]);
            $in = null;
            $out = null;
            foreach ($events as $event) {
                if ($event->isSubstitutionIn()) {
                    $in = $event->getTimestamp();
                }
                if ($event->isSubstitutionOut()) {
                    $out = $event->getTimestamp();
                }
            }
            // Annahme: Spiel dauert 90 Minuten, Startelf = kein sub_in
            $start = $game->getStartTime() ?? null;
            $end = $game->getEndTime() ?? null;
            if (!$start) {
                continue;
            }   
            if (!$in) {
                $in = $start;
            }
            if (!$out) {
                $out = $end ?? (clone $start)->modify('+90 minutes');
            }
            $minutes = ($out->getTimestamp() - $in->getTimestamp()) / 60;
            if ($minutes > 0) {
                $totalMinutes += (int) $minutes;
            }
        }

        return $totalMinutes;
    }
}
