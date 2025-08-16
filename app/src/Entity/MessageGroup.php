<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(
    name: 'message_groups',
    indexes: [
        new ORM\Index(name: 'idx_message_groups_owner_id', columns: ['owner_id'])
    ]
)]
class MessageGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(
        name: 'owner_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    private User $owner;

    /** @var Collection<int, User> */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(
        name: 'message_group_members',
        joinColumns: [
            new ORM\JoinColumn(
                name: 'message_group_id',
                referencedColumnName: 'id',
                onDelete: 'CASCADE'
            )
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(
                name: 'user_id',
                referencedColumnName: 'id',
                onDelete: 'CASCADE'
            )
        ]
    )]
    private Collection $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    // Getter and setter methods
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /** @return Collection<int, User> */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        $this->members->removeElement($member);

        return $this;
    }
}
