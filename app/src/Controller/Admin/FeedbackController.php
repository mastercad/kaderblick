<?php

namespace App\Controller\Admin;

use App\Entity\Feedback;
use App\Repository\FeedbackRepository;
use App\Service\GithubService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/feedback')]
#[IsGranted('ROLE_ADMIN')]
class FeedbackController extends AbstractController
{
    #[Route('/', name: 'admin_feedback_index', methods: ['GET'])]
    public function index(FeedbackRepository $feedbackRepository, GithubService $githubService): Response
    {
        //        dump($githubService->retrieveIssues());

        return $this->render('admin/feedback/index.html.twig', [
            'unresolved' => $feedbackRepository->findUnresolved(),
            'read' => $feedbackRepository->findByIsReadAndUnresolved(),
            'resolved' => $feedbackRepository->findByResolved(),
            'statistics' => $feedbackRepository->fetchTypeStatistics()
        ]);
    }

    #[Route('/{id}/mark-read', name: 'admin_feedback_mark_read', methods: ['POST'])]
    public function markRead(Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        $feedback->setIsRead(true);
        $entityManager->flush();

        $this->addFlash('success', 'Feedback wurde als gelesen markiert.');

        return $this->redirectToRoute('admin_feedback_index');
    }

    #[Route('/{id}/resolve', name: 'admin_feedback_resolve', methods: ['POST'])]
    public function resolve(
        Request $request,
        Feedback $feedback,
        EntityManagerInterface $entityManager
    ): Response {
        $feedback->setResolved(true);
        $feedback->setIsRead(true); // Automatisch als gelesen markieren
        $feedback->setAdminNote($request->request->get('adminNote'));

        $entityManager->flush();

        $this->addFlash('success', 'Feedback wurde als erledigt markiert.');

        return $this->redirectToRoute('admin_feedback_index');
    }
}
