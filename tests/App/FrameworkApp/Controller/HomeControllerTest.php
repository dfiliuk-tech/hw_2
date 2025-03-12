<?php

declare(strict_types=1);

namespace Tests\App\FrameworkApp\Controller;

use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\Framework\Security\SecurityMiddleware;
use App\Framework\View\TwigService;
use App\FrameworkApp\Controller\HomeController;
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    private HomeController $controller;
    private TwigService $twig;
    private SecurityMiddleware $security;

    protected function setUp(): void
    {
        // Create mocks for dependencies
        $this->twig = $this->createMock(TwigService::class);
        $this->security = $this->createMock(SecurityMiddleware::class);

        // Create the controller
        $this->controller = new HomeController($this->twig, $this->security);
    }

    public function testIndex(): void
    {
        // Create a request
        $uri = new Uri('http://example.com/');
        $request = new ServerRequest('GET', $uri);

        // Mock the twig render method to return a test content
        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('home.html.twig'),
                $this->callback(function ($context) {
                    return isset($context['page_title']) &&
                        $context['page_title'] === 'Home';
                })
            )
            ->willReturn('<html>Home content</html>');

        // Call the index method
        $response = $this->controller->index($request);

        // Verify response
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('<html>Home content</html>', (string)$response->getBody());
    }

    /**
     * This test verifies that the controller correctly initializes and passes
     * the security middleware to the twig service
     */
    public function testSecurityMiddlewareIntegration(): void
    {
        // The SecurityMiddleware should be passed to the Twig service
        // during constructor (in AbstractController)
        $this->twig->expects($this->once())
            ->method('setSecurityMiddleware')
            ->with($this->security);

        // Re-create the controller to trigger the initialization
        $controller = new HomeController($this->twig, $this->security);
    }
}
