<?php

declare(strict_types=1);

namespace Tests\App\Framework\Security;

use App\Framework\Entity\User;
use App\Framework\Security\DatabaseAuthProvider;
use App\Framework\Security\UserInterface;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class DatabaseAuthProviderTest extends TestCase
{
    private DatabaseAuthProvider $authProvider;
    private PDO $pdo;

    protected function setUp(): void
    {
        // Create an in-memory SQLite database for testing
        $this->pdo = new PDO('sqlite::memory:');

        // Create users table
        $this->pdo->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                roles TEXT NOT NULL
            )
        ');

        // Create auth provider with the test database
        $this->authProvider = new DatabaseAuthProvider($this->pdo);

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function tearDown(): void
    {
        // Clean up session data
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function testCreateUser(): void
    {
        // Create a test user
        $user = $this->authProvider->createUser('testuser', 'password123', ['ROLE_USER']);

        // Verify user was created
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());

        // Verify user exists in database
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => 'testuser']);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($userData);
        $this->assertEquals('testuser', $userData['username']);
        $this->assertEquals(json_encode(['ROLE_USER']), $userData['roles']);
    }

    public function testCreateUserWithDuplicateUsername(): void
    {
        // Create first user
        $this->authProvider->createUser('duplicate', 'password123', ['ROLE_USER']);

        // Attempt to create another user with the same username
        $this->expectException(\RuntimeException::class);
        $this->authProvider->createUser('duplicate', 'anotherpassword', ['ROLE_USER']);
    }

    public function testAuthenticate(): void
    {
        // Create a test user
        $this->authProvider->createUser('authuser', 'securepass', ['ROLE_USER']);

        // Authenticate with correct credentials
        $user = $this->authProvider->authenticate('authuser', 'securepass');

        // Verify authentication success
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEquals('authuser', $user->getUsername());

        // Verify user ID is stored in session
        $this->assertArrayHasKey('user_id', $_SESSION);
        $this->assertEquals($user->getId(), $_SESSION['user_id']);
    }

    public function testAuthenticateWithInvalidUsername(): void
    {
        // Authenticate with non-existent username
        $user = $this->authProvider->authenticate('nonexistent', 'anypassword');

        // Authentication should fail
        $this->assertNull($user);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testAuthenticateWithInvalidPassword(): void
    {
        // Create a test user
        $this->authProvider->createUser('passuser', 'correctpass', ['ROLE_USER']);

        // Authenticate with wrong password
        $user = $this->authProvider->authenticate('passuser', 'wrongpass');

        // Authentication should fail
        $this->assertNull($user);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testGetCurrentUser(): void
    {
        // Create a test user
        $createdUser = $this->authProvider->createUser('currentuser', 'userpass', ['ROLE_USER']);

        // Set user ID in session
        $_SESSION['user_id'] = $createdUser->getId();

        // Get current user
        $currentUser = $this->authProvider->getCurrentUser();

        // Verify correct user is returned
        $this->assertInstanceOf(UserInterface::class, $currentUser);
        $this->assertEquals($createdUser->getId(), $currentUser->getId());
        $this->assertEquals('currentuser', $currentUser->getUsername());
    }

    public function testGetCurrentUserWithInvalidSession(): void
    {
        // Set invalid user ID in session
        $_SESSION['user_id'] = 9999;

        // Get current user
        $currentUser = $this->authProvider->getCurrentUser();

        // Should return null and clear session
        $this->assertNull($currentUser);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testHasRole(): void
    {
        // Create users with different roles using unique usernames
        $adminUser = $this->authProvider->createUser('admin_role_test', 'adminpass', ['ROLE_ADMIN', 'ROLE_USER']);
        $regularUser = $this->authProvider->createUser('user_role_test', 'userpass', ['ROLE_USER']);

        // Check admin has admin role
        $this->assertTrue($this->authProvider->hasRole($adminUser, ['ROLE_ADMIN']));

        // Check admin has user role
        $this->assertTrue($this->authProvider->hasRole($adminUser, ['ROLE_USER']));

        // Check admin has either role
        $this->assertTrue($this->authProvider->hasRole($adminUser, ['ROLE_EDITOR', 'ROLE_ADMIN']));

        // Check regular user does not have admin role
        $this->assertFalse($this->authProvider->hasRole($regularUser, ['ROLE_ADMIN']));

        // Check regular user has user role
        $this->assertTrue($this->authProvider->hasRole($regularUser, ['ROLE_USER']));
    }

    public function testLogout(): void
    {
        // Set up session
        $_SESSION['user_id'] = 1;

        // Logout
        $this->authProvider->logout();

        // Verify session data is cleared
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }
}
