<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\FormationType;
use App\Entity\User;
use App\Service\CoachTeamPlayerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationController extends AbstractController
{
    public function __construct(
        private CoachTeamPlayerService $coachTeamPlayerService,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/formations', name: 'formations_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (null === $user) {
            $user = $this->entityManager->getRepository(User::class)->find(1);
        }

        // Nur Aufstellungen des aktuellen Trainers anzeigen
        $formations = $em->getRepository(Formation::class)->findBy([
            'user' => $user
        ]);

        return $this->json(['formations' => array_map(fn (Formation $formation) => [
            'id' => $formation->getId(),
            'name' => $formation->getName(),
            'formationData' => $formation->getFormationData(),
            'formationType' => [
                'id' => $formation->getFormationType()->getId(),
                'name' => $formation->getFormationType()->getName(),
            ]
        ], $formations)]);
    }

    #[Route('/formation/new', name: 'formation_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $formationType = $em->getRepository(FormationType::class)->findOneBy(['name' => 'fußball']);
            $data = json_decode($request->getContent(), true);
            $formation = new Formation();
            $formation->setUser($user);
            $formation->setFormationType($formationType);
            $formation->setName($data['name']);
            $formation->setFormationData($data['formationData']);
            $em->persist($formation);
            $em->flush();

            return $this->json(['success' => true]);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/formation/{id}/edit', name: 'formation_edit')]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $formationType = $em->getRepository(FormationType::class)->findOneBy(['name' => 'fußball']);
            $data = json_decode($request->getContent(), true);
            $formation->setUser($user);
            $formation->setFormationType($formationType);
            $formation->setName($data['name']);
            $formation->setFormationData($data['formationData']);
            $em->flush();

            return $this->json(['success' => true]);
        }

        /** @var User $user */
        $user = $this->getUser();
        $availablePlayers = $this->coachTeamPlayerService->resolveAvailablePlayersForCoach($user);

        return $this->json([
            'formation' => [
                'id' => $formation->getId(),
                'name' => $formation->getName(),
                'formationData' => $formation->getFormationData(),
                'formationType' => [
                    'id' => $formation->getFormationType()->getId(),
                    'name' => $formation->getFormationType()->getName(),
                ]
            ],
            'availablePlayers' => $availablePlayers
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
                'id' => $player['id'],
                'name' => $player['name'],
                'shirtNumber' => $playerData['shirtNumber'],
            ];
        }, $players);

        return $this->json([
            'players' => $playersData,
            'teamName' => $team->getName()
        ]);
    }

    #[Route('/formation/{id}/delete', name: 'formation_delete', methods: ['DELETE'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $em): JsonResponse
    {
        //        if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->request->get('_token'))) {
        $em->remove($formation);
        $em->flush();
        //        }

        //        return $this->redirectToRoute('formations_index');
        return $this->json(['success' => true]);
    }
}
