<?php

namespace App\Controller\Api;

use App\Entity\CalendarEvent;
use App\Entity\TeamRide;
use App\Entity\TeamRidePassenger;
use App\Entity\User;
use App\Repository\CalendarEventRepository;
use App\Repository\TeamRideRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/teamrides', name: 'api_teamride_')]
class TeamRidesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private TeamRideRepository $teamRideRepo,
        private CalendarEventRepository $eventRepo
    ) {
    }

    #[Route('/event/{eventId}', methods: ['GET'])]
    public function list(int $eventId): JsonResponse
    {
        $event = $this->eventRepo->find($eventId);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }
        $rides = $this->teamRideRepo->findBy(['event' => $event]);
        $data = [];
        foreach ($rides as $ride) {
            $data[] = [
                'id' => $ride->getId(),
                'driver' => $ride->getDriver()->getFullName(),
                'seats' => $ride->getSeats(),
                'note' => $ride->getNote(),
                'availableSeats' => $ride->getSeats() - $ride->getPassengers()->count(),
                'passengers' => array_map(fn ($p) => [
                    'id' => $p->getUser()->getId(),
                    'name' => $p->getUser()->getFullName(),
                ], $ride->getPassengers()->toArray()),
            ];
        }

        return $this->json(['rides' => $data]);
    }

    #[Route('/add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $event = $this->eventRepo->find($data['event_id'] ?? null);
        /** @var User $user */
        $user = $this->getUser();

        if (!$event instanceof CalendarEvent) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_BAD_REQUEST);
        }

        $ride = new TeamRide();
        $ride->setEvent($event);
        $ride->setDriver($user);
        $ride->setSeats($data['seats'] ?? 1);
        $ride->setNote($data['note'] ?? null);
        $this->em->persist($ride);
        $this->em->flush();

        return $this->json(['success' => true, 'id' => $ride->getId()]);
    }

    #[Route('/book/{rideId}', methods: ['POST'])]
    public function book(int $rideId): JsonResponse
    {
        $ride = $this->teamRideRepo->find($rideId);
        /** @var User $user */
        $user = $this->getUser();

        if (!$ride instanceof TeamRide) {
            return $this->json(['error' => 'Ride not found'], Response::HTTP_BAD_REQUEST);
        }
        if ($ride->getPassengers()->count() >= $ride->getSeats()) {
            return $this->json(['error' => 'No seats available'], Response::HTTP_CONFLICT);
        }
        foreach ($ride->getPassengers() as $p) {
            if ($p->getUser()->getId() === $user->getId()) {
                return $this->json(['error' => 'Already booked'], Response::HTTP_CONFLICT);
            }
        }
        $passenger = new TeamRidePassenger();
        $passenger->setTeamRide($ride);
        $passenger->setUser($user);
        $this->em->persist($passenger);
        $this->em->flush();

        return $this->json(['success' => true]);
    }
}
