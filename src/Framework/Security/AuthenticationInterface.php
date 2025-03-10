<?php

declare(strict_types=1);

namespace App\Framework\Security;

interface AuthenticationInterface
{
    /**
     * Authenticate a user with username and password
     */
    public function authenticate(string $username, string $password): ?UserInterface;

    /**
     * Get the currently authenticated user if any
     */
    public function getCurrentUser(): ?UserInterface;

    /**
     * Check if a user has one of the specified roles
     */
    public function hasRole(UserInterface $user, array $roles): bool;

    /**
     * Log out the current user
     */
    public function logout(): void;
}
