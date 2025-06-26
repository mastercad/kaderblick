<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class EmailVerificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private string $emailFrom = 'noreply@kaderblick.byte-artist.de'
    ) {}

    public function sendEmailChangeVerification(User $user): void
    {
        $token = bin2hex(random_bytes(32));
        $user->setEmailVerificationToken($token);
        $user->setEmailVerificationTokenExpiresAt(new \DateTime('+24 hours'));
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $verificationUrl = $this->urlGenerator->generate(
            'api_verify_email_change',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from($this->emailFrom)
            ->to($user->getNewEmail())
            ->subject('E-Mail-Adresse bestätigen')
            ->htmlTemplate('emails/email_change_verification.html.twig')
            ->context([
                'verificationUrl' => $verificationUrl,
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }

    public function verifyEmailChangeToken(string $token): ?User
    {
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['emailVerificationToken' => $token]);

        if (!$user) {
            return null;
        }

        if ($user->getEmailVerificationTokenExpiresAt() < new \DateTime()) {
            return null;
        }

        $user->setEmailVerificationToken(null);
        $user->setEmailVerificationTokenExpiresAt(null);

        return $user;
    }
}
