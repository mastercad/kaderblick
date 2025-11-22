<?php

namespace App\Service;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Twig\Environment;

class PasswordResetService
{
    private const TOKEN_VALIDITY_HOURS = 24;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PasswordResetTokenRepository $tokenRepository,
        private UserRepository $userRepository,
        private MailerInterface $mailer,
        private Environment $twig,
        private UserPasswordHasherInterface $passwordHasher,
        private string $frontendUrl,
        private string $mailerFrom
    ) {
    }

    public function requestPasswordReset(string $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            // Aus Sicherheitsgründen geben wir trotzdem true zurück,
            // um keine Informationen über existierende E-Mail-Adressen zu leaken
            return true;
        }

        // Invalidiere alle vorhandenen Tokens für diesen User
        $this->tokenRepository->invalidateAllTokensForUser($user);

        // Erstelle neuen Token
        $token = $this->generateSecureToken();
        $passwordResetToken = new PasswordResetToken();
        $passwordResetToken->setToken($token);
        $passwordResetToken->setUser($user);
        $passwordResetToken->setExpiresAt(
            (new DateTime())->modify('+' . self::TOKEN_VALIDITY_HOURS . ' hours')
        );

        $this->entityManager->persist($passwordResetToken);
        $this->entityManager->flush();

        // Sende E-Mail
        $this->sendResetEmail($user, $token);

        return true;
    }

    public function validateToken(string $token): ?PasswordResetToken
    {
        return $this->tokenRepository->findValidTokenByToken($token);
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $resetToken = $this->validateToken($token);

        if (!$resetToken) {
            return false;
        }

        $user = $resetToken->getUser();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $resetToken->setUsed(true);

        $this->entityManager->flush();

        return true;
    }

    private function generateSecureToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function sendResetEmail(User $user, string $token): void
    {
        $resetUrl = $this->frontendUrl . '/reset-password/' . $token;

        $html = $this->twig->render('emails/password_reset.html.twig', [
            'user' => $user,
            'resetUrl' => $resetUrl,
            'validityHours' => self::TOKEN_VALIDITY_HOURS,
        ]);

        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Passwort zurücksetzen')
            ->html($html);

        $this->mailer->send($email);
    }

    public function cleanupExpiredTokens(): int
    {
        return $this->tokenRepository->deleteExpiredTokens();
    }
}
