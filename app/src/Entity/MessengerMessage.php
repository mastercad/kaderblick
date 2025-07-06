<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: 'messenger_messages')]
#[ORM\Index(columns: ['queue_name'], name: 'idx_queue_name')]
#[ORM\Index(columns: ['available_at'], name: 'idx_available_at')]
#[ORM\Index(columns: ['delivered_at'], name: 'idx_delivered_at')]
class MessengerMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'bigint')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private string $body;

    #[ORM\Column(type: 'text')]
    private string $headers;

    #[ORM\Column(type: 'string', length: 255)]
    private string $queue_name;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $created_at;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $available_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $delivered_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getHeaders(): string
    {
        return $this->headers;
    }

    public function setHeaders(string $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getQueueName(): string
    {
        return $this->queue_name;
    }

    public function setQueueName(string $queue_name): self
    {
        $this->queue_name = $queue_name;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getAvailableAt(): DateTimeInterface
    {
        return $this->available_at;
    }

    public function setAvailableAt(DateTimeInterface $available_at): self
    {
        $this->available_at = $available_at;

        return $this;
    }

    public function getDeliveredAt(): ?DateTimeInterface
    {
        return $this->delivered_at;
    }

    public function setDeliveredAt(?DateTimeInterface $delivered_at): self
    {
        $this->delivered_at = $delivered_at;

        return $this;
    }
}
