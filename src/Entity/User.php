<?php

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private ?int $id = null;
    /**
     * @ORM\Column(type="string", unique=true)
     */
    private string $login;
    /**
     * @ORM\Column(type="string", unique=true)
     */
    private string $email;
    /**
     * @ORM\Column(type="string", name="display_name")
     */
    private string $displayName;
    /**
     * @ORM\Column(type="string")
     */
    private string $password;
    /**
     * @ORM\Column(type="datetime_immutable", name="registered_at")
     */
    private DateTimeInterface $registeredAt;
    /**
     * @ORM\Column(type="datetime_immutable", name="last_login_at", nullable=true)
     */
    private ?DateTimeInterface $lastLoginAt = null;
    /**
     * @ORM\OneToMany(targetEntity="AccessToken", mappedBy="user")
     */
    private Collection $accessTokens;
    /**
     * @ORM\OneToMany(targetEntity="RefreshToken", mappedBy="user")
     */
    private Collection $refreshTokens;

    public static function create(string $login, string $email, string $displayName, string $hashedPassword): self
    {
        $entity = new self();
        $entity->login = $login;
        $entity->email = $email;
        $entity->displayName = $displayName;
        $entity->password = $hashedPassword;
        $now = new DateTimeImmutable();
        $entity->registeredAt = $now;
        return $entity;
    }

    public function __construct()
    {
        $this->accessTokens = new ArrayCollection();
        $this->refreshTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRegisteredAt(): DateTimeInterface
    {
        return $this->registeredAt;
    }

    public function getLastLoginAt(): ?DateTimeInterface
    {
        return $this->lastLoginAt;
    }
}