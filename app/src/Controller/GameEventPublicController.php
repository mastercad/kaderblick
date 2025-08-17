<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Entity\GameEventType;
use App\Entity\Player;
use App\Repository\GameEventRepository;
use App\Repository\GameRepository;
use App\Repository\GameEventTypeRepository;
use App\Repository\PlayerRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameEventPublicController extends AbstractController
{
    #[Route('/game/{id}/events', name: 'game_event_public', methods: ['GET'])]
    public function form(
        Game $game,
        GameEventTypeRepository $eventTypeRepo,
        PlayerRepository $playerRepo,
        GameEventRepository $eventRepo,
        \App\Repository\SubstitutionReasonRepository $substitutionReasonRepo
    ): Response {
        $eventTypes = $eventTypeRepo->findAll();
        // Spieler beider Teams (Heim & Auswärts) über PlayerTeamAssignment (nur aktive Zuordnungen)
        $teams = array_filter([$game->getHomeTeam(), $game->getAwayTeam()]);
        $players = method_exists($playerRepo, 'findActiveByTeams')
            ? $playerRepo->findActiveByTeams($teams)
            : [];
        $events = $eventRepo->findBy(['game' => $game], ['timestamp' => 'ASC']);
        $substitutionReasons = $substitutionReasonRepo->fetchFullList();
        return $this->render('game_event/public_form.html.twig', [
            'game' => $game,
            'eventTypes' => $eventTypes,
            'players' => $players,
            'events' => $events,
            'teams' => $teams,
            'substitutionReasons' => $substitutionReasons
        ]);
    }

    #[Route('/api/game/{id}/event', name: 'api_game_event_add', methods: ['POST'])]
    public function addEvent(
        Game $game,
        Request $request,
        EntityManagerInterface $em,
        PlayerRepository $playerRepo,
        GameEventTypeRepository $eventTypeRepo,
        \App\Repository\SubstitutionReasonRepository $substitutionReasonRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid data'], 400);
        }
        $event = new GameEvent();
        $event->setGame($game);
        $eventType = $eventTypeRepo->find($data['eventType'] ?? null);
        $event->setGameEventType($eventType);
        $event->setPlayer($playerRepo->find($data['player'] ?? null));
        // Nur bei Wechsel-Ereignissen (Code oder Name enthält "wechsel") den zweiten Spieler setzen
        $isSubstitution = false;
        if ($eventType) {
            $name = mb_strtolower($eventType->getName());
            $code = method_exists($eventType, 'getCode') ? mb_strtolower($eventType->getCode()) : '';
            $isSubstitution = str_contains($name, 'wechsel') || str_contains($code, 'sub');
        }
        if ($isSubstitution && !empty($data['relatedPlayer'])) {
            $event->setRelatedPlayer($playerRepo->find($data['relatedPlayer']));
        } else {
            $event->setRelatedPlayer(null);
        }
        // Team des Spielers für das aktuelle Spiel ermitteln (über PlayerTeamAssignment)
        $player = $event->getPlayer();
        $team = null;
        if ($player) {
            $assignments = $player->getPlayerTeamAssignments();
            foreach ($assignments as $pta) {
                // Nur aktive Zuordnungen (ohne Enddatum oder Enddatum in der Zukunft)
                $ptaTeam = $pta->getTeam();
                $ptaEnd = $pta->getEndDate();
                $isActive = ($ptaEnd === null) || ($ptaEnd >= new \DateTime());
                if ($isActive && ($ptaTeam === $game->getHomeTeam() || $ptaTeam === $game->getAwayTeam())) {
                    $team = $ptaTeam;
                    break;
                }
            }
        }
        $event->setTeam($team);
        $event->setTimestamp(
            (new DateTime())->setTime(0, 0)->modify('+' . ((int)($data['minute'] ?? 1)) . ' minutes')
        );
        // Grund für Wechsel als Beschreibung, falls vorhanden
        if ($isSubstitution && !empty($data['reason'])) {
            $reasonEntity = $substitutionReasonRepo->find($data['reason']);
            $event->setDescription($reasonEntity ? $reasonEntity->getName() : $data['reason']);
        } else {
            $event->setDescription($data['description'] ?? null);
        }
        $em->persist($event);
        $em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/api/game/{id}/events', name: 'api_game_event_list', methods: ['GET'])]
    public function listEvents(Game $game, GameEventRepository $eventRepo): JsonResponse
    {
        $events = $eventRepo->findBy(['game' => $game], ['timestamp' => 'ASC']);
        $result = [];
        foreach ($events as $event) {
            $result[] = [
                'id' => $event->getId(),
                'type' => $event->getGameEventType()?->getName(),
                'typeIcon' => $event->getGameEventType()?->getIcon(),
                'typeColor' => $event->getGameEventType()?->getColor(),
                'minute' => $event->getTimestamp()?->format('i'),
                'player' => $event->getPlayer()?->getFullName(),
                'relatedPlayer' => $event->getRelatedPlayer()?->getFullName(),
                'description' => $event->getDescription(),
            ];
        }
        return $this->json($result);
    }
}
