<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(
    name: 'users',
    indexes: [
        new ORM\Index(name: 'idx_users_player_id', columns: ['player_id']),
        new ORM\Index(name: 'idx_users_coach_id', columns: ['coach_id']),
        new ORM\Index(name: 'idx_users_club_id', columns: ['club_id'])
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_users_email', columns: ['email'])
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private int $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $email;

    #[ORM\Column(name: 'first_name', type: 'string', length: 255)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 255)]
    private string $lastName;

    /** @var array<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isEnabled = false;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $verificationExpires = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $height = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $weight = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $shoeSize = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $shirtSize = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $pantsSize = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $newEmail = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $emailVerificationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $emailVerificationTokenExpiresAt = null;

    /**
     * @var Collection<int, DashboardWidget>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: DashboardWidget::class)]
    private Collection $widgets;

    /**
     * @var Collection<int, UserRelation>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserRelation::class)]
    private Collection $relations;

    /**
     * @var Collection<int, PushSubscription>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PushSubscription::class, orphanRemoval: true)]
    private Collection $pushSubscriptions;

    public function __construct()
    {
        $this->widgets = new ArrayCollection();
        $this->relations = new ArrayCollection();
        $this->pushSubscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        if ($this->isVerified) {
            $roles[] = 'ROLE_USER';
        } else {
            $roles[] = 'ROLE_GUEST';
        }
        if ($this->relations->count() > 0) {
            $roles[] = 'ROLE_RELATED_USER';
        }

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        $this->roles[] = $role;

        $this->roles = array_unique($this->roles);

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): self
    {
        $this->verificationToken = $verificationToken;

        return $this;
    }

    public function getVerificationExpires(): ?DateTime
    {
        return $this->verificationExpires;
    }

    public function setVerificationExpires(?DateTime $verificationExpires): self
    {
        $this->verificationExpires = $verificationExpires;

        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getShoeSize(): ?float
    {
        return $this->shoeSize;
    }

    public function setShoeSize(?float $shoeSize): self
    {
        $this->shoeSize = $shoeSize;

        return $this;
    }

    public function getShirtSize(): ?string
    {
        return $this->shirtSize;
    }

    public function setShirtSize(?string $shirtSize): self
    {
        $this->shirtSize = $shirtSize;

        return $this;
    }

    public function getPantsSize(): ?string
    {
        return $this->pantsSize;
    }

    public function setPantsSize(?string $pantsSize): self
    {
        $this->pantsSize = $pantsSize;

        return $this;
    }

    public function getNewEmail(): ?string
    {
        return $this->newEmail;
    }

    public function setNewEmail(?string $newEmail): self
    {
        $this->newEmail = $newEmail;

        return $this;
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    public function setEmailVerificationToken(?string $token): self
    {
        $this->emailVerificationToken = $token;

        return $this;
    }

    public function getEmailVerificationTokenExpiresAt(): ?DateTime
    {
        return $this->emailVerificationTokenExpiresAt;
    }

    public function setEmailVerificationTokenExpiresAt(?DateTime $expiresAt): self
    {
        $this->emailVerificationTokenExpiresAt = $expiresAt;

        return $this;
    }

    /**
     * @return Collection<int, DashboardWidget>
     */
    public function getWidgets(): Collection
    {
        return $this->widgets;
    }

    public function addWidget(DashboardWidget $widget): self
    {
        if (!$this->widgets->contains($widget)) {
            $this->widgets->add($widget);
            $widget->setUser($this);
        }

        return $this;
    }

    public function removeWidget(DashboardWidget $widget): self
    {
        if ($this->widgets->removeElement($widget)) {
            // set the owning side to null (unless already changed)
            if ($widget->getUser() === $this) {
                $widget->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserRelation>
     */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function addRelation(UserRelation $relation): self
    {
        if (!$this->relations->contains($relation)) {
            $this->relations->add($relation);
            $relation->setUser($this);
        }

        return $this;
    }

    public function removeRelation(UserRelation $relation): self
    {
        if ($this->relations->removeElement($relation)) {
            if ($relation->getUser() === $this) {
                $relation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PushSubscription>
     */
    public function getPushSubscriptions(): Collection
    {
        return $this->pushSubscriptions;
    }

    public function addPushSubscription(PushSubscription $subscription): self
    {
        if (!$this->pushSubscriptions->contains($subscription)) {
            $this->pushSubscriptions->add($subscription);
            $subscription->setUser($this);
        }

        return $this;
    }

    public function removePushSubscription(PushSubscription $subscription): self
    {
        if ($this->pushSubscriptions->removeElement($subscription)) {
            if ($subscription->getUser() === $this) {
                $subscription->setUser(null);
            }
        }

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}
