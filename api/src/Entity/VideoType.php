<?php

namespace App\Entity;

use App\Repository\VideoTypeRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(
    name: 'video_types',
    indexes: [
        new ORM\Index(name: 'idx_video_types_created_from', columns: ['created_from_id']),
        new ORM\Index(name: 'idx_video_types_updated_from', columns: ['updated_from_id'])
    ]
)]
#[ORM\Entity(repositoryClass: VideoTypeRepository::class)]
class VideoType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $sort = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'videoTypesCreatedFrom')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdFrom = null;

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'videoTypesUpdatedFrom')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $updatedFrom = null;

    /**
     * @var Collection<int, Video>
     */
    #[ORM\OneToMany(targetEntity: Video::class, mappedBy: 'videoType')]
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

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): static
    {
        $this->sort = $sort;

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

    public function getCreatedFrom(): ?User
    {
        return $this->createdFrom;
    }

    public function setCreatedFrom(?User $createdFrom): static
    {
        $this->createdFrom = $createdFrom;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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
            $video->setVideoType($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): static
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getVideoType() === $this) {
                $video->setVideoType(null);
            }
        }

        return $this;
    }
}
