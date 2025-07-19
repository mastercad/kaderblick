<?php

namespace App\Controller\Api;

use App\Entity\Feedback;
use App\Entity\User;
use App\Service\GithubService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/feedback')]
class FeedbackController extends AbstractController
{
    #[Route('/create', name: 'api_feedback_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            $feedback = new Feedback();
            $feedback->setUser($user);
            $feedback->setType($request->request->get('type'));
            $feedback->setMessage($request->request->get('message'));
            $feedback->setUrl($request->request->get('url'));
            $feedback->setUserAgent($request->request->get('userAgent'));

            if ($request->files->has('screenshot')) {
                $file = $request->files->get('screenshot');

                if (!$file->isValid()) {
                    throw new Exception('Ungültige Datei');
                }

                $fileName = 'feedback_' . uniqid() . '.png';
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/feedback';

                try {
                    $file->move($uploadDir, $fileName);
                    $feedback->setScreenshotPath('/uploads/feedback/' . $fileName);
                } catch (Exception $e) {
                    error_log('Screenshot move error: ' . $e->getMessage());
                    throw $e;
                }
            }

            $entityManager->persist($feedback);
            $entityManager->flush();

            return $this->json(['status' => 'success']);
        } catch (Exception $e) {
            error_log('Feedback error: ' . $e->getMessage());

            return $this->json([
                'status' => 'error',
                'message' => 'Fehler beim Speichern des Feedbacks: ' . $e->getMessage()
            ], 500);
        }
    }
}
