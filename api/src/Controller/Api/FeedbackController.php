<?php

namespace App\Controller\Api;

use App\Entity\Feedback;
use App\Entity\User;
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

            $jsonData = json_decode($request->getContent(), true);

            $feedback = new Feedback();
            $feedback->setUser($user);
            $feedback->setType($jsonData['type']);
            $feedback->setMessage($jsonData['message']);
            $feedback->setUrl($jsonData['url']);
            $feedback->setUserAgent($jsonData['userAgent']);

            if (isset($jsonData['screenshot']) && is_string($jsonData['screenshot']) && str_starts_with($jsonData['screenshot'], 'data:image/png;base64,')) {
                $fileName = 'feedback_' . uniqid() . '.png';
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/feedback';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $base64 = substr($jsonData['screenshot'], strlen('data:image/png;base64,'));
                $binaryData = base64_decode($base64);
                $filePath = $uploadDir . '/' . $fileName;
                if (false === file_put_contents($filePath, $binaryData)) {
                    throw new Exception('Screenshot konnte nicht gespeichert werden');
                }
                $feedback->setScreenshotPath('/uploads/feedback/' . $fileName);
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
