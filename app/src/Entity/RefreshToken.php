<?php

// src/Entity/RefreshToken.php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity()]
#[ORM\Table(
    name: 'refresh_tokens',
    indexes: [
        new ORM\Index(name: 'idx_refresh_token_user_id', columns: ['user_id'])
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_refresh_token_token', columns: ['token'])
    ]
)]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(
        name: 'user_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
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
