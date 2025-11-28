<?php

namespace App\Entity;

use App\Repository\UserLevelRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserLevelRepository::class)]
#[ORM\Table(name: 'user_levels')]
class UserLevel
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'userLevel', targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\Column(type: 'integer')]
    private int $xpTotal;

    #[ORM\Column(type: 'integer')]
    private int $level;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getXpTotal(): int
    {
        return $this->xpTotal;
    }

    public function setXpTotal(int $xpTotal): self
    {
        $this->xpTotal = $xpTotal;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
