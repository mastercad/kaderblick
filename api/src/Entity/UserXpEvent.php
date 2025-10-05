<?php

namespace App\Entity;

use App\Repository\UserXpEventRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserXpEventRepository::class)]
#[ORM\Table(name: "user_xp_events")]
class UserXpEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(type: "integer")]
    private int $userId;

    #[ORM\Column(type: "string", length: 50)]
    private string $actionType;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $actionId = null;

    #[ORM\Column(type: "integer")]
    private int $xpValue;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: "json", nullable: true)]
    private ?array $meta = null;

    #[ORM\Column(type: "datetime_immutable")]
    private DateTimeImmutable $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getActionType(): string
    {
        return $this->actionType;
    }

    public function setActionType(string $actionType): self
    {
        $this->actionType = $actionType;
        return $this;
    }

    public function getActionId(): ?int
    {
        return $this->actionId;
    }

    public function setActionId(?int $actionId): self
    {
        $this->actionId = $actionId;
        return $this;
    }

    public function getXpValue(): int
    {
        return $this->xpValue;
    }

    public function setXpValue(int $xpValue): self
    {
        $this->xpValue = $xpValue;
        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }

    /**
     * @param array<string, mixed>|null $meta
     */
    public function setMeta(?array $meta): self
    {
        $this->meta = $meta;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
