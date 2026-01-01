<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class VerificationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ParameterBagInterface $params,
    ) {
    }

    #[Route('/verify/email', name: 'verify_email_legacy', methods: ['GET'])]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        MailerInterface $mailer
    ): JsonResponse {
        $token = $request->query->get('Token');

        if (!$token) {
            return new JsonResponse(
                ['error' => 'Token fehlt.'],
                400
            );
        }

        $user = $userRepository->findUserByValidationToken($token);

        if (!$user instanceof User) {
            return new JsonResponse(
                ['error' => 'Ungültiger Token.'],
                404
            );
        }

        $user->setIsVerified(true)
             ->setIsEnabled(true);

        $this->em->persist($user);
        $this->em->flush();

        $email = (new TemplatedEmail())
            ->from('no-reply@kaderblick.de')
            ->to($user->getEmail())
            ->subject('Willkommen auf der Plattform')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context([
                'name' => $user->getEmail(),
                'website_url' => $this->params->get('app.website_url'),
                'contact_email' => $this->params->get('app.contact_email'),
                'phone_number' => $this->params->get('app.phone_number')
            ]);

        $mailer->send($email);

        return new JsonResponse([
            'message' => 'Dein Account wurde erfolgreich aktiviert! Du kannst dich jetzt anmelden.'
        ], 200);
    }

    #[Route('/verify/success', name: 'app_verify_success')]
    public function verifySuccess(): JsonResponse
    {
        return $this->json(['message' => 'E-Mail erfolgreich bestätigt']);
    }
}
