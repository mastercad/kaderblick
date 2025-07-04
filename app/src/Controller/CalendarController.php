<?php

namespace App\Controller;

use App\Entity\CalendarEvent;
use App\Entity\CalendarEventType;
use App\Entity\Game;
use App\Entity\GameType;
use App\Entity\Location;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\CalendarEventRepository;
use App\Service\EmailNotificationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/calendar', name: 'calendar_')]
class CalendarController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EmailNotificationService $emailService,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(CalendarEventRepository $calendarEventRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $calendarGameEventType = $this->entityManager->getRepository(CalendarEventType::class)->findOneBy(['name' => 'Spiel']);

        return $this->render('calendar/index.html.twig', [
            'calendarGameEventTypeId' => $calendarGameEventType?->getId(),
            'upcomingEvents' => $calendarEventRepository->findUpcoming(),
            'eventTypes' => $this->entityManager->getRepository(CalendarEventType::class)->findAll(),
            'locations' => $this->entityManager->getRepository(Location::class)->findAll(),
            'teams' => $this->entityManager->getRepository(Team::class)->findAll(),
            'gameTypes' => $this->entityManager->getRepository(GameType::class)->findAll(),
        ]);
    }

    #[Route('/events', name: 'events', methods: ['GET'])]
    public function retrieveEvents(Request $request, CalendarEventRepository $calendarEventRepository): JsonResponse
    {
        $start = new DateTime($request->query->get('start'));
        $end = new DateTime($request->query->get('end'));

        $calendarEvents = $calendarEventRepository->findBetweenDates($start, $end);

        $formattedEvents = array_map(function ($calendarEvent) {
            $endDate = $calendarEvent->getEndDate() ?: (clone $calendarEvent->getStartDate())->setTime(23, 59, 59);

            return [
                'id' => $calendarEvent->getId(),
                'title' => $calendarEvent->getTitle(),
                'start' => $calendarEvent->getStartDate()->format('Y-m-d\TH:i:s'),
                'end' => $endDate->format('Y-m-d\TH:i:s'),
                'description' => $calendarEvent->getDescription(),
                'game' => $calendarEvent->getGame() ? [
                    'homeTeam' => [
                        'id' => $calendarEvent->getGame()->getHomeTeam()->getId(),
                        'name' => $calendarEvent->getGame()->getHomeTeam()->getName()
                    ],
                    'awayTeam' => [
                        'id' => $calendarEvent->getGame()->getAwayTeam()->getId(),
                        'name' => $calendarEvent->getGame()->getAwayTeam()->getName()
                    ],
                    'gameType' => [
                        'id' => $calendarEvent->getGame()->getGameType()->getId(),
                        'name' => $calendarEvent->getGame()->getGameType()->getName()
                    ]
                ] : null,
                'type' => $calendarEvent->getEventType() ? [
                    'id' => $calendarEvent->getEventType()->getId(),
                    'name' => $calendarEvent->getEventType()->getName(),
                    'color' => $calendarEvent->getEventType()->getColor()
                ] : null,
                'location' => $calendarEvent->getLocation() ? [
                    'id' => $calendarEvent->getLocation()->getId(),
                    'name' => $calendarEvent->getLocation()->getName()
                ] : null
            ];
        }, $calendarEvents);

        return $this->json($formattedEvents);

        return $this->json($calendarEvents, 200, [], [
            'groups' => ['calendar_event:read'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            },
        ]);
    }

    #[Route('/upcoming', name: 'upcoming', methods: ['GET'])]
    public function retrieveUpcomingEvents(CalendarEventRepository $calendarEventRepository): JsonResponse
    {
        $calendarEvents = $calendarEventRepository->findUpcoming();

        return $this->json($calendarEvents, 200, [], ['groups' => ['event:read']]);
    }

    #[Route('/event', name: 'event_create', methods: ['POST'])]
    public function createEvent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $calendarEvent = new CalendarEvent();
        $this->updateEventFromData($calendarEvent, $data);

        return $this->json(['success' => true]);
    }

    /**
     * Drag & Drop endpunkt für die Aktualisierung von Kalendereinträgen.
     */
    #[Route('/event/{id}', name: 'event_update', methods: ['PUT'])]
    public function updateCalendarEvent(CalendarEvent $calendarEvent, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->updateEventFromData($calendarEvent, $data);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/event/{id}', name: 'event_delete', methods: ['DELETE'])]
    public function deleteEvent(CalendarEvent $calendarEvent, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($calendarEvent);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/event/notify', name: 'event_notify', methods: ['POST'])]
    public function notifyAboutEvent(CalendarEvent $calendarEvent): JsonResponse
    {
        $recipients = $this->loadEventRecipients($calendarEvent);
        $this->emailService->sendEventNotification($recipients, $calendarEvent);

        $calendarEvent->setNotificationSent(true);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    private function updateEventFromData(CalendarEvent $calendarEvent, array $data): void
    {
        $calendarEvent->setTitle($data['title'] ?? $calendarEvent->getTitle());
        $calendarEvent->setDescription($data['description'] ?? null);
        $calendarEvent->setStartDate(new DateTime($data['startDate']));

        if (isset($data['endDate'])) {
            $calendarEvent->setEndDate(new DateTime($data['endDate']));
        }

        if (isset($data['typeId'])) {
            $type = $this->entityManager->getReference(CalendarEventType::class, $data['typeId']);
            $calendarEvent->setEventType($type);
        }

        if (isset($data['locationId'])) {
            $location = $this->entityManager->getReference(Location::class, (int) $data['locationId']);
            $calendarEvent->setLocation($location);
        }

        if (isset($data['homeTeamId'])) {
            $homeTeam = $this->entityManager->getReference(Team::class, (int) $data['homeTeamId']);
            if (null === $calendarEvent->getGame()) {
                $game = new Game();
                $calendarEvent->setGame($game);
                $game->setCalendarEvent($calendarEvent);
                $this->entityManager->persist($game);
            }
            $calendarEvent->getGame()->setHomeTeam($homeTeam);
        }

        if (isset($data['awayTeamId'])) {
            $awayTeam = $this->entityManager->getReference(Team::class, (int) $data['awayTeamId']);
            if (null === $calendarEvent->getGame()) {
                $game = new Game();
                $calendarEvent->setGame($game);
                $game->setCalendarEvent($calendarEvent);
                $this->entityManager->persist($game);
            }
            $calendarEvent->getGame()->setAwayTeam($awayTeam);
        }

        if (isset($data['gameTypeId'])) {
            $gameType = $this->entityManager->getReference(GameType::class, (int) $data['gameTypeId']);
            if (null === $calendarEvent->getGame()) {
                $game = new Game();
                $calendarEvent->setGame($game);
                $game->setCalendarEvent($calendarEvent);
                $this->entityManager->persist($game);
            }
            $calendarEvent->getGame()->setGameType($gameType);
        }

        $this->entityManager->persist($calendarEvent);
        $this->entityManager->flush();
    }

    private function loadEventRecipients(CalendarEvent $calendarEvent): array
    {
        return $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('u.email')
            ->where('u.isVerified = true')
            ->getQuery()
            ->getSingleColumnResult();
    }

    #[Route('/locations/search', name: 'search_locations', methods: ['GET'])]
    public function searchLocations(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $term = $request->query->get('term', '');

        $qb = $entityManager->createQueryBuilder();
        $qb->select('l')
           ->from(Location::class, 'l')
           ->orWhere('l.name LIKE :term')
           ->orWhere('l.city LIKE :term')
           ->orWhere('l.address LIKE :term')
           ->setParameter('term', '%' . $term . '%')
           ->setMaxResults(10);

        $locations = $qb->getQuery()->getArrayResult();

        return new JsonResponse($locations);
    }
}
