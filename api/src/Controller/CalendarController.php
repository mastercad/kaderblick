<?php

namespace App\Controller;

use App\Entity\CalendarEvent;
use App\Entity\CalendarEventType;
use App\Entity\GameType;
use App\Entity\Location;
use App\Entity\TaskAssignment;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\WeatherData;
use App\Repository\CalendarEventRepository;
use App\Repository\ParticipationRepository;
use App\Security\Voter\CalendarEventVoter;
use App\Service\CalendarEventService;
use App\Service\EmailNotificationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/calendar', name: 'api_calendar_')]
class CalendarController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EmailNotificationService $emailService,
        private readonly ParticipationRepository $participationRepository,
        private readonly CalendarEventService $calendarEventService
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

        $unfilteredCalendarEvents = $calendarEventRepository->findBetweenDates($start, $end);

        // Filtere basierend auf VIEW-Berechtigung
        $calendarEvents = array_filter($unfilteredCalendarEvents, fn ($calendarEvent) => $this->isGranted(CalendarEventVoter::VIEW, $calendarEvent));

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

            // Find task through TaskAssignment
            $taskFromAssignment = null;
            $taskAssignment = $this->entityManager->getRepository(TaskAssignment::class)
                ->findOneBy(['calendarEvent' => $calendarEvent]);
            if ($taskAssignment && $taskAssignment->getTask()) {
                $task = $taskAssignment->getTask();
                $taskFromAssignment = [
                    'id' => $task->getId(),
                    'isRecurring' => $task->isRecurring(),
                    'recurrenceMode' => $task->getRecurrenceMode(),
                    'recurrenceRule' => $task->getRecurrenceRule(),
                    'rotationUsers' => $task->getRotationUsers()->map(fn ($rotationUser) => [
                        'id' => $rotationUser->getId(),
                        'fullName' => $rotationUser->getFullName()
                    ])->toArray(),
                    'rotationCount' => $task->getRotationCount(),
                    'offset' => $task->getOffsetDays(),
                ];
            }

            return [
                'id' => $calendarEvent->getId(),
                'title' => $calendarEvent->getTitle(),
                'start' => $calendarEvent->getStartDate()->format('Y-m-d\TH:i:s'),
                'end' => $endDate->format('Y-m-d\TH:i:s'),
                'description' => $calendarEvent->getDescription(),
                'weatherData' => [
                    'weatherCode' => $calendarEvent->getWeatherData()?->getDailyWeatherData()['weathercode'][0] ?? null,
                ],
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
                    ],
                    'league' => [
                        'id' => $calendarEvent->getGame()->getLeague()?->getId(),
                        'name' => $calendarEvent->getGame()->getLeague()?->getName()
                    ]
                ] : null,
                'task' => $taskFromAssignment,
                'type' => $calendarEvent->getCalendarEventType() ? [
                    'id' => $calendarEvent->getCalendarEventType()->getId(),
                    'name' => $calendarEvent->getCalendarEventType()->getName(),
                    'color' => $calendarEvent->getCalendarEventType()->getColor()
                ] : null,
                'location' => $calendarEvent->getLocation() ? [
                    'id' => $calendarEvent->getLocation()->getId(),
                    'name' => $calendarEvent->getLocation()->getName(),
                    'latitude' => $calendarEvent->getLocation()->getLatitude(),
                    'longitude' => $calendarEvent->getLocation()->getLongitude(),
                    'city' => $calendarEvent->getLocation()->getCity(),
                    'address' => $calendarEvent->getLocation()->getAddress()
                ] : null,
                'permissions' => [
                    'canCreate' => $this->isGranted(CalendarEventVoter::CREATE, $calendarEvent->getGame() ?? null),
                    'canEdit' => $this->isGranted(CalendarEventVoter::EDIT, $calendarEvent),
                    'canDelete' => $this->isGranted(CalendarEventVoter::DELETE, $calendarEvent),
                ],
                'participation_status' => $participationStatus,
            ];
        }, $calendarEvents);

        return $this->json(array_values($formattedEvents));
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

        if (!$this->isGranted(CalendarEventVoter::CREATE, $calendarEvent)) {
            return $this->json(['error' => 'Forbidden', 'success' => false], 403);
        }

        $errors = $this->calendarEventService->updateEventFromData($calendarEvent, json_decode($data, true));

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

    /**
     * Drag & Drop endpunkt für die Aktualisierung von Kalendereinträgen.
     */
    #[Route('/event/{id}', name: 'event_update', methods: ['PUT'])]
    public function updateCalendarEvent(CalendarEvent $calendarEvent, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isGranted(CalendarEventVoter::EDIT, $calendarEvent)) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $this->calendarEventService->updateEventFromData($calendarEvent, $data);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/event/{id}', name: 'event_delete', methods: ['DELETE'])]
    public function deleteEvent(CalendarEvent $calendarEvent, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isGranted(CalendarEventVoter::DELETE, $calendarEvent)) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $deletionMode = $data['deletionMode'] ?? 'single'; // 'single' oder 'series'

        $taskAssignmentRepo = $entityManager->getRepository(TaskAssignment::class);
        $taskAssignment = $taskAssignmentRepo->findOneBy(['calendarEvent' => $calendarEvent]);
        $task = $taskAssignment?->getTask();

        if ($task && 'series' === $deletionMode) {
            $taskAssignments = $taskAssignmentRepo->findBy(['task' => $task]);
            foreach ($taskAssignments as $taskAssignment) {
                if ($taskAssignment->getCalendarEvent()) {
                    $this->calendarEventService->deleteCalendarEventWithDependencies($taskAssignment->getCalendarEvent());
                }
                $entityManager->remove($taskAssignment);
            }

            $taskRotationUsers = $task->getRotationUsers();
            foreach ($taskRotationUsers as $user) {
                $task->removeRotationUser($user);
            }

            $entityManager->remove($task);
            $entityManager->flush();
        } else {
            $this->calendarEventService->deleteCalendarEventWithDependencies($calendarEvent);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/event/{id}/weather-data', name: 'event_weather_data', methods: ['GET'])]
    public function viewEventWeatherData(CalendarEvent $calendarEvent): JsonResponse
    {
        if (!$this->isGranted(CalendarEventVoter::VIEW, $calendarEvent)) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $weatherData = $calendarEvent->getWeatherData();

        if (!$weatherData instanceof WeatherData) {
            return $this->json([
                'dailyWeatherData' => null,
                'hourlyWeatherData' => null
            ]);
        }

        $indexStart = $calendarEvent->getStartDate()->format('H');
        $indexEnd = 23 - (23 - ($calendarEvent->getEndDate() ? $calendarEvent->getEndDate()->format('H') : 0));

        $rawHourlyWeatherData = $weatherData->getHourlyWeatherData();
        $hourlyWeatherData = [];

        foreach ($rawHourlyWeatherData as $key => $information) {
            foreach ($information as $index => $value) {
                if ($index < (int) $indexStart || $index > (int) $indexEnd) {
                    continue;
                }
                $hourlyWeatherData[$key][$index] = $value;
            }
        }

        return $this->json([
            'dailyWeatherData' => $weatherData->getDailyWeatherData() ?: [],
            'hourlyWeatherData' => $hourlyWeatherData,
        ]);
    }

    #[Route('/event/notify', name: 'event_notify', methods: ['POST'])]
    public function notifyAboutEvent(CalendarEvent $calendarEvent): JsonResponse
    {
        if (!$this->isGranted(CalendarEventVoter::EDIT, $calendarEvent)) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $recipients = $this->calendarEventService->loadEventRecipients($calendarEvent);
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
