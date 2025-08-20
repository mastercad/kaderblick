<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/games', name: 'games_')]
class GamesController extends AbstractController
{
    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->createQueryBuilder('g')
            ->leftJoin('g.calendarEvent', 'ce')
            ->leftJoin('ce.calendarEventType', 'cet')
            ->addSelect('ce', 'cet')
            ->where('cet.name = :spiel')
            ->setParameter('spiel', 'Spiel')
            ->orderBy('ce.startDate', 'DESC')
            ->getQuery()
            ->getResult();

        $now = new DateTimeImmutable();
        $running = [];
        $upcoming = [];
        $finished = [];

        foreach ($games as $game) {
            $ce = $game->getCalendarEvent();
            if (!$ce) {
                continue;
            }
            $start = $ce->getStartDate();
            $end = $ce->getEndDate();
            if ($start && $end && $now >= $start && $now <= $end) {
                $running[] = $game;
            } elseif ($start && $now < $start) {
                $upcoming[] = $game;
            } else {
                $finished[] = $game;
            }
        }

        return $this->render('games/index.html.twig', [
            'running_games' => $running,
            'upcoming_games' => $upcoming,
            'finished_games' => $finished,
        ]);
    }

    #[Route(path: '/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Game $game): Response
    {
        $calendarEvent = $game->getCalendarEvent();
        $gameEvents = $game->getGameEvents();

        return $this->render('games/show.html.twig', [
            'game' => $game,
            'calendarEvent' => $calendarEvent,
            'gameEvents' => $gameEvents,
        ]);
    }
}
