<?php

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccessTokenRepository")
 * @ORM\Table(name="access_tokens")
 */
class AccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private User $user;

    /**
     * @ORM\Column(type="string")
     */
    private string $token;

    /**
     * @ORM\Column(type="datetime_immutable", name="created_at")
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", name="expires_at")
     */
    private DateTimeInterface $expiresAt;

    /**
     * @param User $user
     * @param string $token generated token
     * @param int $expiresIn token lifetime in seconds
     * @return static
     */
    public static function create(User $user, string $token, int $expiresIn): self
    {
        $entity = new self();
        $entity->user = $user;
        $entity->token = $token;
        $now = new DateTimeImmutable();
        $entity->createdAt = $now;
        $entity->expiresAt = $now->modify('+' . $expiresIn . ' seconds');
        return $entity;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): DateTimeInterface
    {
        return $this->expiresAt;
    }
}