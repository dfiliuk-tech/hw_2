<?php

declare(strict_types=1);

namespace Tests\App\Framework\Entity;

use App\Framework\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserConstruction(): void
    {
        $id = 1;
        $username = 'testuser';
        $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
        $roles = ['ROLE_USER', 'ROLE_EDITOR'];

        $user = new User($id, $username, $passwordHash, $roles);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($roles, $user->getRoles());
    }

    public function testVerifyPasswordWithCorrectPassword(): void
    {
        $password = 'secure_password';
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $user = new User(1, 'user', $passwordHash, ['ROLE_USER']);

        $this->assertTrue($user->verifyPassword($password));
    }

    public function testVerifyPasswordWithIncorrectPassword(): void
    {
        $correctPassword = 'secure_password';
        $wrongPassword = 'wrong_password';
        $passwordHash = password_hash($correctPassword, PASSWORD_BCRYPT);

        $user = new User(1, 'user', $passwordHash, ['ROLE_USER']);

        $this->assertFalse($user->verifyPassword($wrongPassword));
    }

    public function testHashPassword(): void
    {
        $password = 'test_password';
        $hash = User::hashPassword($password);

        // Hash should be a non-empty string
        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);

        // Hash should be valid
        $this->assertTrue(password_verify($password, $hash));

        // Different passwords should not verify
        $this->assertFalse(password_verify('different_password', $hash));

        // Each hash should be different (salt is applied)
        $anotherHash = User::hashPassword($password);
        $this->assertNotEquals($hash, $anotherHash);
    }

    public function testUserWithEmptyRoles(): void
    {
        $user = new User(1, 'username', 'hash', []);

        $this->assertIsArray($user->getRoles());
        $this->assertEmpty($user->getRoles());
    }

    public function testUserWithSingleRole(): void
    {
        $user = new User(1, 'username', 'hash', ['ROLE_USER']);

        $this->assertIsArray($user->getRoles());
        $this->assertCount(1, $user->getRoles());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testUserWithMultipleRoles(): void
    {
        $roles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
        $user = new User(1, 'username', 'hash', $roles);

        $this->assertIsArray($user->getRoles());
        $this->assertCount(3, $user->getRoles());
        $this->assertEquals($roles, $user->getRoles());
    }
}
