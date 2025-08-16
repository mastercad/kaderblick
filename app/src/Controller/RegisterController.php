<?php

namespace App\Controller;

use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\User;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class RegisterController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/register', name: 'register', methods: ['GET'])]
    public function registerFormular(Request $request): Response
    {
        return $this->render('auth/register.html.twig');
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UrlGeneratorInterface $urlGenerator,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $token = bin2hex(random_bytes(32));

        // Split fullname into firstName and lastName
        $fullName = trim($data['fullName'] ?? '');
        $nameParts = explode(' ', $fullName);

        // Last word is lastName, everything before is firstName
        $lastName = array_pop($nameParts);
        $firstName = implode(' ', $nameParts);

        $user = new User();
        $user->setEmail($data['email'])
            ->setPassword($passwordHasher->hashPassword($user, $data['password']))
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setVerificationToken($token)
            ->setIsVerified(false)
            ->setIsEnabled(false)
            ->setVerificationExpires((new DateTime())->modify('+1 month'));

        try {
            $this->em->persist($user);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $exception) {
            return new JsonResponse(
                ['error' => 'E-Mail-Adresse bereits registriert.'],
                400,
            );
        }

        $url = $urlGenerator->generate(
            'api_verify_email',
            ['Token' => $user->getVerificationToken()],
            UrlGeneratorInterface::ABS_URL
        );

        $email = (new TemplatedEmail())
            ->from('no-reply@byte-artist.de')
            ->to($user->getEmail())
            ->subject('Bitte bestätige deine E-Mail')
            ->htmlTemplate('emails/verification.html.twig')
            ->context([
                'name' => $user->getEmail(),
                'signedUrl' => $url
            ]);

        $mailer->send($email);

        return new JsonResponse(['message' => 'Registrierung erfolgreich. Bitte E-Mail bestätigen.'], 201);
    }

    #[Route('/verify-email/{token}', name: 'verify_email')]
    public function verifyEmail(string $token): Response
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Der Verifizierungslink ist ungültig oder abgelaufen.');
        }

        $user->setIsVerified(true)
             ->setVerificationToken(null)
             ->setVerificationExpires(null);

        // Automatische Zuordnung prüfen wird jetzt über UserRelations gemacht
        // Diese Legacy-Implementierung wird entfernt

        $this->em->flush();

        $this->addFlash('success', 'Deine E-Mail-Adresse wurde erfolgreich verifiziert.');

        return $this->redirectToRoute('app_login');
    }
}
