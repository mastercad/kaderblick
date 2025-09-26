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
use App\Repository\ParticipationRepository;
use App\Security\Voter\CalendarEventVoter;
use App\Service\EmailNotificationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/calendar', name: 'api_calendar_')]
class CalendarController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EmailNotificationService $emailService,
        private readonly ValidatorInterface $validator,
        private readonly ParticipationRepository $participationRepository
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(CalendarEventRepository $calendarEventRepository): Response
    {
        $calendarGameEventTypeSpiel = $this->entityManager->getRepository(CalendarEventType::class)->findOneBy(['name' => 'Spiel']);

        return $this->render('calendar/index.html.twig', [
            'calendarGameEventTypeId' => $calendarGameEventTypeSpiel?->getId(),
            'upcomingEvents' => $calendarEventRepository->findUpcoming(),
            'eventTypes' => $this->entityManager->getRepository(CalendarEventType::class)->findAll(),
            'locations' => $this->entityManager->getRepository(Location::class)->findAll(),
            'teams' => $this->entityManager->getRepository(Team::class)->findAll(),
            'gameTypes' => $this->entityManager->getRepository(GameType::class)->findAll(),
            'permissions' => [
                'CREATE' => CalendarEventVoter::CREATE,
                'EDIT' => CalendarEventVoter::EDIT,
                'VIEW' => CalendarEventVoter::VIEW,
                'DELETE' => CalendarEventVoter::DELETE,
            ]
        ]);
    }

    #[Route('/events', name: 'events', methods: ['GET'])]
    public function retrieveEvents(Request $request, CalendarEventRepository $calendarEventRepository): JsonResponse
    {
        $start = new DateTime($request->query->get('start'));
        $end = new DateTime($request->query->get('end'));

        $calendarEvents = $calendarEventRepository->findBetweenDates($start, $end);
        /** @var ?User $user */
        $user = $this->getUser();

        $formattedEvents = array_map(function ($calendarEvent) use ($user) {
            $endDate = $calendarEvent->getEndDate();
            if (!$endDate) {
                $endDate = new DateTime();
                $endDate->setTimestamp($calendarEvent->getStartDate()->getTimestamp());
                $endDate->modify('23:59:59');
            }

            // Participation-Status für eingeloggten User holen
            $participationStatus = null;
            $participation = $user instanceof User ? $this->participationRepository->findByUserAndEvent($user, $calendarEvent) : [];
            if ($participation && $participation->getStatus()) {
                $participationStatus = [
                    'id' => $participation->getStatus()->getId(),
                    'name' => $participation->getStatus()->getName(),
                    'code' => $participation->getStatus()->getCode(),
                    'icon' => $participation->getStatus()->getIcon(),
                    'color' => $participation->getStatus()->getColor(),
                ];
            } else {
                $participationStatus = null;
            }

            return [
                'id' => $calendarEvent->getId(),
                'title' => $calendarEvent->getTitle(),
                'start' => $calendarEvent->getStartDate()->format('Y-m-d\TH:i:s'),
                'end' => $endDate->format('Y-m-d\TH:i:s'),
                'description' => $calendarEvent->getDescription(),
                'game' => $calendarEvent->getGame() ? [
                    'id' => $calendarEvent->getGame()->getId(),
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
                'type' => $calendarEvent->getCalendarEventType() ? [
                    'id' => $calendarEvent->getCalendarEventType()->getId(),
                    'name' => $calendarEvent->getCalendarEventType()->getName(),
                    'color' => $calendarEvent->getCalendarEventType()->getColor()
                ] : null,
                'location' => $calendarEvent->getLocation() ? [
                    'id' => $calendarEvent->getLocation()->getId(),
                    'name' => $calendarEvent->getLocation()->getName()
                ] : null,
                'permissions' => [
                    'canCreate' => $this->isGranted(CalendarEventVoter::CREATE, $calendarEvent->getGame() ?? null),
                    'canEdit' => $this->isGranted(CalendarEventVoter::EDIT, $calendarEvent),
                    'canDelete' => $this->isGranted(CalendarEventVoter::DELETE, $calendarEvent),
                ],
                'participation_status' => $participationStatus,
            ];
        }, $calendarEvents);

        return $this->json($formattedEvents);
    }

    #[Route('/event', name: 'event_create', methods: ['POST'])]
    public function createEvent(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $data = $request->getContent();
        $context = ['groups' => ['calendar_event:write']];

        $calendarEvent = $serializer->deserialize(
            $data,
            CalendarEvent::class,
            'json',
            $context
        );

        $errors = $this->updateEventFromData($calendarEvent, json_decode($data, true));

        $messages = [];
        if (0 < count($errors)) {
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            return $this->json(
                ['error' => implode(', ', $messages), 'success' => false],
                400
            );
        }

        return $this->json(['success' => true]);
    }

    #[Route('/event/{id}/weather-data', name: 'event_weather_data', methods: ['GET'])]
    public function viewEventWeatherData(CalendarEvent $calendarEvent): JsonResponse
    {
        $weatherData = $calendarEvent->getWeatherData();
        return $this->json($weatherData, 200, [], ['groups' => ['weather_data:read']]);
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

    #[Route('/upcoming', name: 'upcoming', methods: ['GET'])]
    public function retrieveUpcomingEvents(CalendarEventRepository $calendarEventRepository): JsonResponse
    {
        $calendarEvents = $calendarEventRepository->findUpcoming();

        return $this->json($calendarEvents, 200, [], ['groups' => ['calendar_event:read']]);
    }

    /**
     * @param array<mixed> $data
     *
     * @return ConstraintViolationList<int, mixed>
     */
    private function updateEventFromData(CalendarEvent $calendarEvent, array $data): ConstraintViolationList
    {
        $calendarEventTypeSpiel = $this->entityManager->getRepository(CalendarEventType::class)->findOneBy(['name' => 'Spiel']);
        $calendarEvent->setTitle($data['title'] ?? $calendarEvent->getTitle());
        $calendarEvent->setDescription($data['description'] ?? null);
        $calendarEvent->setStartDate(new DateTime($data['startDate']));

        if (isset($data['endDate'])) {
            $calendarEvent->setEndDate(new DateTime($data['endDate']));
        }

        if ($data['eventTypeId']) {
            $type = $this->entityManager->getReference(CalendarEventType::class, $data['eventTypeId']);
            $calendarEvent->setCalendarEventType($type);
        }

        if ($data['locationId']) {
            $location = $this->entityManager->getReference(Location::class, (int) $data['locationId']);
            $calendarEvent->setLocation($location);
        }

        if (
            $data['eventTypeId']
            && (int) $data['eventTypeId'] === $calendarEventTypeSpiel->getId()
        ) {
            if (null === $calendarEvent->getGame()) {
                $game = new Game();
                $calendarEvent->setGame($game);
                $game->setCalendarEvent($calendarEvent);
                $this->entityManager->persist($game);
            }
        }

        if (isset($data['game']['homeTeamId']) && $data['game']['homeTeamId']) {
            $homeTeam = $this->entityManager->getReference(Team::class, (int) $data['game']['homeTeamId']);
            $calendarEvent->getGame()?->setHomeTeam($homeTeam);
        }

        if (isset($data['game']['awayTeamId']) && $data['game']['awayTeamId']) {
            $awayTeam = $this->entityManager->getReference(Team::class, (int) $data['game']['awayTeamId']);
            $calendarEvent->getGame()?->setAwayTeam($awayTeam);
        }

        if (isset($data['gameTypeId']) && $data['gameTypeId']) {
            $gameType = $this->entityManager->getReference(GameType::class, (int) $data['gameTypeId']);
            $calendarEvent->getGame()?->setGameType($gameType);
        }

        if (isset($data['fussballDeUrl']) && $data['fussballDeUrl']) {
            $calendarEvent->getGame()?->setFussballDeUrl($data['fussballDeUrl']);
        }

        if (isset($data['fussballDeId']) && $data['fussballDeId']) {
            $calendarEvent->getGame()?->setFussballDeId($data['fussballDeId']);
        }

        $this->entityManager->persist($calendarEvent);

        /** @var ConstraintViolationList $errors */
        $errors = $this->validator->validate($calendarEvent);

        if ($calendarEvent->getGame()) {
            $gameErrors = $this->validator->validate($calendarEvent->getGame());
            foreach ($gameErrors as $gameError) {
                $errors->add($gameError);
            }
        }

        if ($errors->count()) {
            return $errors;
        }

        $this->entityManager->flush();

        return new ConstraintViolationList();
    }

    /** @return array<int, string> */
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
