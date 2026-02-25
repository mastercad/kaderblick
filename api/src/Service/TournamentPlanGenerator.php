<?php

namespace App\Service;

use App\Entity\Tournament;
use App\Entity\TournamentMatch;
use App\Entity\TournamentTeam;
use Doctrine\ORM\EntityManagerInterface;

class TournamentPlanGenerator
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Generate a single-elimination bracket for the given tournament.
     * Returns array of created TournamentMatch entities.
     *
     * @return TournamentMatch[]
     */
    public function generateSingleElimination(Tournament $tournament): array
    {
        $teams = $tournament->getTeams()->toArray();

        // sort by seed if set
        usort($teams, function (TournamentTeam $a, TournamentTeam $b) {
            $seedA = $a->getSeed() ?? PHP_INT_MAX;
            $seedB = $b->getSeed() ?? PHP_INT_MAX;

            return $seedA <=> $seedB;
        });

        $n = count($teams);
        if ($n < 2) {
            return [];
        }

        // next power of two
        $size = 1;
        while ($size < $n) {
            $size <<= 1;
        }

        // pad with nulls (byes)
        for ($i = $n; $i < $size; ++$i) {
            $teams[] = null;
        }

        $created = [];

        // create initial round matches
        $round = 1;
        $matchesByRound = [];
        $pairCount = $size / 2;
        for ($i = 0; $i < $pairCount; ++$i) {
            $home = $teams[$i * 2];
            $away = $teams[$i * 2 + 1];

            $m = new TournamentMatch();
            $m->setTournament($tournament);
            $m->setRound($round);
            $m->setSlot($i + 1);
            if ($home instanceof TournamentTeam) {
                $m->setHomeTeam($home->getTeam());
            }
            if ($away instanceof TournamentTeam) {
                $m->setAwayTeam($away->getTeam());
            }

            $this->em->persist($m);
            $created[] = $m;
            $matchesByRound[$round][] = $m;
        }

        // create subsequent rounds and link previous matches to next
        $prevRoundMatches = $matchesByRound[$round];
        while (count($prevRoundMatches) > 1) {
            ++$round;
            $matchesByRound[$round] = [];
            $pairCount = intdiv(count($prevRoundMatches), 2);
            for ($i = 0; $i < $pairCount; ++$i) {
                $m = new TournamentMatch();
                $m->setTournament($tournament);
                $m->setRound($round);
                $m->setSlot($i + 1);
                $this->em->persist($m);
                $created[] = $m;
                $matchesByRound[$round][] = $m;

                // link two previous matches to this next match
                $prevA = $prevRoundMatches[$i * 2] ?? null;
                $prevB = $prevRoundMatches[$i * 2 + 1] ?? null;
                if ($prevA) {
                    $prevA->setNextMatch($m);
                    $this->em->persist($prevA);
                }
                if ($prevB) {
                    $prevB->setNextMatch($m);
                    $this->em->persist($prevB);
                }
            }

            $prevRoundMatches = $matchesByRound[$round];
        }

        $this->em->flush();

        return $created;
    }
}
