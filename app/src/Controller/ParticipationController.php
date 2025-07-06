<?php

namespace App\Controller;

use App\Entity\CalendarEvent;
use App\Entity\Participation;
use App\Entity\Player;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/participation', name: 'api_participation_')]
class ParticipationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('/event/{id}', name: 'status', methods: ['GET'])]
    public function getEventParticipations(CalendarEvent $event): Response
    {
        $participations = $this->em->getRepository(Participation::class)
            ->findBy(['event' => $event]);

        $players = $this->em->getRepository(Player::class)->findAll();

        // Liste aller Spieler mit ihrem Teilnahmestatus
        $status = array_map(function ($player) use ($participations) {
            $participation = array_filter($participations, fn ($p) => $p->getPlayer() === $player);

            return [
                'player' => $player,
                'status' => !empty($participation) ? current($participation)->isParticipating() : null,
                'note' => !empty($participation) ? current($participation)->getNote() : null
            ];
        }, $players);

        return $this->json([
            'event' => $event,
            'participations' => $status
        ]);
    }

    #[Route('/event/{id}/respond', name: 'respond', methods: ['POST'])]
    public function respond(Request $request, CalendarEvent $event): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $player = $user->getPlayer();

        if (!$player) {
            return $this->json(['error' => 'Nur Spieler können zu- oder absagen'], 403);
        }

        $isParticipating = $request->request->getBoolean('participating');
        $note = $request->request->get('note');

        $participation = $this->em->getRepository(Participation::class)
            ->findOneBy(['event' => $event, 'player' => $player]) ?? new Participation($player, $event);

        $participation->setPlayer($player)
            ->setEvent($event)
            ->setIsParticipating($isParticipating)
            ->setNote($note);

        $this->em->persist($participation);
        $this->em->flush();

        return $this->json(['message' => 'Teilnahmestatus aktualisiert']);
    }
}
