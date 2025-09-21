<?php

namespace App\Entity;

use App\Repository\SurveyResponseRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurveyResponseRepository::class)]
class SurveyResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private ?int $userId = null;

    #[ORM\ManyToOne(targetEntity: Survey::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    // Antworten als JSON-Array: [{questionId, answer: [optionId|text]}]
    /**
     * @var array<int, mixed>|null
     */
    #[ORM\Column(type: 'json')]
    private ?array $answers = null;

    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): self
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * @return array<int, mixed>|null
     */
    public function getAnswers(): ?array
    {
        return $this->answers;
    }

    /**
     * @param array<int, mixed>|null $answers
     */
    public function setAnswers(?array $answers): self
    {
        $this->answers = $answers;

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
}
