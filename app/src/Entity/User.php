<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore-next-line Property is set by Doctrine and never written in code */
    private int $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
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

    #[ORM\OneToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Expression(
        'this.getPlayer() === null or this.getClub() === null',
        message: 'Ein Benutzer kann nicht gleichzeitig Spieler und Vereinsmitglied sein.'
    )]
    private ?Player $player = null;

    #[ORM\OneToOne(targetEntity: Coach::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Expression(
        'this.getCoach() === null or this.getClub() === null',
        message: 'Ein Benutzer kann nicht gleichzeitig Trainer und Vereinsmitglied sein.'
    )]
    private ?Coach $coach = null;

    #[ORM\ManyToOne(targetEntity: Club::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Club $club = null;

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

        if (null !== $this->player) {
            $roles[] = 'ROLE_PLAYER';
        }
        if (null !== $this->coach) {
            $roles[] = 'ROLE_COACH';
        }
        if (null !== $this->club) {
            $roles[] = 'ROLE_CLUB';
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

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
    {
        if (null !== $player && null !== $this->club) {
            throw new InvalidArgumentException('Ein Benutzer kann nicht gleichzeitig Spieler und Vereinsmitglied sein.');
        }
        $this->player = $player;

        return $this;
    }

    public function getCoach(): ?Coach
    {
        return $this->coach;
    }

    public function setCoach(?Coach $coach): self
    {
        if (null !== $coach && null !== $this->club) {
            throw new InvalidArgumentException('Ein Benutzer kann nicht gleichzeitig Trainer und Vereinsmitglied sein.');
        }
        $this->coach = $coach;

        return $this;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): self
    {
        if (null !== $club && (null !== $this->player || null !== $this->coach)) {
            throw new InvalidArgumentException('Ein Benutzer kann nicht gleichzeitig Vereinsmitglied und Spieler/Trainer sein.');
        }
        $this->club = $club;

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

    public function eraseCredentials(): void
    {
    }
}
