<?php

namespace App\Entity;

use App\Repository\CameraRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CameraRepository::class)]
class Camera
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'camerasCreated')]
    #[ORM\JoinColumn(name: 'created_from_id', nullable: false)]
    private ?User $createdFrom = null;

    #[ORM\ManyToOne(inversedBy: 'camerasUpdated')]
    #[ORM\JoinColumn(name: 'updated_from_id')]
    private ?User $updatedFrom = null;

    #[ORM\Column(name: 'created_at')]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Video>
     */
    #[ORM\OneToMany(targetEntity: Video::class, mappedBy: 'camera')]
    private Collection $videos;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedFrom(): ?User
    {
        return $this->createdFrom;
    }

    public function setCreatedFrom(?User $createdFrom): static
    {
        $this->createdFrom = $createdFrom;

        return $this;
    }

    public function getUpdatedFrom(): ?User
    {
        return $this->updatedFrom;
    }

    public function setUpdatedFrom(?User $updatedFrom): static
    {
        $this->updatedFrom = $updatedFrom;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setCamera($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): static
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getCamera() === $this) {
                $video->setCamera(null);
            }
        }

        return $this;
    }
}
