<?php

namespace App\Entity;

use App\Repository\SurveyQuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurveyQuestionRepository::class)]
class SurveyQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Survey::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $questionText;

    #[ORM\ManyToOne(targetEntity: SurveyOptionType::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?SurveyOptionType $type = null;

    /**
     * @var Collection<int, SurveyOption>
     */
    #[ORM\ManyToMany(targetEntity: SurveyOption::class, mappedBy: 'questions')]
    private Collection $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getQuestionText(): ?string
    {
        return $this->questionText;
    }

    public function setQuestionText(string $questionText): self
    {
        $this->questionText = $questionText;

        return $this;
    }

    public function getType(): ?SurveyOptionType
    {
        return $this->type;
    }

    public function setType(SurveyOptionType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, SurveyOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(SurveyOption $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->addQuestion($this);
        }

        return $this;
    }

    public function removeOption(SurveyOption $option): self
    {
        if ($this->options->removeElement($option)) {
            $option->removeQuestion($this);
        }

        return $this;
    }
}
