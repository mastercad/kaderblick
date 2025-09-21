<?php

namespace App\Entity;

use App\Repository\SurveyOptionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurveyOptionRepository::class)]
class SurveyOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    /** @var Collection<int, SurveyQuestion> */
    #[ORM\ManyToMany(targetEntity: SurveyQuestion::class, inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $questions;

    #[ORM\Column(type: 'string', length: 255)]
    private string $optionText;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, SurveyQuestion>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    /**
     * @param Collection<int, SurveyQuestion> $questions
     */
    public function setQuestions(Collection $questions): self
    {
        $this->questions = $questions;

        return $this;
    }

    public function getOptionText(): ?string
    {
        return $this->optionText;
    }

    public function setOptionText(string $optionText): self
    {
        $this->optionText = $optionText;

        return $this;
    }

    public function addQuestion(SurveyQuestion $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
        }

        return $this;
    }

    public function removeQuestion(SurveyQuestion $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
        }

        return $this;
    }
}
