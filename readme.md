# Minimal PHP Framework

A lightweight PHP framework skeleton with PSR-7 and PSR-11 compatibility.

## Features

- Docker-based development environment
- Custom PSR-7 HTTP message implementation
- PHP-DI container with autowiring (PSR-11 compatible)
- Front controller architecture
- Static route configuration
- HTTP method-based routing
- PHPUnit testing setup
- Static code analysis tools integration

## Project Structure

```
.
├── config/                 # Configuration files
│   └── routes.php          # Route definitions
├── docker-compose.yaml     # Docker Compose configuration
├── Dockerfile              # PHP container configuration
├── phpunit.xml             # PHPUnit configuration
├── public/                 # Web-accessible files
│   └── index.php           # Front controller
├── src/                    # Source code
│   ├── App/                # Application-specific code
│   │   ├── Controller/     # Controller classes
│   │   └── Service/        # Service classes
│   └── Framework/          # Framework components
│       ├── Application.php # Main application class
│       ├── Http/           # PSR-7 HTTP implementation
│       └── Routing/        # Routing system
├── tests/                  # Test suite
│   ├── App/                # Application tests
│   └── Framework/          # Framework tests
├── var/                    # Variable data
│   └── cache/              # PHP-DI compiled container cache
├── vendor/                 # Composer dependencies
└── composer.json           # Composer configuration
```

## Setup and Installation

1. Clone the repository
2. Start the Docker containers:
   ```
   docker-compose up -d
   ```
3. Install dependencies:
   ```
   docker-compose exec php composer install
   ```
4. Access the application:
   ```
   http://localhost:8000
   ```

## Creating and Registering Routes

Routes are defined statically in the `config/routes.php` file:

```php
// config/routes.php
return [
    ['GET', '/', HomeController::class, 'index'],
    ['GET', '/api/status', ApiController::class, 'status'],
    ['POST', '/api/status', ApiController::class, 'update'],
    
    // Add your new route
    ['GET', '/your-route', YourController::class, 'yourAction'],
];
```

Example controller:

```php
<?php
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface;

class YourController
{
    public function yourAction(ServerRequestInterface $request): string
    {
        return "Your response content";
    }
}
```

With PHP-DI's autowiring, your controllers will be automatically instantiated with their dependencies. No explicit controller registration is needed!

## Dependency Injection with Autowiring

This framework uses PHP-DI's autowiring, which automatically resolves dependencies based on type-hints:

```php
namespace App\Controller;

use App\Service\UserService;
use Psr\Http\Message\ServerRequestInterface;

class UserController
{
    private UserService $userService;
    
    // PHP-DI will automatically instantiate and inject UserService
    public function __construct(UserService $userService) 
    {
        $this->userService = $userService;
    }
    
    public function list(ServerRequestInterface $request): string
    {
        $users = $this->userService->getAllUsers();
        return "Users: " . implode(', ', $users);
    }
}
```

For custom dependency configuration, you can add definitions to the container builder in `public/index.php`:

```php
$containerBuilder->addDefinitions([
    // Interface to implementation mapping
    SomeInterface::class => \DI\get(SomeImplementation::class),
    
    // Configuration values
    'config.debug' => true,
]);
```

For more advanced usage, see the [PHP-DI documentation](https://php-di.org/doc/).

## Unit Testing

The framework uses PHPUnit for testing. Tests are organized in a directory structure that mirrors the source code:

```
tests/
├── App/                # Application tests
│   ├── Controller/     # Controller tests
│   └── Service/        # Service tests
└── Framework/          # Framework tests
    ├── Http/           # HTTP component tests
    └── Routing/        # Routing component tests
```

### Running Tests

To run all tests:

```
docker-compose exec php composer test
```

To run a specific test class:

```
docker-compose exec php vendor/bin/phpunit tests/Framework/Http/UriTest.php
```

### Writing Tests

Test classes should extend `PHPUnit\Framework\TestCase` and follow these naming conventions:

- Test classes should be in the same namespace as the class they test, but under the `Tests\` namespace.
- Test methods should start with `test`.

Example:

```php
namespace Tests\App\Service;

use App\Service\ExampleService;
use PHPUnit\Framework\TestCase;

class ExampleServiceTest extends TestCase
{
    public function testGetData(): void
    {
        $service = new ExampleService();
        $data = $service->getData();
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('key1', $data);
        // More assertions...
    }
}
```

## Static Code Analysis Tools

This project integrates three static code analysis tools:

### 1. PHPStan

PHPStan performs static analysis to find errors in your code.

To run:
```
docker-compose exec php vendor/bin/phpstan analyze -l 5 src
```

Output example:
```
 2/2 [==============================] 100%

 [OK] No errors
```

### 2. PHP_CodeSniffer

PHP_CodeSniffer checks your code against PSR-12 coding standards.

To run:
```
docker-compose exec php vendor/bin/phpcs --standard=PSR12 src
```

Output example:
```
.................
Time: 84ms; Memory: 8MB
```

### 3. PHP Mess Detector

PHP Mess Detector finds potential problems like unused variables, complex code, etc.

To run:
```
docker-compose exec php vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode
```

Output example:
```
src/Framework/Application.php:97  Avoid unused private methods such as 'wrapResponse'.
```

### Running All Tools

You can run all tools together using the Composer script:

```
docker-compose exec php composer analyze
```

## Implementation Details

### PSR-7 HTTP Messages
The framework includes a custom implementation of the PSR-7 HTTP message interfaces:

- `Framework\Http\Request` - Basic HTTP request implementation
- `Framework\Http\ServerRequest` - Server-side HTTP request
- `Framework\Http\Response` - HTTP response
- `Framework\Http\Stream` - Stream implementation for request/response bodies
- `Framework\Http\Uri` - URI implementation

### PSR-11 Container
The framework uses PHP-DI, a powerful PSR-11 compatible dependency injection container with autowiring.

### Routing
A basic but functional routing system:

- `Framework\Routing\Router` - Matches requests to routes and dispatches them to controllers

### Application
The main application class handles the request/response lifecycle:

- `Framework\Application` - Processes requests and generates responses

# Security Implementation Documentation

This document explains the security measures implemented in our PHP framework.

## Overview

The security implementation focuses on addressing the following key areas:

1. **Authentication** - Verifying user identity
2. **Authorization** - Controlling access based on user roles
3. **CSRF Protection** - Preventing cross-site request forgery
4. **XSS Prevention** - Protecting against cross-site scripting
5. **Secure HTTP Headers** - Strengthening browser security
6. **SQL Injection Prevention** - Using prepared statements
7. **Secure Session Management** - Protecting user sessions

## 1. Authentication System

### User Entity

- Implemented a `UserInterface` interface and `User` class to represent users
- Password hashing using PHP's secure bcrypt implementation (cost factor 12)
- No plain text passwords stored in the database

### Authentication Provider

- Created `DatabaseAuthProvider` that implements `AuthenticationInterface`
- Session-based authentication state management
- Secure password verification using `password_verify()`

### Login Process

- Form-based authentication with CSRF protection
- Login errors don't reveal whether username or password was incorrect
- Redirects to original destination after successful login

## 2. Authorization System

### Role-Based Access Control

- Two built-in user roles: 'ROLE_USER' and 'ROLE_ADMIN'
- Admin Dashboard only accessible to users with 'ROLE_ADMIN' role
- Authorization checks in controllers and middleware

### Default Users

Two default users are created:
- Admin (username: 'admin', password: 'admin123')
- Regular User (username: 'user', password: 'user123')

## 3. CSRF Protection

- CSRF token generation with secure random bytes
- Token validation for all state-changing operations
- Token expiration (1 hour by default)
- Token per session with automatic regeneration

## 4. XSS Prevention

- All user-generated content is escaped using `htmlspecialchars()` with ENT_QUOTES
- HTML escaping helper method in the `SecurityMiddleware` class
- Content-Security-Policy header to restrict script sources

## 5. Secure HTTP Headers

Added the following security headers:

- **X-Content-Type-Options: nosniff** - Prevents MIME type sniffing
- **X-XSS-Protection: 1; mode=block** - Additional XSS protection
- **X-Frame-Options: DENY** - Prevents clickjacking
- **Content-Security-Policy** - Restricts resource loading
- **Referrer-Policy: no-referrer-when-downgrade** - Controls referrer information
- **Strict-Transport-Security** - Enforces HTTPS (when HTTPS is enabled)

## 6. SQL Injection Prevention

- Used PDO prepared statements for all database operations
- Parameter binding for all user-supplied data
- Type casting for numeric values

## 7. Secure Session Management

- HTTP-only cookies to prevent JavaScript access
- Same-site cookie attribute set to 'Strict'
- Secure flag enabled when using HTTPS
- Session data validation before use

## Integration in the Framework

### Security Middleware

The `SecurityMiddleware` class integrates all security features:

- Authentication and authorization checks
- CSRF token generation and validation
- Output escaping for XSS prevention
- Security header management

### Application Integration

The `Application` class was modified to:
- Process requests through the security middleware
- Authenticate users for all non-public routes
- Redirect unauthenticated users to the login page

## How to Use the Security System

### Protecting a Route

Routes in `routes.php` are automatically protected unless they're in the public routes list.

### Checking User Roles in Controllers

```php
public function dashboard(ServerRequest $request): Response
{
    // Verify user has admin role
    if (!$this->security->verifyAuthorization($request, ['ROLE_ADMIN'])) {
        return new Response(403, ['Content-Type' => 'text/html'], "Access Denied");
    }
    
    // Admin-only code here
}
```

### Adding CSRF Protection to Forms

```php
// Generate a token
$csrfToken = $this->security->generateCsrfToken();

// Add to form
echo '<input type="hidden" name="csrf_token" value="' . $this->security->escapeOutput($csrfToken) . '">';

// Validate in controller
if (!$this->security->validateCsrfToken($data['csrf_token'])) {
    // Invalid token handling
}
```

### Preventing XSS

```php
// Always escape output
echo $this->security->escapeOutput($userProvidedContent);
```

## Recommendations for Future Enhancements

1. **Password Policies** - Implement password strength requirements and expiration
2. **Multi-factor Authentication** - Add a second layer of authentication
3. **API Authentication** - Implement JWT or OAuth for API access
4. **Rate Limiting** - Prevent brute force attacks
5. **Audit Logging** - Track security events and user actions
6. **User Account Management** - Add account recovery, email verification, etc.

## License

MIT