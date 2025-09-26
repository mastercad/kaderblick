<?php

namespace App\Entity;

use App\Repository\PushSubscriptionRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PushSubscriptionRepository::class)]
#[ORM\Table(name: 'push_subscriptions')]
#[ORM\UniqueConstraint(name: 'uniq_push_endpoint', columns: ['user_id', 'endpoint'])]
class PushSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'pushSubscriptions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 500)]
    private ?string $endpoint = null;

    #[ORM\Column(length: 255)]
    private ?string $publicKey = null;

    #[ORM\Column(length: 255)]
    private ?string $authToken = null;

    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    public function getAuthToken(): ?string
    {
        return $this->authToken;
    }

    public function setAuthToken(string $authToken): self
    {
        $this->authToken = $authToken;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getP256dhKey(): ?string
    {
        return $this->publicKey;
    }

    public function setP256dhKey(string $p256dhKey): self
    {
        $this->publicKey = $p256dhKey;

        return $this;
    }

    public function getAuthKey(): ?string
    {
        return $this->authToken;
    }

    public function setAuthKey(string $authKey): self
    {
        $this->authToken = $authKey;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'keys' => [
                'p256dh' => $this->publicKey,
                'auth' => $this->authToken
            ]
        ];
    }
}
