<?php

declare(strict_types=1);

namespace Tests\App\Framework\Controller;

use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\Framework\Security\SecurityMiddleware;
use App\Framework\Security\UserInterface;
use App\Framework\View\TwigService;
use PHPUnit\Framework\TestCase;

class AbstractControllerTest extends TestCase
{
    private TestController $controller;
    private TwigService $twig;
    private SecurityMiddleware $security;

    protected function setUp(): void
    {
        // Create mocks for dependencies
        $this->twig = $this->createMock(TwigService::class);
        $this->security = $this->createMock(SecurityMiddleware::class);

        // Create the controller
        $this->controller = new TestController($this->twig, $this->security);
    }

    public function testRender(): void
    {
        // Mock the twig render method to return a test content
        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('test.html.twig'),
                $this->callback(function ($context) {
                    return isset($context['test_var']) &&
                           $context['test_var'] === 'test_value';
                })
            )
            ->willReturn('<html>Test content</html>');

        // Call the render method
        $response = $this->controller->testRender('test.html.twig', [
            'test_var' => 'test_value'
        ]);

        // Verify response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('<html>Test content</html>', (string)$response->getBody());
    }

    public function testRenderWithUserInRequest(): void
    {
        // Create a mock user
        $user = $this->createMock(UserInterface::class);

        // Create a request with the user
        $uri = new Uri('http://example.com/test');
        $request = new ServerRequest('GET', $uri);
        $request = $request->withAttribute('user', $user);

        // Set the request in the global variable
        $GLOBALS['request'] = $request;

        // Mock the twig render method
        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('test.html.twig'),
                $this->callback(function ($context) use ($user) {
                    return isset($context['user']) && $context['user'] === $user;
                })
            )
            ->willReturn('<html>Test with user</html>');

        // Call the render method
        $response = $this->controller->testRender('test.html.twig');

        // Clean up global
        unset($GLOBALS['request']);

        // Verify response
        $this->assertEquals('<html>Test with user</html>', (string)$response->getBody());
    }

    public function testJson(): void
    {
        // Test data
        $data = ['key' => 'value'];

        // Call the json method
        $response = $this->controller->testJson($data);

        // Verify response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(json_encode($data), (string)$response->getBody());
    }

    public function testRedirect(): void
    {
        // Call the redirect method
        $response = $this->controller->testRedirect('/target', 303);

        // Verify response
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertEquals('/target', $response->getHeaderLine('Location'));
        $this->assertEquals('', (string)$response->getBody());
    }

    public function testTwigSecurityIntegration(): void
    {
        // The SecurityMiddleware should be passed to the Twig service
        // during constructor
        $this->twig->expects($this->once())
            ->method('setSecurityMiddleware')
            ->with($this->security);

        // Re-create the controller to trigger the initialization
        $controller = new TestController($this->twig, $this->security);
    }
}
