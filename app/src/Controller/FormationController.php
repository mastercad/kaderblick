<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\User;
use App\Form\FormationType;
use App\Service\CoachTeamPlayerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationController extends AbstractController
{
    public function __construct(
        private CoachTeamPlayerService $coachTeamPlayerService
    ) {
    }

    #[Route('/formations', name: 'formations_index')]
    public function index(EntityManagerInterface $em): Response
    {
        // Nur Aufstellungen des aktuellen Trainers anzeigen
        $formations = $em->getRepository(Formation::class)->findBy([
            'user' => $this->getUser()
        ]);

        return $this->render('formation/index.html.twig', [
            'formations' => $formations
        ]);
    }

    #[Route('/formation/new', name: 'formation_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $formation->setUser($user);
            $em->persist($formation);
            $em->flush();

            return $this->redirectToRoute('formation_edit', ['id' => $formation->getId()]);
        }

        return $this->render('formation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/formation/{id}/edit', name: 'formation_edit')]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $em): Response
    {
        if ($request->isXmlHttpRequest()) {
            // AJAX-Anfrage zum Speichern der Formation
            $data = json_decode($request->getContent(), true);
            $formation->setFormationData($data);
            $em->flush();

            return $this->json(['status' => 'success']);
        }

        /** @var User $user */
        $user = $this->getUser();
        $availablePlayers = $this->coachTeamPlayerService->resolveAvailablePlayersForCoach($user);

        return $this->render('formation/edit.html.twig', [
            'formation' => $formation,
            'availablePlayers' => $availablePlayers,
        ]);
    }

    #[Route('/formation/team/{teamId}/players', name: 'formation_team_players', methods: ['GET'])]
    public function getTeamPlayers(int $teamId, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Prüfen ob der User berechtigt ist, auf dieses Team zuzugreifen
        $teams = $this->coachTeamPlayerService->collectCoachTeams($user);
        $team = null;

        foreach ($teams as $t) {
            if ($t->getId() === $teamId) {
                $team = $t;
                break;
            }
        }

        if (!$team) {
            return $this->json(['error' => 'Team nicht gefunden oder keine Berechtigung'], 404);
        }

        $players = $this->coachTeamPlayerService->collectTeamPlayers($team);

        $playersData = array_map(function ($playerData) {
            $player = $playerData['player'];

            return [
                'id' => $player->getId(),
                'name' => $player->getFullName(),
                'firstName' => $player->getFirstName(),
                'lastName' => $player->getLastName(),
                'shirtNumber' => $playerData['shirtNumber'],
            ];
        }, $players);

        return $this->json([
            'players' => $playersData,
            'teamName' => $team->getName()
        ]);
    }

    #[Route('/formation/{id}/delete', name: 'formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->request->get('_token'))) {
            $em->remove($formation);
            $em->flush();
        }

        return $this->redirectToRoute('formations_index');
    }
}
