<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Repository\TeamRepository;
use App\Security\Voter\TeamVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamsController extends AbstractController
{
    #[Route('/teams', name: 'teams_index')]
    public function index(TeamRepository $teamRepository): Response
    {
        /** @var ?User $user */
        $user = $this->getUser();
        $teams = $teamRepository->fetchOptimizedList($user);

        return $this->render('teams/index.html.twig', [
            'teams' => $teams,
            'permissions' => [
                'CREATE' => TeamVoter::CREATE,
                'EDIT' => TeamVoter::EDIT,
                'VIEW' => TeamVoter::VIEW,
                'DELETE' => TeamVoter::DELETE
            ]
        ]);
    }

    #[Route('/team/edit/{id}', name: 'team_edit', methods: ['GET'])]
    public function edit(Team $team): Response
    {
        return $this->render('teams/edit_modal.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/team/update/{id}', name: 'team_update', methods: ['POST'])]
    public function update(Request $request, Team $team, EntityManagerInterface $em): Response
    {
        $team->setName($request->request->get('name'));

        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/team/delete/{id}', name: 'teams_delete', methods: ['POST'])]
    public function delete(Team $team, EntityManagerInterface $em): Response
    {
        $em->remove($team);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
