<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class RefreshTokenService
{
    private EntityManagerInterface $em;
    private int $refreshTokenTtl;

    public function __construct(EntityManagerInterface $em, int $refreshTokenTtl = 604800)
    {
        $this->em = $em;
        $this->refreshTokenTtl = $refreshTokenTtl;
    }

    public function createRefreshToken(User $user): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = new DateTime();
        $expiresAt->modify("+{$this->refreshTokenTtl} seconds");

        $refreshToken = new RefreshToken($token, $user, $expiresAt);
        $this->em->persist($refreshToken);
        $this->em->flush();

        return $token;
    }

    public function validateRefreshToken(string $token): User
    {
        $refreshToken = $this->em->getRepository(RefreshToken::class)->findOneBy(['token' => $token]);

        if (!$refreshToken || $refreshToken->isExpired()) {
            throw new AuthenticationException('Invalid or expired refresh token.');
        }

        return $refreshToken->getUser();
    }

    public function revokeRefreshToken(string $token): void
    {
        $refreshToken = $this->em->getRepository(RefreshToken::class)->find($token);

        if ($refreshToken) {
            $this->em->remove($refreshToken);
            $this->em->flush();
        }
    }
}
