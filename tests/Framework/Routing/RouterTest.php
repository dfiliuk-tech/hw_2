<?php

declare(strict_types=1);

namespace Tests\Framework\Routing;

use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\Framework\Routing\Router;
use App\Framework\Routing\Exception\RouteNotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouterTest extends TestCase
{
    private ContainerInterface $container;
    private Router $router;
    
    protected function setUp(): void
    {
        // Create a mock container
        $this->container = $this->createMock(ContainerInterface::class);
        
        // Create router with the mock container
        $this->router = new Router($this->container);
    }
    
    public function testAddRoute(): void
    {
        $this->router->add('GET', '/test', [
            'controller' => 'TestController',
            'action' => 'index'
        ]);
        
        // Create a request for the route
        $request = $this->createRequest('GET', '/test');
        
        // Match should return the route options
        $route = $this->router->match($request);
        
        $this->assertSame('TestController', $route['controller']);
        $this->assertSame('index', $route['action']);
    }
    
    public function testMatchWithMethodNotAllowed(): void
    {
        $this->router->add('GET', '/test', [
            'controller' => 'TestController',
            'action' => 'index'
        ]);
        
        // Create a POST request for a GET route
        $request = $this->createRequest('POST', '/test');
        
        $this->expectException(RouteNotFoundException::class);
        $this->router->match($request);
    }
    
    public function testMatchWithNonExistentRoute(): void
    {
        $this->router->add('GET', '/test', [
            'controller' => 'TestController',
            'action' => 'index'
        ]);
        
        // Create a request for a non-existent route
        $request = $this->createRequest('GET', '/non-existent');
        
        $this->expectException(RouteNotFoundException::class);
        $this->router->match($request);
    }
    
    public function testMatchWithTrailingSlash(): void
    {
        $this->router->add('GET', '/test', [
            'controller' => 'TestController',
            'action' => 'index'
        ]);
        
        // Create a request with a trailing slash
        $request = $this->createRequest('GET', '/test/');
        
        $route = $this->router->match($request);
        
        $this->assertSame('TestController', $route['controller']);
        $this->assertSame('index', $route['action']);
    }
    
    public function testDispatch(): void
    {
        // Create a mock controller
        $controller = new class {
            public function index(ServerRequestInterface $request): string
            {
                return 'Test response';
            }
        };
        
        // Configure the container mock to return our controller
        $this->container->method('get')
            ->with('TestController')
            ->willReturn($controller);
        
        // Add a route
        $this->router->add('GET', '/test', [
            'controller' => 'TestController',
            'action' => 'index'
        ]);
        
        // Create a request
        $request = $this->createRequest('GET', '/test');
        
        // Match the route
        $route = $this->router->match($request);
        
        // Dispatch the route
        $response = $this->router->dispatch($route, $request);
        
        $this->assertSame('Test response', $response);
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
