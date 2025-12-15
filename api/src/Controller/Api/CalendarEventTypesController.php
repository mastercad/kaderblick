<?php

namespace App\Controller\Api;

use App\Entity\CalendarEventType;
use App\Security\Voter\CalendarEventTypeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/calendar-event-types', name: 'api_calendar_event_types_')]
class CalendarEventTypesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'api_calendar_event_types_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $calendarEventTypes = $this->em->getRepository(CalendarEventType::class)->findAll();

        // Filtere basierend auf VIEW-Berechtigung
        $calendarEventTypes = array_filter($calendarEventTypes, fn ($type) => $this->isGranted(CalendarEventTypeVoter::VIEW, $type));

        return $this->json([
            'entries' => array_map(fn (CalendarEventType $calendarEventType) => [
                'id' => $calendarEventType->getId(),
                'name' => $calendarEventType->getName(),
                'color' => $calendarEventType->getColor()
            ], $calendarEventTypes),
        ]);
    }
}
