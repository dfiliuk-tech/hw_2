<?php

namespace App\Framework\Entity;

use App\Framework\Security\UserInterface;

class User implements UserInterface
{
    private int $id;
    private string $username;
    private string $passwordHash;
    private array $roles;

    public function __construct(int $id, string $username, string $passwordHash, array $roles = [])
    {
        $this->id = $id;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->roles = $roles;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    /**
     * Create a password hash
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
