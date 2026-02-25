<?php

namespace App\Entity;

use App\Repository\VideoSegmentRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'video_segments')]
#[ORM\Index(name: 'idx_video_segments_video_id', columns: ['video_id'])]
#[ORM\Index(name: 'idx_video_segments_user_id', columns: ['user_id'])]
#[ORM\Entity(repositoryClass: VideoSegmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class VideoSegment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['video_segment:read'])]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'videoSegments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Ein Video muss zugeordnet sein.')]
    #[Groups(['video_segment:read', 'video_segment:write'])]
    private ?Video $video = null;

    #[ORM\ManyToOne(inversedBy: 'videoSegments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Ein Benutzer muss zugeordnet sein.')]
    #[Groups(['video_segment:read'])]
    private ?User $user = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Die Startminute muss angegeben werden.')]
    #[Assert\PositiveOrZero(message: 'Die Startminute muss 0 oder positiv sein.')]
    #[Groups(['video_segment:read', 'video_segment:write'])]
    private ?float $startMinute = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull(message: 'Die Länge in Sekunden muss angegeben werden.')]
    #[Assert\Positive(message: 'Die Länge muss positiv sein.')]
    #[Groups(['video_segment:read', 'video_segment:write'])]
    private ?int $lengthSeconds = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['video_segment:read', 'video_segment:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['video_segment:read', 'video_segment:write'])]
    private ?string $subTitle = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['video_segment:read', 'video_segment:write'])]
    private bool $includeAudio = true;

    #[ORM\Column]
    #[Groups(['video_segment:read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['video_segment:read'])]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(['video_segment:read', 'video_segment:write'])]
    private int $sortOrder = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): static
    {
        $this->video = $video;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStartMinute(): ?float
    {
        return $this->startMinute;
    }

    public function setStartMinute(float $startMinute): static
    {
        $this->startMinute = $startMinute;

        return $this;
    }

    public function getLengthSeconds(): ?int
    {
        return $this->lengthSeconds;
    }

    public function setLengthSeconds(int $lengthSeconds): static
    {
        $this->lengthSeconds = $lengthSeconds;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    public function setSubTitle(?string $subTitle): static
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    public function isIncludeAudio(): bool
    {
        return $this->includeAudio;
    }

    public function setIncludeAudio(bool $includeAudio): static
    {
        $this->includeAudio = $includeAudio;

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

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
