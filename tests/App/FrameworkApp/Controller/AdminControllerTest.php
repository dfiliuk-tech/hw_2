<?php

declare(strict_types=1);

namespace Tests\App\FrameworkApp\Controller;

use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\Framework\Security\AuthenticationInterface;
use App\Framework\Security\SecurityMiddleware;
use App\Framework\View\TwigService;
use App\FrameworkApp\Controller\AdminController;
use PHPUnit\Framework\TestCase;

class AdminControllerTest extends TestCase
{
    private AdminController $controller;
    private TwigService $twig;
    private SecurityMiddleware $security;
    private AuthenticationInterface $auth;

    protected function setUp(): void
    {
        // Create mocks for dependencies
        $this->twig = $this->createMock(TwigService::class);
        $this->security = $this->createMock(SecurityMiddleware::class);
        $this->auth = $this->createMock(AuthenticationInterface::class);

        // Setup the twig security integration
        $this->twig->expects($this->once())
            ->method('setSecurityMiddleware')
            ->with($this->security);

        // Create the controller
        $this->controller = new AdminController($this->auth, $this->twig, $this->security);
    }

    public function testDashboardWithAdminAccess(): void
    {
        // Create a request
        $uri = new Uri('http://example.com/admin');
        $request = new ServerRequest('GET', $uri);

        // Security middleware should verify authorization and return true
        $this->security->expects($this->once())
            ->method('verifyAuthorization')
            ->with($request, ['ROLE_ADMIN'])
            ->willReturn(true);

        // Server software for testing
        $_SERVER['SERVER_SOFTWARE'] = 'PHPUnit Test Server';

        // Mock the twig render method to return a test content
        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('admin.html.twig'),
                $this->callback(function ($context) {
                    return isset($context['demo_users']) &&
                        is_array($context['demo_users']) &&
                        isset($context['server_software']) &&
                        $context['server_software'] === 'PHPUnit Test Server';
                })
            )
            ->willReturn('<html>Admin dashboard</html>');

        // Call the dashboard method
        $response = $this->controller->dashboard($request);

        // Verify response
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('<html>Admin dashboard</html>', (string)$response->getBody());

        // Cleanup
        unset($_SERVER['SERVER_SOFTWARE']);
    }

    public function testDashboardWithoutAdminAccess(): void
    {
        // Create a request
        $uri = new Uri('http://example.com/admin');
        $request = new ServerRequest('GET', $uri);

        // Security middleware should verify authorization and return false
        $this->security->expects($this->once())
            ->method('verifyAuthorization')
            ->with($request, ['ROLE_ADMIN'])
            ->willReturn(false);

        // Mock the twig render method to return an error page
        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('error.html.twig'),
                $this->callback(function ($context) {
                    return isset($context['status']) &&
                        $context['status'] === 403 &&
                        isset($context['title']) &&
                        $context['title'] === 'Access Denied';
                })
            )
            ->willReturn('<html>Access denied</html>');

        // Call the dashboard method
        $response = $this->controller->dashboard($request);

        // Verify response
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('<html>Access denied</html>', (string)$response->getBody());
    }

    public function testAction(): void
    {
        // Create a request with POST data including a CSRF token
        $uri = new Uri('http://example.com/admin/action');
        $request = new ServerRequest('POST', $uri);
        $request = $request->withParsedBody([
            'csrf_token' => 'valid_token',
            'action' => 'test_action'
        ]);

        // Security middleware should verify authorization and return true
        $this->security->expects($this->once())
            ->method('verifyAuthorization')
            ->with($request, ['ROLE_ADMIN'])
            ->willReturn(true);

        // Security middleware should validate the CSRF token and return true
        $this->security->expects($this->once())
            ->method('validateCsrfToken')
            ->with('valid_token')
            ->willReturn(true);

        // Security middleware should escape the action name
        $this->security->expects($this->once())
            ->method('escapeOutput')
            ->with('test_action')
            ->willReturn('test_action');

        // Mock the twig render method to return a result page
        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('error.html.twig'),
                $this->callback(function ($context) {
                    return isset($context['status']) &&
                        $context['status'] === 200 &&
                        isset($context['title']) &&
                        $context['title'] === 'Action Result' &&
                        isset($context['message']) &&
                        strpos($context['message'], 'test_action') !== false;
                })
            )
            ->willReturn('<html>Action result</html>');

        // Call the action method
        $response = $this->controller->action($request);

        // Verify response
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('<html>Action result</html>', (string)$response->getBody());
    }

    public function testActionWithInvalidCsrfToken(): void
    {
        // Create a request with POST data including an invalid CSRF token
        $uri = new Uri('http://example.com/admin/action');
        $request = new ServerRequest('POST', $uri);
        $request = $request->withParsedBody([
            'csrf_token' => 'invalid_token',
            'action' => 'test_action'
        ]);

        // Security middleware should verify authorization and return true
        $this->security->expects($this->once())
            ->method('verifyAuthorization')
            ->with($request, ['ROLE_ADMIN'])
            ->willReturn(true);

        // Security middleware should validate the CSRF token and return false
        $this->security->expects($this->once())
            ->method('validateCsrfToken')
            ->with('invalid_token')
            ->willReturn(false);

        // Mock the twig render method to return an error page
        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('error.html.twig'),
                $this->callback(function ($context) {
                    return isset($context['status']) &&
                        $context['status'] === 400 &&
                        isset($context['title']) &&
                        $context['title'] === 'Bad Request';
                })
            )
            ->willReturn('<html>Bad request</html>');

        // Call the action method
        $response = $this->controller->action($request);

        // Verify response
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('<html>Bad request</html>', (string)$response->getBody());
    }
}
