<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'messages')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $sender;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'message_recipients')]
    private Collection $recipients;

    #[ORM\Column(length: 255)]
    private string $subject;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'datetime')]
    private DateTime $sentAt;

    #[ORM\Column(type: 'json')]
    private array $readBy = [];

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
        $this->sentAt = new DateTime();
    }

    // Getter and setter methods...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): User
    {
        return $this->sender;
    }

    public function setSender(User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(User $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients->add($recipient);
        }

        return $this;
    }

    public function removeRecipient(User $recipient): self
    {
        $this->recipients->removeElement($recipient);

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSentAt(): DateTime
    {
        return $this->sentAt;
    }

    public function markAsRead(User $user): self
    {
        if (!in_array($user->getId(), $this->readBy)) {
            $this->readBy[] = $user->getId();
        }

        return $this;
    }

    public function isReadBy(User $user): bool
    {
        return in_array($user->getId(), $this->readBy);
    }
}
