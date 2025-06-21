<?php

namespace App\Entity;

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
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private string $body;

    #[ORM\Column(type: 'text')]
    private string $headers;

    #[ORM\Column(type: 'string', length: 255)]
    private string $queue_name;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $available_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $delivered_at = null;
}
