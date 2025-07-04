<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        MailerInterface $mailer
    ): Response {
        $token = $request->query->get('Token');

        $user = $userRepository->findUserByValidationToken($token, new DateTime());

        if (!$user instanceof User) {
            return new Response('error: Ungültiger Token', 400);
        }

        $user->setIsVerified(true);

        // Automatische Zuordnung prüfen
        $player = $this->em->getRepository(Player::class)->findOneBy(['email' => $user->getEmail()]);
        if ($player) {
            $user->setPlayer($player);
        }

        $coach = $this->em->getRepository(Coach::class)->findOneBy(['email' => $user->getEmail()]);
        if ($coach) {
            $user->setCoach($coach);
        }

        $this->em->persist($user);
        $this->em->flush();

        $email = (new TemplatedEmail())
            ->from('no-reply@byte-artist.de')
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

        $this->addFlash(
            'verification_success',
            'Dein Account wurde erfolgreich aktiviert! Du kannst dich jetzt anmelden.'
        );

        return $this->redirectToRoute('home');
    }

    #[Route('/verify/success', name: 'app_verify_success')]
    public function verifySuccess(): JsonResponse
    {
        //        return $this->render('verification/verify.html.twig');
        return $this->json(['message' => 'E-Mail erfolgreich bestätigt']);
    }
}
