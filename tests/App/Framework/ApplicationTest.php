<?php

declare(strict_types=1);

namespace Tests\App\Framework;

use App\Framework\Application;
use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\Framework\Routing\Exception\RouteNotFoundException;
use App\Framework\Routing\Router;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApplicationTest extends TestCase
{
    private Router $router;
    private Application $app;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        // Create a mock router instead of container
        $this->router = $this->createMock(Router::class);

        // Create the application with the router
        $this->app = new Application($this->router);
    }

    public function testHandleSuccessfulRequest(): void
    {
        // Create a request
        $request = $this->createRequest('GET', '/test');

        // Set up route matching
        $route = [
            'controller' => 'TestController',
            'action' => 'index'
        ];

        $this->router->method('match')
            ->with($request)
            ->willReturn($route);

        // Set up route dispatching
        $this->router->method('dispatch')
            ->with($route, $request)
            ->willReturn('Test response');

        // Handle the request
        $response = $this->app->handle($request);

        // Verify the response
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Test response', (string)$response->getBody());
    }

    public function testHandleWithJsonResponse(): void
    {
        // Create a request
        $request = $this->createRequest('GET', '/api/test');

        // Set up route matching
        $route = [
            'controller' => 'ApiController',
            'action' => 'test'
        ];

        $this->router->method('match')
            ->with($request)
            ->willReturn($route);

        // Set up route dispatching to return an array (which should be JSON encoded)
        $data = ['status' => 'success', 'data' => 'test'];

        $this->router->method('dispatch')
            ->with($route, $request)
            ->willReturn($data);

        // Handle the request
        $response = $this->app->handle($request);

        // Verify the response
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame(json_encode($data), (string)$response->getBody());
    }

    public function testHandleWithPrebuiltResponse(): void
    {
        // Create a request
        $request = $this->createRequest('GET', '/test');

        // Set up route matching
        $route = [
            'controller' => 'TestController',
            'action' => 'index'
        ];

        $this->router->method('match')
            ->with($request)
            ->willReturn($route);

        // Set up route dispatching to return a Response object
        $prebuiltResponse = new Response(201, ['X-Custom' => 'Value'], 'Created');

        $this->router->method('dispatch')
            ->with($route, $request)
            ->willReturn($prebuiltResponse);

        // Handle the request
        $response = $this->app->handle($request);

        // Verify the response is passed through unchanged
        $this->assertSame($prebuiltResponse, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Value', $response->getHeaderLine('X-Custom'));
        $this->assertSame('Created', (string)$response->getBody());
    }

    public function testHandleRouteNotFound(): void
    {
        // Create a request
        $request = $this->createRequest('GET', '/not-found');

        // Set up route matching to throw an exception
        $this->router->method('match')
            ->with($request)
            ->willThrowException(new RouteNotFoundException('Route not found'));

        // Handle the request
        $response = $this->app->handle($request);

        // Verify the response
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString('Not Found', (string)$response->getBody());
    }

    public function testHandleServerError(): void
    {
        // Create a request
        $request = $this->createRequest('GET', '/test');

        // Set up route matching to throw an unexpected exception
        $this->router->method('match')
            ->with($request)
            ->willThrowException(new \RuntimeException('Something went wrong'));

        // Handle the request
        $response = $this->app->handle($request);

        // Verify the response
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('Internal Server Error', (string)$response->getBody());
    }

    /**
     * Helper method to create a request
     */
    private function createRequest(string $method, string $path): ServerRequestInterface
    {
        $uri = new Uri('http://example.com' . $path);
        return new ServerRequest($method, $uri);
    }
}
