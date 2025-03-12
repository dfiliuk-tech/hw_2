<?php

declare(strict_types=1);

namespace Tests\App\Framework\Security;

use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\Framework\Security\AuthenticationInterface;
use App\Framework\Security\SecurityMiddleware;
use App\Framework\Security\UserInterface;
use PHPUnit\Framework\TestCase;

class SecurityMiddlewareTest extends TestCase
{
    private SecurityMiddleware $middleware;
    private AuthenticationInterface $auth;

    protected function setUp(): void
    {
        $this->auth = $this->createMock(AuthenticationInterface::class);
        $this->middleware = new SecurityMiddleware($this->auth, [
            'public_routes' => ['/login', '/logout', '/public'],
            'csrf_protection' => true,
            'csrf_token_name' => 'csrf_token',
            'csrf_token_expiry' => 3600,
        ]);

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

    public function testProcess(): void
    {
        // Create a request
        $request = $this->createRequest('GET', '/test');

        // Mock callable
        $next = function (ServerRequest $request) {
            return new Response(200, [], 'Success');
        };

        // Process the request
        $response = $this->middleware->process($request, $next);

        // Verify response has security headers
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', (string)$response->getBody());
        $this->assertEquals('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
        $this->assertEquals('1; mode=block', $response->getHeaderLine('X-XSS-Protection'));
        $this->assertEquals('DENY', $response->getHeaderLine('X-Frame-Options'));
        $this->assertStringContainsString('default-src', $response->getHeaderLine('Content-Security-Policy'));
    }

    public function testAuthenticateWithPublicRoute(): void
    {
        // Create a request for a public route
        $request = $this->createRequest('GET', '/public');

        // Auth should not be called for public routes
        $this->auth->expects($this->never())
            ->method('getCurrentUser');

        // Authenticate the request
        $authenticatedRequest = $this->middleware->authenticate($request);

        // Request should be returned unchanged
        $this->assertSame($request, $authenticatedRequest);
    }

    public function testAuthenticateWithAuthenticatedUser(): void
    {
        // Create a request for a protected route
        $request = $this->createRequest('GET', '/protected');

        // Create a mock user
        $user = $this->createMock(UserInterface::class);

        // Configure auth to return a user
        $this->auth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($user);

        // Authenticate the request
        $authenticatedRequest = $this->middleware->authenticate($request);

        // Request should be returned with user attribute
        $this->assertNotNull($authenticatedRequest);
        $this->assertSame($user, $authenticatedRequest->getAttribute('user'));
    }

    public function testAuthenticateWithUnauthenticatedUser(): void
    {
        // Create a request for a protected route
        $request = $this->createRequest('GET', '/protected');

        // Configure auth to return null (no authenticated user)
        $this->auth->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn(null);

        // Authenticate the request
        $authenticatedRequest = $this->middleware->authenticate($request);

        // Should return null for unauthenticated request to protected route
        $this->assertNull($authenticatedRequest);
    }

    public function testVerifyAuthorizationWithoutUser(): void
    {
        // Create a request without a user
        $request = $this->createRequest('GET', '/protected');

        // Verify authorization
        $result = $this->middleware->verifyAuthorization($request, ['ROLE_ADMIN']);

        // Should fail without a user
        $this->assertFalse($result);
    }

    public function testVerifyAuthorizationWithUserNoRoles(): void
    {
        // Create a request
        $request = $this->createRequest('GET', '/protected');

        // Add a user to the request
        $user = $this->createMock(UserInterface::class);
        $request = $request->withAttribute('user', $user);

        // Verify authorization with no specific roles required
        $result = $this->middleware->verifyAuthorization($request, []);

        // Should pass with a user and no specific roles required
        $this->assertTrue($result);
    }

    public function testVerifyAuthorizationWithUserAndRoles(): void
    {
        // Create a request
        $request = $this->createRequest('GET', '/admin');

        // Add a user to the request
        $user = $this->createMock(UserInterface::class);
        $request = $request->withAttribute('user', $user);

        // Configure auth to check roles
        $this->auth->expects($this->once())
            ->method('hasRole')
            ->with($user, ['ROLE_ADMIN'])
            ->willReturn(true);

        // Verify authorization with admin role required
        $result = $this->middleware->verifyAuthorization($request, ['ROLE_ADMIN']);

        // Should pass with a user that has admin role
        $this->assertTrue($result);
    }

    public function testCsrfTokenGenerationAndValidation(): void
    {
        // Generate a token
        $token = $this->middleware->generateCsrfToken();

        // Token should be stored in session
        $this->assertArrayHasKey('csrf_token', $_SESSION);
        $this->assertEquals($token, $_SESSION['csrf_token']['token']);

        // Token should be valid
        $this->assertTrue($this->middleware->validateCsrfToken($token));

        // Invalid token should fail
        $this->assertFalse($this->middleware->validateCsrfToken('invalid-token'));
    }

    public function testEscapeOutput(): void
    {
        // Test HTML escaping
        $unsafe = '<script>alert("XSS");</script>';
        $safe = $this->middleware->escapeOutput($unsafe);

        $this->assertNotEquals($unsafe, $safe);
        $this->assertEquals('&lt;script&gt;alert(&quot;XSS&quot;);&lt;/script&gt;', $safe);
    }

    /**
     * Helper method to create a request
     */
    private function createRequest(string $method, string $path): ServerRequest
    {
        $uri = new Uri('http://example.com' . $path);
        return new ServerRequest($method, $uri);
    }
}
