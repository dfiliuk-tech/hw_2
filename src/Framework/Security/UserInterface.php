<?php

namespace App\Framework\Security;

interface UserInterface
{
    /**
     * Get user ID
     */
    public function getId(): int;

    /**
     * Get username
     */
    public function getUsername(): string;

    /**
     * Get user roles
     *
     * @return string[]
     */
    public function getRoles(): array;

    /**
     * Check if password matches
     */
    public function verifyPassword(string $password): bool;
}
