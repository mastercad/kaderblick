<?php

namespace App\Controller;

use App\Entity\CalendarEvent;
use App\Entity\Participation;
use App\Entity\User;
use App\Repository\ParticipationRepository;
use App\Repository\ParticipationStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/participation', name: 'api_participation_')]
class ParticipationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ParticipationRepository $participationRepository,
        private ParticipationStatusRepository $participationStatusRepository
    ) {
    }

    #[Route('/event/{id}', name: 'status', methods: ['GET'])]
    public function getEventParticipations(CalendarEvent $event): JsonResponse
    {
        $participations = $this->participationRepository->findBy(['event' => $event]);

        $participationData = [];

        // Nur Benutzer mit tatsächlichen Teilnahmen anzeigen
        foreach ($participations as $participation) {
            $user = $participation->getUser();
            $participationData[] = [
                'user_id' => $user->getId(),
                'user_name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'status' => [
                    'id' => $participation->getStatus()->getId(),
                    'name' => $participation->getStatus()->getName(),
                    'code' => $participation->getStatus()->getCode(),
                    'color' => $participation->getStatus()->getColor(),
                    'icon' => $participation->getStatus()->getIcon()
                ],
                'note' => $participation->getNote(),
                'is_team_player' => $this->isTeamPlayer($user, $event)
            ];
        }

        return $this->json([
            'event' => [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'type' => $event->getCalendarEventType()?->getName() ?? 'Unbekannt',
                'is_game' => null !== $event->getGame()
            ],
            'participations' => $participationData,
            'available_statuses' => $this->getAvailableStatuses($event)
        ]);
    }

    #[Route('/event/{id}/respond', name: 'respond', methods: ['POST'])]
    public function respond(Request $request, CalendarEvent $event): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Benutzer nicht authentifiziert'], 401);
        }

        // Prüfen ob der Benutzer berechtigt ist an diesem Event teilzunehmen
        if (!$this->canUserParticipate($user, $event)) {
            return $this->json(['error' => 'Sie sind nicht berechtigt an diesem Event teilzunehmen'], 403);
        }

        $statusId = $request->request->getInt('status_id');
        $note = $request->request->get('note', '');

        $status = $this->participationStatusRepository->find($statusId);
        if (!$status) {
            return $this->json(['error' => 'Ungültiger Status'], 400);
        }

        // Bestehende Teilnahme suchen oder neue erstellen
        $participation = $this->participationRepository->findOneBy([
            'event' => $event,
            'user' => $user
        ]) ?? new Participation();

        $participation->setUser($user)
            ->setEvent($event)
            ->setStatus($status)
            ->setNote($note);

        $this->em->persist($participation);
        $this->em->flush();

        return $this->json([
            'message' => 'Teilnahmestatus erfolgreich aktualisiert',
            'participation' => [
                'status' => [
                    'id' => $status->getId(),
                    'name' => $status->getName(),
                    'code' => $status->getCode(),
                    'color' => $status->getColor(),
                    'icon' => $status->getIcon()
                ],
                'note' => $participation->getNote()
            ]
        ]);
    }

    #[Route('/statuses', name: 'statuses', methods: ['GET'])]
    public function getParticipationStatuses(): JsonResponse
    {
        $statuses = $this->participationStatusRepository->findBy([], ['sortOrder' => 'ASC']);

        return $this->json([
            'statuses' => array_map(fn ($status) => [
                'id' => $status->getId(),
                'name' => $status->getName(),
                'code' => $status->getCode(),
                'color' => $status->getColor(),
                'icon' => $status->getIcon(),
                'sort_order' => $status->getSortOrder()
            ], $statuses)
        ]);
    }

    /**
     * Ermittelt die verfügbaren Status für ein Event.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getAvailableStatuses(CalendarEvent $event): array
    {
        // Alle Status sind erstmal verfügbar - könnte später je nach Event-Typ gefiltert werden
        $statuses = $this->participationStatusRepository->findBy([], ['sortOrder' => 'ASC']);

        return array_map(fn ($status) => [
            'id' => $status->getId(),
            'name' => $status->getName(),
            'code' => $status->getCode(),
            'color' => $status->getColor(),
            'icon' => $status->getIcon(),
            'sort_order' => $status->getSortOrder()
        ], $statuses);
    }

    /**
     * Prüft ob ein Benutzer Spieler in einem der beteiligten Teams ist.
     */
    private function isTeamPlayer(User $user, CalendarEvent $event): bool
    {
        $game = $event->getGame();
        if (!$game) {
            return false;
        }

        // Finde die Player-Relation des Users
        $playerRelation = null;
        foreach ($user->getRelations() as $relation) {
            if (
                $relation->getPlayer()
                && 'player' === $relation->getRelationType()->getCategory()
                && 'self_player' === $relation->getRelationType()->getIdentifier()
            ) {
                $playerRelation = $relation;
                break;
            }
        }

        if (!$playerRelation || !$playerRelation->getPlayer()) {
            return false;
        }

        $player = $playerRelation->getPlayer();

        // Prüfen ob Spieler in einem der Teams spielt
        foreach ($player->getPlayerTeamAssignments() as $assignment) {
            $team = $assignment->getTeam();
            if ($team === $game->getHomeTeam() || $team === $game->getAwayTeam()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prüft ob ein Benutzer an einem Event teilnehmen kann.
     */
    private function canUserParticipate(User $user, CalendarEvent $event): bool
    {
        // Grundsätzlich können alle Benutzer an allen Events teilnehmen
        return true;
    }
}
