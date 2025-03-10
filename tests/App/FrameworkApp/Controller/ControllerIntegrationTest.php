<?php

declare(strict_types=1);

namespace Tests\App\FrameworkApp;

use App\Framework\Application;
use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\Framework\Routing\Router;
use App\FrameworkApp\Controller\ApiController;
use App\FrameworkApp\Controller\ContactController;
use App\FrameworkApp\Controller\HomeController;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ControllerIntegrationTest extends TestCase
{
    private ContainerInterface $container;
    private Router $router;
    private Application $application;

    protected function setUp(): void
    {
        // Create a mock container
        $this->container = $this->createMock(ContainerInterface::class);
        
        // Set up the container to return the correct controller instances
        $this->container->method('get')
            ->willReturnCallback(function ($class) {
                return match ($class) {
                    HomeController::class => new HomeController(),
                    ContactController::class => new ContactController(),
                    ApiController::class => new ApiController(),
                    default => throw new \RuntimeException("Unknown class: $class")
                };
            });

        // Create and set up the router
        $this->router = new Router($this->container);
        
        // Register routes
        $this->router->add('GET', '/', [
            'controller' => HomeController::class,
            'action' => 'index'
        ]);
        
        $this->router->add('GET', '/contact', [
            'controller' => ContactController::class,
            'action' => 'show'
        ]);
        
        $this->router->add('GET', '/api/status', [
            'controller' => ApiController::class,
            'action' => 'status'
        ]);
        
        $this->router->add('POST', '/api/status', [
            'controller' => ApiController::class,
            'action' => 'update'
        ]);

        // Create the application
        $this->application = new Application($this->router);
    }

    public function testHomeEndpoint(): void
    {
        // Create a request to the home endpoint
        $request = $this->createRequest('GET', '/');
        
        // Handle the request
        $response = $this->application->handle($request);
        
        // Assert response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Welcome to Our Minimal Framework', (string)$response->getBody());
    }

    public function testContactEndpoint(): void
    {
        // Create a request to the contact endpoint
        $request = $this->createRequest('GET', '/contact');
        
        // Handle the request
        $response = $this->application->handle($request);
        
        // Assert response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Contact Us', (string)$response->getBody());
    }

    public function testApiStatusEndpoint(): void
    {
        // Create a request to the API status endpoint
        $request = $this->createRequest('GET', '/api/status');
        
        // Handle the request
        $response = $this->application->handle($request);
        
        // Assert response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        
        // Decode the body
        $content = json_decode((string)$response->getBody(), true);
        $this->assertEquals('OK', $content['status']);
    }

    public function testApiUpdateEndpoint(): void
    {
        // Create a request to the API update endpoint
        $request = $this->createRequest('POST', '/api/status');
        $request = $request->withParsedBody(['status' => 'testing']);
        
        // Handle the request
        $response = $this->application->handle($request);
        
        // Assert response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        
        // Decode the body
        $content = json_decode((string)$response->getBody(), true);
        $this->assertEquals('testing', $content['status']);
        $this->assertTrue($content['updated']);
    }

    public function testNonExistentEndpoint(): void
    {
        // Create a request to a non-existent endpoint
        $request = $this->createRequest('GET', '/does-not-exist');
        
        // Handle the request
        $response = $this->application->handle($request);
        
        // Assert response
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Not Found', (string)$response->getBody());
    }

    public function testMethodNotAllowed(): void
    {
        // Create a POST request to a GET-only endpoint
        $request = $this->createRequest('POST', '/contact');
        
        // Handle the request
        $response = $this->application->handle($request);
        
        // Assert response
        $this->assertEquals(404, $response->getStatusCode());
    }

    private function createRequest(string $method, string $path): ServerRequest
    {
        $uri = new Uri('http://example.com' . $path);
        return new ServerRequest($method, $uri);
    }
}
