<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: '"user"')]
#[UniqueEntity(fields: ["email"], message: "Diese E-Mail ist bereits registriert.")]
#[ORM\HasLifecycleCallbacks]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private null|int $id = null;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    //Assert Test
    #[Assert\NotBlank(message: "Need to use an email address.")]
    private string $email;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    //Assert Test
    #[Assert\NotBlank(message: "Need to use an username.")]
    private string $name;

    /** @var string[] */
    #[ORM\Column(type: "json")]
    private array $roles = ["ROLE_USER"];

    // Hinweis: enthält den gehashten Password-String
    #[ORM\Column(type: "string", length: 255)]
    private string $password;

    #[ORM\Column(type: "boolean")]
    private bool $isVerified = false;

    #[ORM\Column(type: "string", length: 128, nullable: true)]
    private null|string $verificationToken = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private null|\DateTimeImmutable $verificationTokenExpiresAt = null;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private null|\DateTimeImmutable $lastLogin = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'author')]
    private Collection $articles;

    public function __construct(string $email)
    {
        $this->email = mb_strtolower($email);
        $this->roles = ['ROLE_USER'];
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->articles = new ArrayCollection();
    }

    public function __toString():string
    {
        return $this->name;
    }

    public function getId(): null|int
    {
        return $this->id;
    }

    // UserIdentifier (Symfony 5.3+)
    public function getUserIdentifier(): string
    {
        if (!$this->email) {
            throw new \LogicException("User has no email");
        }
        return $this->email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = mb_strtolower($email);
        return $this;
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

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = array_values(array_unique($roles));
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    // Setze das gehashte Passwort (nicht das rohe!)
    public function setPassword(string $hashedPassword): self
    {
        $this->password = $hashedPassword;
        return $this;
    }

    public function getSalt(): null|string
    {
        return null; // moderne Hasher enthalten salt intern
    }

    public function eraseCredentials(): void
    {
        // keine temporären sensitiven daten vorhanden
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

    public function getVerificationToken(): null|string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(null|string $token): self
    {
        $this->verificationToken = $token;
        return $this;
    }

    public function getVerificationTokenExpiresAt(): null|\DateTimeImmutable
    {
        return $this->verificationTokenExpiresAt;
    }

    public function setVerificationTokenExpiresAt(null|\DateTimeImmutable $dt): self
    {
        $this->verificationTokenExpiresAt = $dt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getLastLogin(): null|\DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function setLastLogin(null|\DateTimeImmutable $dt): self
    {
        $this->lastLogin = $dt;
        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setAuthor($this);
        }

        return $this;
    }
}
