<?php
// src/Entity/RefreshToken.php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity()]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $expiresAt;

    public function __construct(string $token, UserInterface $user, DateTimeInterface $expiresAt)
    {
        $this->user = $user;
        $this->token = $token;
        $this->expiresAt = $expiresAt;
    }

    public function isExpired(): bool
    {
        return new DateTime() > $this->expiresAt;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
