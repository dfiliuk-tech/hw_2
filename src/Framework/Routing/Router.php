<?php

declare(strict_types=1);

namespace App\Framework\Routing;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Framework\Routing\Exception\RouteNotFoundException;
use Psr\Container\ContainerInterface;

class Router
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $routes = [];

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Register a route with the router
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path URL path to match
     * @param array<string, mixed> $options Route options (controller, action, etc.)
     * @return void
     */
    public function add(string $method, string $path, array $options): void
    {
        $this->routes[strtoupper($method)][$path] = $options;
    }

    /**
     * Match a request to a route
     *
     * @param ServerRequestInterface $request The incoming request
     * @return array<string, mixed> The matched route
     * @throws RouteNotFoundException
     */
    public function match(ServerRequestInterface $request): array
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();

        // Normalize URI by removing trailing slashes
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        if (isset($this->routes[$method][$uri])) {
            return $this->routes[$method][$uri];
        }

        throw new RouteNotFoundException("No route found for {$method} {$uri}");
    }

    /**
     * Dispatch a route to its controller
     *
     * @param array<string, mixed> $route The route to dispatch
     * @param ServerRequestInterface $request The incoming request
     * @return mixed The controller response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function dispatch(array $route, ServerRequestInterface $request): mixed
    {
        $controller = $route['controller'];
        $action = $route['action'];

        // Get the controller from the container
        $controllerInstance = $this->container->get($controller);

        // Call the action method with the request
        return $controllerInstance->$action($request);
    }
}
