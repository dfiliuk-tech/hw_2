<?php

declare(strict_types=1);

namespace App\Framework\Security;

use App\Framework\Http\ServerRequest;
use App\Framework\Http\Response;
use Psr\Http\Message\MessageInterface;
use Random\RandomException;

class SecurityMiddleware
{
    private array $options;
    private AuthenticationInterface $auth;

    public function __construct(AuthenticationInterface $auth, array $options = [])
    {
        $this->auth = $auth;

        // Default security options
        $this->options = array_merge([
            'csrf_protection' => true,
            'csrf_token_name' => 'csrf_token',
            'csrf_token_expiry' => 3600, // 1 hour
            'secure_headers' => true,
            'public_routes' => ['/login', '/logout'] // Routes that don't require authentication
        ], $options);
    }

    public function process(ServerRequest $request, callable $next): Response
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set secure headers
        if ($this->options['secure_headers']) {
            $response = $this->setSecureHeaders($next($request));
        } else {
            $response = $next($request);
        }

        return $response;
    }

    public function authenticate(ServerRequest $request): ?ServerRequest
    {
        // Skip authentication for public routes
        $path = $request->getUri()->getPath();
        if (in_array($path, $this->options['public_routes'])) {
            return $request;
        }

        // Check if user is authenticated
        $user = $this->auth->getCurrentUser();
        if ($user === null) {
            return null; // Authentication failed
        }

        // Add authenticated user to request attributes
        return $request->withAttribute('user', $user);
    }

    public function verifyAuthorization(ServerRequest $request, array $requiredRoles = []): bool
    {
        $user = $request->getAttribute('user');

        // No user or no roles to check
        if ($user === null) {
            return false;
        }

        // If no specific roles required, just being authenticated is enough
        if (empty($requiredRoles)) {
            return true;
        }

        // Check if user has at least one of the required roles
        return $this->auth->hasRole($user, $requiredRoles);
    }

    private function setSecureHeaders(Response $response): MessageInterface
    {
        // Add security headers
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');
        $response = $response->withHeader('X-XSS-Protection', '1; mode=block');
        $response = $response->withHeader('X-Frame-Options', 'DENY');
        $response = $response->withHeader(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self'; object-src 'none'"
        );
        $response = $response->withHeader('Referrer-Policy', 'no-referrer-when-downgrade');

        // Set Strict-Transport-Security
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $response = $response->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * @throws RandomException
     */
    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[$this->options['csrf_token_name']] = [
            'token' => $token,
            'expires' => time() + $this->options['csrf_token_expiry']
        ];
        return $token;
    }

    public function validateCsrfToken(string $token): bool
    {
        if (!isset($_SESSION[$this->options['csrf_token_name']])) {
            return false;
        }

        $storedToken = $_SESSION[$this->options['csrf_token_name']]['token'];
        $expires = $_SESSION[$this->options['csrf_token_name']]['expires'];

        // Check if token is valid and not expired
        return hash_equals($storedToken, $token) && time() < $expires;
    }

    public function escapeOutput(string $output): string
    {
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
