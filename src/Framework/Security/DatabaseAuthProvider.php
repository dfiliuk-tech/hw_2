<?php

declare(strict_types=1);

namespace App\Framework\Security;

use App\Framework\Entity\User;
use PDO;
use RuntimeException;

class DatabaseAuthProvider implements AuthenticationInterface
{
    public function __construct(private PDO $PDO)
    {
        $this->initDatabase();
    }

    public function authenticate(string $username, string $password): ?UserInterface
    {
        $stmt = $this->PDO->prepare(
            'SELECT id, username, password, roles FROM users WHERE username = :username'
        );

        $stmt->execute(['username' => $username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        $user = new User(
            (int)$userData['id'],
            $userData['username'],
            $userData['password'],
            json_decode($userData['roles'], true) ?: []
        );

        if (!$user->verifyPassword($password)) {
            return null;
        }

        // Store user ID in session
        $_SESSION['user_id'] = $user->getId();

        return $user;
    }

    public function getCurrentUser(): ?UserInterface
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $stmt = $this->PDO->prepare('SELECT id, username, password, roles FROM users WHERE id = :id');
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            // Invalid session data
            unset($_SESSION['user_id']);
            return null;
        }

        return new User(
            (int)$userData['id'],
            $userData['username'],
            $userData['password'],
            json_decode($userData['roles'], true) ?: []
        );
    }

    public function hasRole(UserInterface $user, array $roles): bool
    {
        $userRoles = $user->getRoles();

        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                return true;
            }
        }

        return false;
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
    }

    public function createUser(string $username, string $password, array $roles = []): UserInterface
    {
        // Check if username already exists
        $stmt = $this->PDO->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            throw new RuntimeException("Username already exists");
        }

        $passwordHash = User::hashPassword($password);
        $rolesJson = json_encode($roles);

        $stmt = $this->PDO->prepare(
            'INSERT INTO users (username, password, roles) VALUES (:username, :password, :roles)'
        );
        $stmt->execute([
            'username' => $username,
            'password' => $passwordHash,
            'roles' => $rolesJson
        ]);

        $id = (int)$this->PDO->lastInsertId();

        return new User($id, $username, $passwordHash, $roles);
    }

    private function initDatabase(): void
    {
        // Create users table if it doesn't exist
        $this->PDO->exec('
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                roles TEXT NOT NULL
            )
        ');

        // Check if we need to create default users
        $stmt = $this->PDO->query('SELECT COUNT(*) FROM users');
        if ($stmt->fetchColumn() === 0) {
            // Create default admin and user accounts
            $this->createUser('admin', 'admin123', ['ROLE_ADMIN']);
            $this->createUser('user', 'user123', ['ROLE_USER']);
        }
    }
}
