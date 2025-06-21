<?php
namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', unique: true)]
    private $email;

    #[ORM\Column(type: 'string', length: 255)]
    private $firstName;

    #[ORM\Column(type:'string', length:255)]
    private $lastName;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;
    
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
        "this.getPlayer() === null or this.getClub() === null",
        message: "Ein Benutzer kann nicht gleichzeitig Spieler und Vereinsmitglied sein."
    )]
    private ?Player $player = null;

    #[ORM\OneToOne(targetEntity: Coach::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\Expression(
        "this.getCoach() === null or this.getClub() === null",
        message: "Ein Benutzer kann nicht gleichzeitig Trainer und Vereinsmitglied sein."
    )]
    private ?Coach $coach = null;

    #[ORM\ManyToOne(targetEntity: Club::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Club $club = null;

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
        return $this->firstName .' '. $this->lastName;
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
        
        if ($this->player !== null) {
            $roles[] = 'ROLE_PLAYER';
        }
        if ($this->coach !== null) {
            $roles[] = 'ROLE_COACH';
        }
        if ($this->club !== null) {
            $roles[] = 'ROLE_CLUB';
        }
        
        return array_unique($roles);
    }

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
        if ($player !== null && $this->club !== null) {
            throw new \InvalidArgumentException('Ein Benutzer kann nicht gleichzeitig Spieler und Vereinsmitglied sein.');
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
        if ($coach !== null && $this->club !== null) {
            throw new \InvalidArgumentException('Ein Benutzer kann nicht gleichzeitig Trainer und Vereinsmitglied sein.');
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
        if ($club !== null && ($this->player !== null || $this->coach !== null)) {
            throw new \InvalidArgumentException('Ein Benutzer kann nicht gleichzeitig Vereinsmitglied und Spieler/Trainer sein.');
        }
        $this->club = $club;
        return $this;
    }

    public function eraseCredentials(): void {}
}